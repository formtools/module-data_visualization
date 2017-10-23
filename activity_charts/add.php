<?php

require_once("../../../global/library.php");

use FormTools\Core;
use FormTools\Modules;
use FormTools\Modules\DataVisualization\ActivityCharts;
use FormTools\Modules\DataVisualization\General;

$module = Modules::initModulePage("admin");
$L = $module->getLangStrings();
$LANG = Core::$L;

$vis_id = "";
$form_id = "";
$view_id = "";
$success = true;
$message = "";
if (isset($_POST["add"])) {
    list($success, $message, $vis_id) = ActivityCharts::addActivityChart($request, $L);
    if ($success) {
        header("location: edit.php?vis_id=$vis_id&page=main&is_new");
        exit;
    }
}

$js = General::getFormViewMappingJs();

$page_vars = array(
    "g_success" => $success,
    "g_message" => $message,
    "vis_id" => $vis_id,
    "form_id" => $form_id,
    "view_id" => $view_id,
    "module_settings" => $module->getSettings(),
    "js_messages" => array(
        "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete"
    )
);

$page_vars["head_js"] =<<< END
if (typeof google != "undefined") {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(vis_ns.redraw_activity_chart);
}

$(function() {
    if (typeof google == "undefined") {
        $("#no_internet_connection").show();
    }
    if ($("input[name=chart_type]:checked").val() == "column_chart") {
        $("#line_width").attr("disabled", "disabled");
    }
    
    $("#form_id, #date_range, input[name=submission_count_group]").bind("change", vis_ns.update_activity_chart_data);
    $("input[name=chart_type], #colour, #line_width").bind("change keyup", vis_ns.redraw_activity_chart);
    $("#vis_name").bind("blur", vis_ns.redraw_activity_chart);
    
    $("input[name=chart_type]").bind("change", function() {
        if (this.value == "column_chart") {
            $("#line_width").attr("disabled", "disabled");
        } else {
            $("#line_width").attr("disabled", "");
        }
    });
});

$js

var rules = [];
rules.push("required,form_id,{$L["validation_no_form_id"]}");
END;

$module->displayPage("templates/activity_charts/add.tpl", $page_vars);
