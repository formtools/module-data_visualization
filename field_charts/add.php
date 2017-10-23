<?php

require_once("../../../global/library.php");

use FormTools\Core;
use FormTools\Modules;
use FormTools\Modules\DataVisualization\FieldCharts;
use FormTools\Modules\DataVisualization\General;

$module = Modules::initModulePage("admin");
$L = $module->getLangStrings();
$LANG = Core::$L;

$vis_id  = "";
$form_id = "";
$view_id = "";
$success = true;
$message = "";
if (isset($_POST["add"])) {
    list ($success, $message, $vis_id) = FieldCharts::addFieldChart($request, $L);
    if ($success) {
        header("location: edit.php?vis_id=$vis_id&page=main&is_new");
        exit;
    }
}

$js = General::getFormViewMappingJs();

$page_vars = array(
    "success" => $success,
    "message" => $message,
    "vis_id" => $vis_id,
    "form_id" => $form_id,
    "view_id" => $view_id,
    "module_settings" => $module->getSettings(),
    "js_messages" => array(
        "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete"
    ),
    "module_js_messages" => array("phrase_please_select_view")
);

$page_vars["head_js"] =<<< END
if (typeof google != "undefined") {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(vis_ns.redraw_field_chart);
}

$(function() {
    if (typeof google == "undefined") {
        $("#no_internet_connection").show();
    }
    if ($("input[chart_type]:checked").val() == "pie_chart") {
        $("#colour").attr("disabled", "disabled");
    }
    $("#form_id").val("").bind("change", function() {
        vis_ns.select_form(this.value, false);
    });
    $("#view_id").bind("change", function() {
        vis_ns.select_view(this.value, false);
    });

    $("#field_id").bind("change", function() {
        if (this.value) {
            $("#thumb_chart, #full_size_chart").show();
            $("#thumb_chart_empty, #full_size_chart_empty").hide();
        } else {
            $("#thumb_chart, #full_size_chart").hide();
            $("#thumb_chart_empty, #full_size_chart_empty").show();
        }
    });
    
    $("#view_id, #field_id").attr("disabled", "disabled");
    
    $("#field_id, input[name=field_chart_ignore_empty_fields]").bind("change", vis_ns.update_field_chart_data);
    $("input[name=pie_chart_format], input[name=include_legend_quicklinks], input[name=include_legend_full_size], #colour").bind("change keyup", vis_ns.redraw_field_chart);
    $("#vis_name").bind("blur", vis_ns.redraw_field_chart);
    
    $("input[name=chart_type]").bind("change", function() {
        vis_ns.update_field_chart_data();
        if (this.value == "pie_chart") {
            $("input[name=pie_chart_format], input[name=include_legend_quicklinks], input[name=include_legend_full_size]").attr("disabled", "");
            $("#colour").attr("disabled", "disabled");
        } else {
            $("input[name=pie_chart_format], input[name=include_legend_quicklinks], input[name=include_legend_full_size]").attr("disabled", "disabled");
            $("#colour").attr("disabled", "");
        }
    });
});

$js

var rules = [];
rules.push("required,vis_name,Please enter the name of this visualization.");
rules.push("required,form_id,{$L["validation_no_form_id"]}");
rules.push("required,view_id,{$L["validation_no_view_id"]}");
rules.push("required,field_id,{$L["validation_no_field_id"]}");
END;

$module->displayPage("templates/field_charts/add.tpl", $page_vars);
