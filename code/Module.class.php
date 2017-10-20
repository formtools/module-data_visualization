<?php


namespace FormTools\Modules\DataVisualization;

use FormTools\Core;
use FormTools\Hooks;
use FormTools\Settings;
use FormTools\Module as FormToolsModule;
use PDOException;


class Module extends FormToolsModule
{
    protected $moduleName = "Export Manager";
    protected $moduleDesc = "Define your own ways of exporting form submission data for view / download. Excel, Printer-friendly HTML, XML and CSV are included by default.";
    protected $author = "Ben Keen";
    protected $authorEmail = "ben.keen@gmail.com";
    protected $authorLink = "http://formtools.org";
    protected $version = "3.0.2";
    protected $date = "2017-10-14";
    protected $originLanguage = "en_us";
    protected $jsFiles = array(
        "{MODULEROOT}/scripts/manage_visualizations.js",
        "{MODULEROOT}/scripts/visualizations.js"

        //<script src="https://www.google.com/jsapi"></script>

    );
    protected $cssFiles = array(
        "{MODULEROOT}/css/styles.css",
        "{MODULEROOT}/css/visualizations.css"
    );

    protected $nav = array(
        "word_visualizations"    => array("index.php", false),
        "phrase_main_settings"   => array("settings.php", false),
        "phrase_activity_charts" => array("activity_charts/settings.php", true),
        "phrase_field_charts"    => array("settings.php", true),
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
}
