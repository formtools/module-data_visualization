<?php


namespace FormTools\Modules\DataVisualization;

use FormTools\Core;
use FormTools\Hooks;
use FormTools\Settings;
use FormTools\Module as FormToolsModule;
use PDO, PDOException;


class Module extends FormToolsModule
{
    protected $moduleName = "Data Visualization";
    protected $moduleDesc = "This module utilizes Google Charts API to create custom graphs and charts of your form submission data, providing an alternative, visual interpretation of your data.";
    protected $author = "Ben Keen";
    protected $authorEmail = "ben.keen@gmail.com";
    protected $authorLink = "http://formtools.org";
    protected $version = "2.0.0";
    protected $date = "2017-10-21";
    protected $originLanguage = "en_us";
    protected $jsFiles = array(
        "https://www.gstatic.com/charts/loader.js",
        "{MODULEROOT}/scripts/manage_visualizations.js"
    );

    protected $cssFiles = array(
        "{MODULEROOT}/css/styles.css",
        "{MODULEROOT}/css/visualizations.css"
    );

    protected $nav = array(
        "word_visualizations"    => array("index.php", false),
        "phrase_main_settings"   => array("settings.php", false),
        "phrase_activity_charts" => array("activity_charts/settings.php", true),
        "phrase_field_charts"    => array("field_charts/settings.php", true),
        "word_help"              => array("help.php", false)
    );

    public function install($module_id)
    {
        $db = Core::$db;

        $queries = array();
        $queries[] = "
            CREATE TABLE {PREFIX}module_data_visualizations (
            vis_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            vis_name varchar(255) NOT NULL,
            vis_type enum('activity','field') NOT NULL,
            chart_type enum('line_chart','area_chart','column_chart','bar_chart','pie_chart') NOT NULL,
            form_id mediumint(9) NOT NULL,
            view_id mediumint(9) DEFAULT NULL,
            field_id mediumint(9) DEFAULT NULL,
            access_type enum('admin','public','private') NOT NULL DEFAULT 'public',
            access_view_mapping enum('all','except','only') NOT NULL DEFAULT 'all',
            access_views mediumtext NOT NULL,
            cache_update_frequency varchar(8) NOT NULL,
            date_range varchar(20) DEFAULT NULL,
            submission_count_group enum('year','month','week','day') DEFAULT NULL,
            colour varchar(10) DEFAULT NULL,
            line_width tinyint(4) DEFAULT NULL,
            field_chart_ignore_empty_fields enum('yes','no') DEFAULT NULL,
            pie_chart_format enum('2D','3D') DEFAULT NULL,
            include_legend_quicklinks enum('yes','no') DEFAULT NULL,
            include_legend_full_size enum('yes','no') DEFAULT NULL,
            PRIMARY KEY (vis_id)
            ) DEFAULT CHARSET=utf8
        ";

        $queries[] = "
            CREATE TABLE {PREFIX}module_data_visualization_cache (
            vis_id mediumint(8) unsigned NOT NULL,
            last_cached datetime NOT NULL,
            data mediumtext NOT NULL,
            PRIMARY KEY (vis_id)
            ) DEFAULT CHARSET=utf8
        ";

        $queries[] = "
            CREATE TABLE {PREFIX}module_data_visualization_clients (
            vis_id mediumint(8) unsigned NOT NULL,
            account_id mediumint(8) unsigned NOT NULL,
            PRIMARY KEY (vis_id,account_id)
            ) DEFAULT CHARSET=utf8
        ";

        try {
            $db->beginTransaction();
            foreach ($queries as $query) {
                $db->query($query);
                $db->execute();
            }
            $db->processTransaction();
        } catch (PDOException $e) {
            $db->rollbackTransaction();
            $L = $this->getLangStrings();
            return array(false, $L["notify_installation_problem_c"] . " <b>" . $e->getMessage() . "</b>");
        }

        Hooks::registerHook("template", "data_visualization", "admin_submission_listings_top", "", "displayVisualizationIcon", 50, true);
        Hooks::registerHook("code", "data_visualization", "main", "FormTools\\Submissions::displaySubmissionListingQuicklinks", "addQuicklink", 50, true);
        Hooks::registerHook("template", "data_visualization", "head_bottom", "", "includeInHead", 50, true);
        Hooks::registerHook("code", "data_visualization", "start", "FormTools\\Forms::deleteForm", "deleteFormHook", 50, true);

        // a custom hook for use in Smarty pages generated via the Pages module
        Hooks::registerHook("template", "data_visualization", "data_visualization", "", "displayInPagesModule", 50, true);

        $settings = array(
            // main settings
            "quicklinks_dialog_width"      => 880,
            "quicklinks_dialog_height"     => 400,
            "quicklinks_dialog_thumb_size" => 200,
            "default_cache_frequency"      => 30,
            "clients_may_refresh_cache"    => "yes",

            // Activity Chart default settings
            "activity_chart_date_range"               => "last_30_days",
            "activity_chart_submission_count_group"   => "day",
            "activity_chart_default_chart_type"       => "line_chart",
            "activity_chart_colour"                   => "blue",
            "activity_chart_line_width"               => 2,

            // Field Chart default settings
            "field_chart_default_chart_type"          => "pie_chart",
            "field_chart_include_legend_quicklinks"   => "no",
            "field_chart_colour"                      => "blue",
            "field_chart_include_legend_full_size"    => "yes",
            "field_chart_pie_chart_format"            => "2D",
            "field_chart_ignore_empty_fields"         => "yes"
        );
        Settings::set($settings, "data_visualization");

        return array(true, "");
    }


