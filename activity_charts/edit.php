<?php

require_once("../../../global/library.php");
ft_init_module_page();
$request = array_merge($_POST, $_GET);

$vis_id = isset($request["vis_id"]) ? $request["vis_id"] : "";
if (empty($vis_id) || !is_numeric($vis_id))
{
  header("location: ../");
  exit;
}

if (isset($_POST["update"]))
{
	list($g_success, $g_message) = dv_update_activity_chart($request["vis_id"], $request["tab"], $request);
}

$vis_info = dv_get_visualization($vis_id);
$js = dv_get_form_view_mapping_js();

// store the current selected tab in memory
$page = ft_load_module_field("data_visualization", "page", "edit_chart", "main");

$same_page = ft_get_clean_php_self();
$tabs = array(
  "main"        => array("tab_label" => $LANG["word_main"], "tab_link" => "{$same_page}?page=main&vis_id={$vis_id}"),
  "appearance"  => array("tab_label" => $L["word_appearance"], "tab_link" => "{$same_page}?page=appearance&vis_id={$vis_id}"),
  "permissions" => array("tab_label" => $LANG["word_permissions"], "tab_link" => "{$same_page}?page=permissions&vis_id={$vis_id}"),
  "advanced"    => array("tab_label" => $LANG["word_advanced"], "tab_link" => "{$same_page}?page=advanced&vis_id={$vis_id}")
);

// start compiling the page vars here (save duplicate code!)
$page_vars = array();
$page_vars["page"] = $page;
$page_vars["tabs"] = $tabs;
$page_vars["show_tabset_nav_links"] = true;

$links = dv_get_tabset_links($vis_id);
$page_vars["prev_tabset_link"] = $links["prev_link"];
$page_vars["next_tabset_link"] = $links["next_link"];

$page_vars["vis_id"] = $vis_id;
$page_vars["vis_info"] = $vis_info;

switch ($page)
{
  case "main":
    include("page_main.php");
    break;
  case "appearance":
    include("page_appearance.php");
    break;
  case "permissions":
    include("page_permissions.php");
    break;
  case "advanced":
    include("page_advanced.php");
    break;

  default:
    include("page_main.php");
    break;
}


















