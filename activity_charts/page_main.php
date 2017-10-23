<?php

if (isset($_GET["is_new"])) {
    $page_vars["g_success"] = true;
    $page_vars["g_message"] = $L["notify_activity_chart_created"];
}

$page_vars["js_messages"] = array(
    "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete",
    "word_yes", "word_no"
);
$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["head_js"] =<<< END
$(function() {
    $("#delete_visualization").bind("click", function() {
        vis_ns.delete_visualization($vis_id);
    });
});

$js

var rules = [];
rules.push("required,vis_name,Please enter the name of this visualization.");
rules.push("required,form_id,{$L["validation_no_form_id"]}");
END;

$module->displayPage("templates/activity_charts/edit.tpl", $page_vars);
