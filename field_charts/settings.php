<?php

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
        "field_chart_default_chart_type"   => $request["field_chart_default_chart_type"],
        "field_chart_ignore_empty_fields"  => $request["field_chart_ignore_empty_fields"]
	);

	if ($request["field_chart_default_chart_type"] == "pie_chart") {
		$settings["field_chart_pie_chart_format"]          = $request["field_chart_pie_chart_format"];
		$settings["field_chart_include_legend_quicklinks"] = $request["field_chart_include_legend_quicklinks"];
		$settings["field_chart_include_legend_full_size"]  = $request["field_chart_include_legend_full_size"];
	} else {
		$settings["field_chart_colour"] = $request["field_chart_colour"];
	}

	// line width only gets submitted for line and area charts
    if (isset($request["field_chart_pie_chart_format"])) {
        $settings["field_chart_pie_chart_format"] = $request["field_chart_pie_chart_format"];
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

    $("input[name=pie_chart_format], input[name=field_chart_ignore_empty_fields], input[name=field_chart_pie_chart_format], input[name=field_chart_include_legend_quicklinks], input[name=field_chart_include_legend_full_size], #field_chart_colour").bind("change keyup", function() {
        drawChart();
    });

    $("input[name=field_chart_default_chart_type]").bind("change", function() {
        drawChart();
        if (this.value == "pie_chart") {
            $("input[name=field_chart_pie_chart_format], input[name=field_chart_include_legend_quicklinks], input[name=field_chart_include_legend_full_size]").attr("disabled", "");
            $("#field_chart_colour").attr("disabled", "disabled");
        } else {
            $("input[name=field_chart_pie_chart_format], input[name=field_chart_include_legend_quicklinks], input[name=field_chart_include_legend_full_size]").attr("disabled", "disabled");
            $("#field_chart_colour").attr("disabled", "");
        }
    });
});

function drawChart() {
    if (typeof google == "undefined") {
        return;
    }
    var chart_type          = $("input[name=field_chart_default_chart_type]:checked").val();
    var ignore_empty_fields = $("input[name=field_chart_ignore_empty_fields]:checked").val();

    var data = new google.visualization.DataTable();
    data.addColumn('string', "Attendee Type");
    data.addColumn('number', 'Count');

    if (ignore_empty_fields == "yes") {
        data.addRows(5);
        data.setValue(0, 0, "Attendees");
        data.setValue(0, 1, 60);
        data.setValue(1, 0, "Guests");
        data.setValue(1, 1, 14);
        data.setValue(2, 0, "Presenters");
        data.setValue(2, 1, 20);
        data.setValue(3, 0, "Faculty");
        data.setValue(3, 1, 2);
        data.setValue(4, 0, "Employees");
        data.setValue(4, 1, 4);
    } else {
        data.addRows(6);
        data.setValue(0, 0, "");
        data.setValue(0, 1, 10);
        data.setValue(1, 0, "Attendees");
        data.setValue(1, 1, 72);
        data.setValue(2, 0, "Guests");
        data.setValue(2, 1, 14);
        data.setValue(3, 0, "Presenters");
        data.setValue(3, 1, 20);
        data.setValue(4, 0, "Faculty");
        data.setValue(4, 1, 2);
        data.setValue(5, 0, "Employees");
        data.setValue(5, 1, 4);
    }

    switch (chart_type) {
        case "pie_chart":
            var thumb_chart = new google.visualization.PieChart(document.getElementById("thumb_chart"));
            var full_size_chart = new google.visualization.PieChart(document.getElementById("full_size_chart"));
            break;
        case "bar_chart":
            var thumb_chart = new google.visualization.BarChart(document.getElementById("thumb_chart"));
            var full_size_chart = new google.visualization.BarChart(document.getElementById("full_size_chart"));
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
            title:  'Attendee Types',
            legend: 'none'
        };
        var full_size_settings = {
            width:  730,
            height: 350,
            title: 'Attendee Types',
            legend: 'none'
        };
    
        if (chart_type == "pie_chart") {
            var pie_chart_format          = $("input[name=field_chart_pie_chart_format]:checked").val();
            var include_legend_quicklinks = $("input[name=field_chart_include_legend_quicklinks]:checked").val();
            var include_legend_full_size  = $("input[name=field_chart_include_legend_full_size]:checked").val();
            thumb_settings.is3D   = (pie_chart_format == "3D") ? true : false;
            thumb_settings.legend = (include_legend_quicklinks == "yes") ? "right" : "none";
            full_size_settings.is3D   = (pie_chart_format == "3D") ? true : false;
            full_size_settings.legend = (include_legend_full_size == "yes") ? "right" : "none";
        } else {
            var colour = $("#field_chart_colour").val();
            thumb_settings.colors     = [colour];
            full_size_settings.colors = [colour];
        }

        thumb_chart.draw(data, thumb_settings);
        full_size_chart.draw(data, full_size_settings);
    }
}
END;

$module->displayPage("templates/field_charts/settings.tpl", $page_vars);
