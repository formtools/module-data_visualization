<?php

use FormTools\Submissions;

$num_submissions = Submissions::getSubmissionCount($vis_info["form_id"], $vis_info["view_id"]);

$page_vars["has_submissions_in_view"] = ($num_submissions > 0) ? "yes" : "no";
$page_vars["js_messages"] = array(
    "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete",
    "word_yes", "word_no"
);
$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["head_js"] =<<< END
if (typeof google != "undefined") {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(vis_ns.update_activity_chart_data);
}

$(function() {
    if (typeof google == "undefined") {
        $("#no_internet_connection").show();
    }
    if ($("#has_submissions_in_view").val() == "no") {
        $("#no_data_message").show();
    }
    if ($("input[name=chart_type]:checked").val() == "column_chart") {
        $("#line_width").attr("disabled", "disabled");
    }
    $("#delete_visualization").bind("click", function() {
        vis_ns.delete_visualization($vis_id);
    });
    
    $("#date_range, input[name=submission_count_group]").bind("change", vis_ns.update_activity_chart_data);
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
END;

$module->displayPage("templates/activity_charts/edit.tpl", $page_vars);
