<?php

$page_vars["js_messages"] = array("phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete",
  "word_yes", "word_no");
$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["head_string"] =<<< END
<script src="../global/scripts/manage_visualizations.js"></script>
<link type="text/css" rel="stylesheet" href="../global/css/styles.css">
END;

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

ft_display_module_page("templates/activity_charts/edit.tpl", $page_vars);