    public function uninstall($module_id)
    {
        $db = Core::$db;

        $db->query("DROP TABLE {PREFIX}module_data_visualizations");
        $db->execute();

        $db->query("DROP TABLE {PREFIX}module_data_visualization_cache");
        $db->execute();

        $db->query("DROP TABLE {PREFIX}module_data_visualization_clients");
        $db->execute();

        return array(true, "");
    }

    /**
     * This adds the quicklink icon to the Submission Listing page. This function is already assigned to those
     * particular hooks on the admin and client Submission Listing pages.
     *
     * It only shows the icon if there's at least ONE visualization to show for this form View.
     *
     * @param array $params
     */
    public function addQuicklink($params)
    {
        $root_url = Core::getRootUrl();
        $smarty = Core::$smarty;
        $template_vars = $smarty->getTemplateVars();

        $module_settings = $this->getSettings();
        if ($template_vars["page"] == "client_forms" && $module_settings["hide_from_client_accounts"] == "yes") {
            return "";
        }

        $form_id = $template_vars["form_info"]["form_id"];
        $view_id = $template_vars["view_id"];

        $vis_ids = General::getQuicklinkVisualizations($form_id, $view_id);

        if (empty($vis_ids)) {
            return "";
        }

        $vis_id_str = implode(",", $vis_ids);

      // output the visualization IDs right into the page. This will save an HTTP request to retrieve them later
      echo <<< END
    <script>g.vis_ids = [$vis_id_str];</script>
END;

        $L = $this->getLangStrings();
        return array(
            "quicklinks" => array(
                "icon_url"   => "$root_url/modules/data_visualization/images/icon_visualization16x16.png",
                "title_text" => "{$L["phrase_view_visualizations"]}",
                "href"       => "#",
                "onclick"    => "return dv_ns.show_visualizations_dialog()"
            )
        );
    }


    /**
     * This embeds the necessary include files for the Visualization module into the head of the admin and client
     * Submission Listing page. Sadly, at the point this is executed, we don't have access to the page data (namely
     * form ID and View ID) so we can't determine whether or not we NEED to include the code.
     *
     * @param string $location
     * @param array $params
     */
    public function includeInHead($location, $params)
    {
        $root_url = Core::getRootUrl();
        $L = $this->getLangStrings();

        if ($params["page"] != "admin_forms" && $params["page"] != "client_forms") {
            return;
        }

        $module_settings = $this->getSettings();
        if ($params["page"] == "client_forms" && $module_settings["hide_from_client_accounts"] == "yes") {
            return;
        }

        $cache_display = "block";
        if ($params["page"] == "admin_forms") {
            $context = "admin_submission_listing";
        } else {
            if ($module_settings["clients_may_refresh_cache"] == "no") {
                $cache_display = "none";
            }
            $context = "client_submission_listing";
        }

        $vis_messages = General::getVisMessages($L);

        echo <<< END
<script src="https://www.google.com/jsapi"></script>
<link type="text/css" rel="stylesheet" href="$root_url/modules/data_visualization/css/visualizations.css">
<script src="$root_url/modules/data_visualization/scripts/visualizations.js?v=2"></script>
<script>
$(function() {
    $(".dv_vis_tile_enlarge").live("click", dv_ns.enlarge_visualization);
    $("#dv_vis_full_nav li.back span").live("click", dv_ns.return_to_overview);
    $("#dv_vis_full_nav li.prev span").live("click", dv_ns.show_prev_visualization);
    $("#dv_vis_full_nav li.next span").live("click", dv_ns.show_next_visualization);
    dv_ns.context = "$context";
});

g.quicklinks_dialog_width = {$module_settings["quicklinks_dialog_width"]};
g.quicklinks_dialog_height = {$module_settings["quicklinks_dialog_height"]};
g.vis_tile_size = {$module_settings["quicklinks_dialog_thumb_size"]};

$vis_messages
</script>

<style>
#dv_vis_tiles li {
    width: {$module_settings["quicklinks_dialog_thumb_size"]}px;
    height: {$module_settings["quicklinks_dialog_thumb_size"]}px;
}
#dv_vis_refresh_cache {
    display: $cache_display;
}
</style>
END;
    }


    public static function displayVisualizationIcon($template, $page_data)
    {
        $db = Core::$db;

        // find out if there are any visualizations to be shown for this form
        $db->query("
            SELECT count(*)
            FROM   {PREFIX}module_data_visualizations
            WHERE  form_id = :form_id
        ");
        $db->bind("form_id", $page_data["form_id"]);
        $db->execute();

        if ($db->fetch(PDO::FETCH_COLUMN) == 0) {
            return;
        }

        $root_url = Core::getRootUrl();

        echo <<< END
<div style="float: right; margin-top: -32px;">
    <a href="#"><img src="$root_url/modules/data_visualization/images/icon_visualization_small.png" /></a>
</div>
END;
    }

}
