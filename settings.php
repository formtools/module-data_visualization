<?php

require_once("../../global/library.php");

use FormTools\Core;
use FormTools\Modules;
use FormTools\Modules\DataVisualization\Visualizations;

$module = Modules::initModulePage("admin");
$L = $module->getLangStrings();
$LANG = Core::$L;

$success = true;
$message = "";
if (isset($request["clear_cache"])) {
	list($success, $message) = Visualizations::clearVisualizationCache();
}

if (isset($request["update"])) {
	$settings = array(
		"quicklinks_dialog_width" => $request["quicklinks_dialog_width"],
		"quicklinks_dialog_height" => $request["quicklinks_dialog_height"],
		"quicklinks_dialog_thumb_size" => $request["quicklinks_dialog_thumb_size"],
		"default_cache_frequency" => $request["default_cache_frequency"],
		"hide_from_client_accounts" => $request["hide_from_client_accounts"],
		"clients_may_refresh_cache" => $request["clients_may_refresh_cache"]
	);
	Modules::setModuleSettings($settings);

	$success = true;
	$message = $L["notify_settings_updated"];
}

$page_vars = array(
	"g_success" => $success,
	"g_message" => $message,
	"module_settings" => $module->getSettings()
);

$page_vars["head_js"] = <<< END
var rules = [];
rules.push("required,quicklinks_dialog_width,{$L["validation_no_dialog_width"]}");
rules.push("digits_only,quicklinks_dialog_width,{$L["validation_invalid_dialog_width"]}");
rules.push("required,quicklinks_dialog_height,{$L["validation_no_dialog_height"]}");
rules.push("digits_only,quicklinks_dialog_height,{$L["validation_invalid_dialog_height"]}");
rules.push("required,quicklinks_dialog_thumb_size,{$L["validation_no_visualization_thumb_size"]}");
rules.push("digits_only,quicklinks_dialog_thumb_size,{$L["validation_no_default_thumb_size"]}");
END;

$module->displayPage("templates/settings.tpl", $page_vars);
