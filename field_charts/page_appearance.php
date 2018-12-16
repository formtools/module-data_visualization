<?php

$page_vars["js_messages"] = array(
    "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete", "word_yes", "word_no"
);
$page_vars["module_js_messages"] = array(
    "phrase_delete_visualization", "confirm_delete_visualization"
);

$page_vars["head_js"] =<<< END
if (typeof google != "undefined") {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(vis_ns.update_field_chart_data);
}

$(function() {
    if (typeof google == "undefined") {
        $("#no_internet_connection").show();
    }
    if ($("input[name=chart_type]:checked").val() == "pie_chart") {
        $("#colour").attr("disabled", "disabled");
    }
    $("#delete_visualization").bind("click", function() {
        vis_ns.delete_visualization($vis_id);
    });

    $("input[name=pie_chart_format], input[name=include_legend]").bind("change keyup", vis_ns.redraw_field_chart);
    $("#date_range, input[name=field_chart_ignore_empty_fields]").bind("change", vis_ns.update_field_chart_data);
    $("#colour, input[name=pie_chart_format], input[name=include_legend_quicklinks], input[name=include_legend_full_size]").bind("change keyup", vis_ns.redraw_field_chart);
    
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
rules.push("required,form_id,{$L["validation_no_form_id"]}");
rules.push("required,view_id,{$L["validation_no_view_id"]}");
END;

$module->displayPage("templates/field_charts/edit.tpl", $page_vars);
