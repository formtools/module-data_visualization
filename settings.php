<?php

require_once("../../global/library.php");
ft_init_module_page();
$request = array_merge($_POST, $_GET);

if (isset($request["clear_cache"]))
{
	list($g_success, $g_message) = dv_clear_visualization_cache();
}

if (isset($request["update"]))
{
	$settings = array(
	  "quicklinks_dialog_width"      => $request["quicklinks_dialog_width"],
	  "quicklinks_dialog_height"     => $request["quicklinks_dialog_height"],
	  "quicklinks_dialog_thumb_size" => $request["quicklinks_dialog_thumb_size"],
	  "default_cache_frequency"      => $request["default_cache_frequency"],
	  "clients_may_refresh_cache"    => $request["clients_may_refresh_cache"]
	);
	ft_set_module_settings($settings);

	$g_success = true;
	$g_message = $L["notify_settings_updated"];
}

$module_settings = ft_get_module_settings();

$page_vars = array();
$page_vars["module_settings"] = $module_settings;
$page_vars["head_js"] =<<< END

var rules = [];
rules.push("required,quicklinks_dialog_width,{$L["validation_no_dialog_width"]}");
rules.push("digits_only,quicklinks_dialog_width,{$L["validation_invalid_dialog_width"]}");
rules.push("required,quicklinks_dialog_height,{$L["validation_no_dialog_height"]}");
rules.push("digits_only,quicklinks_dialog_height,{$L["validation_invalid_dialog_height"]}");
rules.push("required,quicklinks_dialog_thumb_size,{$L["validation_no_visualization_thumb_size"]}");
rules.push("digits_only,quicklinks_dialog_thumb_size,{$L["validation_no_default_thumb_size"]}");

END;

ft_display_module_page("templates/settings.tpl", $page_vars);













































