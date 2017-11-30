<?php

/**
 * N.B. The settings page field names don't have the "activity_chart_" prefix because I wanted to re-use the
 * same JS on both this page and the Add and Edit Activity Chart pages without any additional work.
 */
require_once("../../../global/library.php");

use FormTools\Core;
use FormTools\Modules;

$module = Modules::initModulePage("admin");
$L = $module->getLangStrings();
$LANG = Core::$L;

$success = true;
$message = "";
if (isset($_POST["update"])) {
    $settings = array(
        "activity_chart_date_range"             => $request["date_range"],
        "activity_chart_submission_count_group" => $request["submission_count_group"],
        "activity_chart_default_chart_type"     => $request["chart_type"],
        "activity_chart_colour"                 => $request["colour"],
    );

    // line width only gets submitted for line and area charts
    if (isset($request["line_width"])) {
        $settings["activity_chart_line_width"] = $request["line_width"];
    }

    Modules::setModuleSettings($settings);
    $message = $L["notify_settings_updated"];
}

$page_vars = array(
    "g_success" => $success,
    "g_message" => $message,
    "module_settings" => $module->getSettings()
);

$page_vars["head_js"] =<<< END
if (typeof google != "undefined") {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
}
      
$(function() {
    if (typeof google == "undefined") {
        $("#no_internet_connection").show();
    }

    $("input[name=chart_type], input[name=submission_count_group], #colour, #line_width").bind("change keyup", drawChart);

    $("input[name=chart_type]").bind("change", function() {
        if (this.value == "column_chart") {
            $("#line_width").attr("disabled", "disabled");
        } else {
            $("#line_width").attr("disabled", "");
        }
    });
});

function drawChart() {
    if (typeof google == "undefined") {
        return;
    }
    var colour     = $("#colour").val();
    var chart_type = $("input[name=chart_type]:checked").val();
    var submission_count_group = $("input[name=submission_count_group]:checked").val();
    var line_width = $("#line_width").val();

    var data = new google.visualization.DataTable();
    data.addColumn('string', "Day");
    data.addColumn('number', 'Submission Count');
    data.addRows(5);

    switch (submission_count_group) {
        case "month":
            data.setValue(0, 0, "Jan 2011");
            data.setValue(0, 1, 10);
            data.setValue(1, 0, "Feb 2011");
            data.setValue(1, 1, 14);
            data.setValue(2, 0, "Mar 2011");
            data.setValue(2, 1, 20);
            data.setValue(3, 0, "Apr 2011");
            data.setValue(3, 1, 2);
            data.setValue(4, 0, "May 2011");
            data.setValue(4, 1, 4);
            break;
        case "day":
            data.setValue(0, 0, "Jan 1");
            data.setValue(0, 1, 10);
            data.setValue(1, 0, "Jan 2");
            data.setValue(1, 1, 14);
            data.setValue(2, 0, "Jan 3");
            data.setValue(2, 1, 20);
            data.setValue(3, 0, "Jan 4");
            data.setValue(3, 1, 2);
            data.setValue(4, 0, "Jan 5");
            data.setValue(4, 1, 4);
            break;
    }
    
    switch (chart_type) {
        case "line_chart":
            var thumb_chart = new google.visualization.LineChart(document.getElementById("thumb_chart"));
            var full_size_chart = new google.visualization.LineChart(document.getElementById("full_size_chart"));
            break;
        case "area_chart":
            var thumb_chart = new google.visualization.AreaChart(document.getElementById("thumb_chart"));
            var full_size_chart = new google.visualization.AreaChart(document.getElementById("full_size_chart"));
            break;
        case "column_chart":
            var thumb_chart = new google.visualization.ColumnChart(document.getElementById("thumb_chart"));
            var full_size_chart = new google.visualization.ColumnChart(document.getElementById("full_size_chart"));
            break;
    }
    
    if (thumb_chart) {
        var thumb_settings = {
            width:  250,
            height: 160,
            title:  'Example Form',
            legend: 'none',
            colors: [colour],
            lineWidth: line_width
        };
        var full_size_settings = {
            width:  730,
            height: 350,
            title:  'Example Form',
            legend: 'none',
            colors: [colour],
            lineWidth: line_width
        };
    
        thumb_chart.draw(data, thumb_settings);
        full_size_chart.draw(data, full_size_settings);
    }
}
END;

$module->displayPage("templates/activity_charts/settings.tpl", $page_vars);
