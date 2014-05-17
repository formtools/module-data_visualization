<?php

require_once("../../global/library.php");
ft_init_module_page();
$request = array_merge($_POST, $_GET);

$num_visualizations_per_page = 10;

if (isset($_GET["delete"]))
{
	list($g_success, $g_message) = dv_delete_visualization($_GET["delete"]);
}

if (isset($_GET["reset"]))
{
  $_GET["keyword"] = "";
  $_GET["form_id"] = "";
  $_GET["view_id"] = "";
  $_GET["vis_types"] = array("activity", "field");
  $_GET["chart_type"] = "";
  $_GET["account_type"] = "admin";
  $_GET["client_id"] = "";
}

// if we're being linked here from the admin's Submission Listing page (i.e. the user just clicked the "Manage Visualizations" button)
// reset everything except the form & View
if (isset($_GET["source"]) && $_GET["source"] == "admin_submission_listing")
{
  $_GET["keyword"] = "";
  $_GET["vis_types"] = array("activity", "field");
  $_GET["chart_type"] = "";
  $_GET["account_type"] = "admin";
  $_GET["client_id"] = "";
}

$keyword        = ft_load_module_field("data_visualization", "keyword", "dv_search_keyword", "");
$search_form_id = ft_load_module_field("data_visualization", "form_id", "dv_form_id", "");
$search_view_id = ft_load_module_field("data_visualization", "view_id", "dv_view_id", "");
$vis_types      = ft_load_module_field("data_visualization", "vis_types", "dv_vis_types", array("activity", "field"));
$chart_type     = ft_load_module_field("data_visualization", "chart_type", "dv_chart_type", "");
$account_type   = ft_load_module_field("data_visualization", "account_type", "dv_account_type", "admin");
$client_id      = ft_load_module_field("data_visualization", "client_id", "dv_client_id", "");

$search_criteria = array(
  "keyword"      => $keyword,
  "form_id"      => $search_form_id,
  "view_id"      => $search_view_id,
  "vis_types"    => $vis_types,
  "chart_type"   => $chart_type,
  "account_type" => $account_type,
  "client_id"    => $client_id
    );

$results = dv_search_visualizations($search_criteria);
$total_results = dv_get_num_visualizations();
$js = dv_get_form_view_mapping_js();

$module_settings = ft_get_module_settings("", "data_visualization");

// get the list of visualization IDs for use in the page
$vis_ids = array();
foreach ($results as $vis_info)
{
	$vis_ids[] = $vis_info["vis_id"];
}
$vis_id_str = implode(",", $vis_ids);

$vis_messages = dv_get_vis_messages($L);

// ------------------------------------------------------------------------------------------------

$page_vars = array();
$page_vars["results"] = $results;
$page_vars["total_results"] = $total_results;
$page_vars["num_visualizations_per_page"] = $num_visualizations_per_page;
$page_vars["keyword"] = $keyword;
$page_vars["search_form_id"] = $search_form_id;
$page_vars["search_view_id"] = $search_view_id;
$page_vars["keyword"] = $keyword;
$page_vars["vis_types"] = $vis_types;
$page_vars["chart_type"] = $chart_type;
$page_vars["account_type"] = $account_type;
$page_vars["client_id"] = $client_id;
$page_vars["js_messages"] = array("word_delete", "word_edit", "phrase_please_select_form", "phrase_please_select", "word_yes", "word_no");
$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["pagination"] = ft_get_dhtml_page_nav(count($results), $num_visualizations_per_page, 1);

$page_vars["head_string"] =<<<END
  <script src="https://www.google.com/jsapi"></script>
  <link type="text/css" rel="stylesheet" href="global/css/styles.css">
  <script src="global/scripts/manage_visualizations.js"></script>
  <script src="global/scripts/visualizations.js?v=2"></script>
  <link type="text/css" rel="stylesheet" href="global/css/visualizations.css">

<style type="text/css">
#dv_vis_tiles li {
  width: {$module_settings["quicklinks_dialog_thumb_size"]}px;
  height: {$module_settings["quicklinks_dialog_thumb_size"]}px;
}
</style>
END;

$page_vars["head_js"] =<<< END
$(function() {
  if (typeof google == "undefined") {
    $("#no_internet_connection").show();
    $("#view_visualizations").hide();
  }
  dv_ns.context = "manage_visualizations";
  $("#view_visualizations").bind("click", dv_ns.show_visualizations_dialog);
  $(".dv_vis_tile_enlarge").live("click", dv_ns.enlarge_visualization);
  $("#dv_vis_full_nav li.back span").live("click", dv_ns.return_to_overview);
  $("#dv_vis_full_nav li.prev span").live("click", dv_ns.show_prev_visualization);
  $("#dv_vis_full_nav li.next span").live("click", dv_ns.show_next_visualization);

  $("#client_id").bind("change", function() { $("#at2").attr("checked", "checked"); });
  $("#search_form form").bind("submit", function() {
    var errors = [];
    if (!$("#vt1").attr("checked") && !$("#vt2").attr("checked")) {
      errors.push("{$L["validation_no_vis_type_selected"]}");
    }
    if (!$("#at1").attr("checked") && !$("#at2").attr("checked")) {
      errors.push("{$L["validation_no_account_type_selected"]}");
    }
    if (errors.length) {
      var error_str = errors.join("<br />", errors);
      ft.display_message("ft_message", 0, error_str);
      return false;
    }
  });

  $("#form_id").bind("change keyup", function() {
    vis_ns.select_form(this.value);
  });

  $("#create_visualization_dialog li").bind("click", function() {
    var vis_type = $(this).find(".visualization_type").val();
    switch (vis_type) {
      case "activity_chart":
        window.location = 'activity_charts/add.php';
        break;
      case "field_chart":
        window.location = 'field_charts/add.php';
        break;
    }
  });

  $("#create_visualization").bind("click", function() {
    ft.create_dialog({
      dialog: $("#create_visualization_dialog"),
      title:  "{$L["phrase_select_visualization"]}",
      min_width: 650
    });
  });

  vis_ns.default_view_label = "{$L["phrase_all_views"]}";
});

g.quicklinks_dialog_width = {$module_settings["quicklinks_dialog_width"]};
g.quicklinks_dialog_height = {$module_settings["quicklinks_dialog_height"]};
g.vis_tile_size = {$module_settings["quicklinks_dialog_thumb_size"]};

$vis_messages

g.vis_ids = [$vis_id_str];
$js
END;


ft_display_module_page("templates/index.tpl", $page_vars);
