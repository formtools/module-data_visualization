<?php

require_once("../../../global/library.php");
ft_init_module_page();
$request = array_merge($_POST, $_GET);

$vis_id = "";
$form_id = "";
$view_id = "";
if (isset($_POST["add"]))
{
	list($g_success, $g_message, $vis_id) = dv_add_activity_chart($request);
  $form_id = $request["form_id"];
  $view_id = isset($request["view_id"]) ? $request["view_id"] : "";
}

$js = dv_get_form_view_mapping_js();
$module_settings = ft_get_module_settings("", "data_visualization");

$page_vars = array();
$page_vars["vis_id"] = $vis_id;
$page_vars["form_id"] = $form_id;
$page_vars["view_id"] = $view_id;
$page_vars["module_settings"] = $module_settings;
$page_vars["js_messages"] = array("phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete");
$page_vars["head_string"] =<<< END
<script src="../global/scripts/manage_visualizations.js"></script>
<link type="text/css" rel="stylesheet" href="../global/css/styles.css">
<script src="https://www.google.com/jsapi"></script>
END;

$page_vars["head_js"] =<<< END
if (typeof google != "undefined") {
  google.load("visualization", "1", {packages:["corechart"]});
}

$(function() {
  if (typeof google == "undefined") {
    $("#no_internet_connection").show();
  }
  vis_ns.redraw_activity_chart();
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

ft_display_module_page("templates/activity_charts/add.tpl", $page_vars);
