<?php

use FormTools\Fields;

$fields = Fields::getFormFields($vis_info["form_id"]);

if (isset($_GET["is_new"])) {
    $page_vars["g_success"] = true;
    $page_vars["g_message"] = $L["notify_field_chart_created"];
}

$page_vars["form_fields"] = $fields;
$page_vars["js_messages"] = array(
    "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete", "word_yes", "word_no"
);
$page_vars["module_js_messages"] = array(
    "phrase_delete_visualization", "confirm_delete_visualization"
);
$page_vars["head_js"] =<<< END
$(function() {
    $("#form_id").bind("change", function() {
        vis_ns.select_form(this.value, false);
    });
    $("#delete_visualization").bind("click", function() {
        vis_ns.delete_visualization($vis_id);
    });
});

$js

var rules = [];
rules.push("required,form_id,{$L["validation_no_form_id"]}");
rules.push("required,view_id,{$L["validation_no_view_id"]}");
END;

$module->displayPage("templates/field_charts/edit.tpl", $page_vars);
