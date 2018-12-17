<?php

require_once("../../global/library.php");

use FormTools\Core;
use FormTools\General as CoreGeneral;
use FormTools\Modules;
use FormTools\Modules\DataVisualization\General;
use FormTools\Modules\DataVisualization\Visualizations;

$module = Modules::initModulePage("admin");
$L = $module->getLangStrings();
$LANG = Core::$L;

$num_visualizations_per_page = 10;
$success = true;
$message = "";
if (isset($_GET["delete"])) {
	list($success, $message) = Visualizations::deleteVisualization($_GET["delete"], $L);
}

if (isset($_GET["reset"])) {
    $_GET["keyword"] = "";
    $_GET["dv_search_form_id"] = "";
    $_GET["dv_search_view_id"] = "";
    $_GET["vis_types"] = array("activity", "field");
    $_GET["dv_search_chart_type"] = "";
    $_GET["account_type"] = "admin";
    $_GET["client_id"] = "";
}

// if we're being linked here from the admin's Submission Listing page (i.e. the user just clicked the "Manage Visualizations" button)
// reset everything except the form & View
if (isset($_GET["source"]) && $_GET["source"] == "admin_submission_listing") {
    $_GET["keyword"] = "";
    $_GET["vis_types"] = array("activity", "field");
    $_GET["chart_type"] = "";
    $_GET["account_type"] = "admin";
    $_GET["client_id"] = "";
}

$keyword        = Modules::loadModuleField("data_visualization", "keyword", "dv_search_keyword", "");
$search_form_id = Modules::loadModuleField("data_visualization", "dv_search_form_id", "dv_form_id", "");
$search_view_id = Modules::loadModuleField("data_visualization", "dv_search_view_id", "dv_view_id", "");
$vis_types      = Modules::loadModuleField("data_visualization", "vis_types", "dv_vis_types", array("activity", "field"));
$chart_type     = Modules::loadModuleField("data_visualization", "dv_search_chart_type", "dv_chart_type", "");
$account_type   = Modules::loadModuleField("data_visualization", "account_type", "dv_account_type", "admin");
$client_id      = Modules::loadModuleField("data_visualization", "client_id", "dv_client_id", "");

$search_criteria = array(
    "keyword"      => $keyword,
    "form_id"      => $search_form_id,
    "view_id"      => $search_view_id,
    "vis_types"    => $vis_types,
    "chart_type"   => $chart_type,
    "account_type" => $account_type,
    "client_id"    => $client_id
);

$results = Visualizations::searchVisualizations($search_criteria);
$total_results = Visualizations::getNumVisualizations();
$js = General::getFormViewMappingJs();

$module_settings = $module->getSettings();

// get the list of visualization IDs for use in the page
$vis_ids = array();
foreach ($results as $vis_info) {
	$vis_ids[] = $vis_info["vis_id"];
}
$vis_id_str = implode(",", $vis_ids);
$vis_messages = General::getVisMessages($L);

$page_vars = array(
    "results" => $results,
    "total_results" => $total_results,
    "num_visualizations_per_page" => $num_visualizations_per_page,
    "keyword" => $keyword,
    "search_form_id" => $search_form_id,
    "search_view_id" => $search_view_id,
    "vis_types" => $vis_types,
    "chart_type" => $chart_type,
    "account_type" => $account_type,
    "client_id" => $client_id,
    "js_messages" => array(
        "word_delete", "word_edit", "phrase_please_select_form", "phrase_please_select", "word_yes", "word_no"
    ),
    "module_js_messages" => array(
        "phrase_delete_visualization", "confirm_delete_visualization"
    ),
    "pagination" => CoreGeneral::getJsPageNav(count($results), $num_visualizations_per_page, 1)
);

$page_vars["head_css"] =<<<END
#dv_vis_tiles li {
    width: {$module_settings["quicklinks_dialog_thumb_size"]}px;
    height: {$module_settings["quicklinks_dialog_thumb_size"]}px;
}
END;

$page_vars["head_js"] =<<< END
$(function() {
    if (typeof google == "undefined") {
        $("#no_internet_connection").show();
        $("#view_visualizations").hide();
    }
    dv_ns.context = "manage_visualizations";
    $("#view_visualizations").bind("click", dv_ns.show_visualizations_dialog);
    $(".dv_vis_tile_enlarge").live("click", dv_ns.enlarge_visualization);
    $("#dv_vis_full_nav li.back span").live("click", dv_ns.return_to_overview);
    $("#dv_vis_full_nav li.prev span").live("click", dv_ns.show_prev_visualization);
    $("#dv_vis_full_nav li.next span").live("click", dv_ns.show_next_visualization);
    
    $("#client_id").bind("change", function() { $("#at2").attr("checked", "checked"); });
    $("#search_form form").bind("submit", function() {
        var errors = [];
        if (!$("#vt1").attr("checked") && !$("#vt2").attr("checked")) {
            errors.push("{$L["validation_no_vis_type_selected"]}");
        }
        if (!$("#at1").attr("checked") && !$("#at2").attr("checked")) {
            errors.push("{$L["validation_no_account_type_selected"]}");
        }
        if (errors.length) {
            var error_str = errors.join("<br />", errors);
            ft.display_message("ft_message", 0, error_str);
            return false;
        }
    });
    
    $("#form_id").bind("change keyup", function() {
        vis_ns.select_form(this.value);
    });
    
    $("#create_visualization_dialog li").bind("click", function() {
        var vis_type = $(this).find(".visualization_type").val();
        switch (vis_type) {
            case "activity_chart":
                window.location = 'activity_charts/add.php';
                break;
            case "field_chart":
                window.location = 'field_charts/add.php';
                break;
        }
    });
    
    $("#create_visualization").bind("click", function() {
        ft.create_dialog({
            dialog: $("#create_visualization_dialog"),
            title:  "{$L["phrase_select_visualization"]}",
            min_width: 650
        });
    });
    
    vis_ns.default_view_label = "{$L["phrase_all_views"]}";
});

g.quicklinks_dialog_width = {$module_settings["quicklinks_dialog_width"]};
g.quicklinks_dialog_height = {$module_settings["quicklinks_dialog_height"]};
g.vis_tile_size = {$module_settings["quicklinks_dialog_thumb_size"]};

$vis_messages

g.vis_ids = [$vis_id_str];
$js
END;

$page_vars["head_string"] =<<< END
<script src="scripts/visualizations.js"></script>
END;


$module->displayPage("templates/index.tpl", $page_vars);
