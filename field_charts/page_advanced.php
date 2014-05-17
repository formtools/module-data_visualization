<?php

$pages_module_available = ft_check_module_enabled("pages");

if (isset($_GET["msg"]) && $_GET["msg"] == "page_created")
{
  $g_success = true;
  $g_message = $L["notify_page_created_and_assigned"];
}

$page_vars["pages_module_available"] = $pages_module_available;
$page_vars["vis_id"] = $vis_id;
$page_vars["vis_info"] = $vis_info;
$page_vars["js_messages"] = array("phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete",
   "word_cancel", "word_yes", "word_no");
$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["head_js"] =<<< END
$(function() {
  vis_ns.init_create_page_and_menu_item_dialog();
  $("#menu_id").bind("change", function() {
    vis_ns.select_menu(this.value);
  });
  $("#delete_visualization").bind("click", function() {
    vis_ns.delete_visualization($vis_id);
  });

});
END;
$page_vars["head_string"] =<<< END
<script src="../global/scripts/manage_visualizations.js"></script>
<link type="text/css" rel="stylesheet" href="../global/css/styles.css">
END;

ft_display_module_page("templates/field_charts/edit.tpl", $page_vars);
