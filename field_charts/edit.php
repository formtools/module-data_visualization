<?php

require_once("../../../global/library.php");

use FormTools\Core;
use FormTools\General as CoreGeneral;
use FormTools\Modules;
use FormTools\Modules\DataVisualization\FieldCharts;
use FormTools\Modules\DataVisualization\General;
use FormTools\Modules\DataVisualization\Visualizations;

$module = Modules::initModulePage("admin");
$L = $module->getLangStrings();
$LANG = Core::$L;

$vis_id = isset($request["vis_id"]) ? $request["vis_id"] : "";
if (empty($vis_id) || !is_numeric($vis_id)) {
	header("location: ../");
	exit;
}

$success = true;
$message = "";
if (isset($_POST["update"])) {
	list ($success, $message) = FieldCharts::updateFieldChart($request["vis_id"], $request["tab"], $request, $L);
}

$vis_info = Visualizations::getVisualization($vis_id, $L);
$js = General::getFormViewMappingJs();

// store the current selected tab in memory
$page = Modules::loadModuleField("data_visualization", "page", "edit_chart", "main");

$same_page = CoreGeneral::getCleanPhpSelf();
$tabs = array(
	"main" => array("tab_label" => $LANG["word_main"], "tab_link" => "{$same_page}?page=main&vis_id={$vis_id}"),
	"appearance" => array("tab_label" => $L["word_appearance"], "tab_link" => "{$same_page}?page=appearance&vis_id={$vis_id}"),
	"permissions" => array("tab_label" => $LANG["word_permissions"], "tab_link" => "{$same_page}?page=permissions&vis_id={$vis_id}"),
	"advanced" => array("tab_label" => $L["word_advanced"], "tab_link" => "{$same_page}?page=advanced&vis_id={$vis_id}")
);

$links = Visualizations::getTabsetLinks($vis_id);

// start compiling the page vars here (save duplicate code!)
$page_vars = array(
	"g_success" => $success,
	"g_message" => $message,
	"page" => $page,
	"tabs" => $tabs,
	"show_tabset_nav_links" => true,
	"prev_tabset_link" => $links["prev_link"],
	"next_tabset_link" => $links["next_link"],
	"vis_id" => $vis_id,
	"vis_info" => $vis_info
);

switch ($page) {
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















