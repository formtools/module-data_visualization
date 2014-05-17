<?php

require_once("../../../global/library.php");
ft_init_module_page();
$request = array_merge($_POST, $_GET);

$vis_id  = "";
$form_id = "";
$view_id = "";
if (isset($_POST["add"]))
{
	list($g_success, $g_message, $vis_id) = dv_add_field_chart($request);
  $form_id = $request["form_id"];
  $view_id = $request["view_id"];
}

$js = dv_get_form_view_mapping_js();
$module_settings = ft_get_module_settings("", "data_visualization");

$page_vars = array();
$page_vars["vis_id"] = $vis_id;
$page_vars["form_id"] = $form_id;
$page_vars["view_id"] = $view_id;
$page_vars["module_settings"] = $module_settings;
$page_vars["js_messages"] = array("phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete");
$page_vars["module_js_messages"] = array("phrase_please_select_view");
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
  vis_ns.redraw_field_chart();

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

ft_display_module_page("templates/field_charts/add.tpl", $page_vars);
