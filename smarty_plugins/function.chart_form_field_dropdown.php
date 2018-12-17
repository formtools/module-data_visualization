<?php

use FormTools\Fields;
use FormTools\FieldTypes;

/**
 * Used for Field Charts. Displays a dropdown list of form fields with the appropriate one selected. This dropdown
 * omits fields that have other form fields as their data source - the module doesn't support visualizing that data.
 *
 * If the selected value being passed isn't found in the available list of fields, it's possible the user deleted the
 * field or changed it to reference a different form's field data. In either case, display a message notifying the user,
 * so they have the choice of deleting it.
 *
 * @param $params
 * @param $smarty
 */
function smarty_function_chart_form_field_dropdown($params, &$smarty)
{
	global $L;

	$name_id = $params["name_id"];
	$selected = $params["selected"];
	$form_id = $params["form_id"];

	$field_types = FieldTypes::get(true);

	// Good lord. We *really* need a Core helper for this.
	$data = array();
	foreach ($field_types as $field_type_info) {
		if ($field_type_info["field_type_identifier"] === "dropdown" ||
			$field_type_info["field_type_identifier"] === "multi_select_dropdown" ||
			$field_type_info["field_type_identifier"] === "radio_buttons" ||
			$field_type_info["field_type_identifier"] === "checkboxes") {

			foreach ($field_type_info["settings"] as $setting_info) {
				if ($setting_info["field_type"] == "option_list_or_form_field") {
					$data[$field_type_info["field_type_id"]] = $setting_info["setting_id"];
				}
			}
		}
	}

	$fields = Fields::getFormFields($form_id, array(
		"include_field_settings" => true,
		"include_field_type_info" => true
	));

	$field_list = array();
	$keys = array_keys($data);
	foreach ($fields as $field_info) {
		if (!in_array($field_info["field_type_id"], $keys)) {
			$field_list[] = $field_info;
			continue;
		}

		// if it's a radio, dropdown, etc. see if the field is using a dynamic field
		$list_field_setting_id = $data[$field_info["field_type_id"]];

		if (array_key_exists($list_field_setting_id, $field_info["settings"])) {
			$setting_value = $field_info["settings"][$list_field_setting_id];

			if (substr($setting_value, 0, strlen("form_field:")) !== "form_field:") {
				$field_list[] = $field_info;
			}
		}
	}

	$found = false;

	$lines = array();
	$lines[] = "<select name=\"{$name_id}\" id=\"{$name_id}\">";
	foreach ($field_list as $field_info) {
		$curr_item_selected = ($field_info["field_id"] === $selected) ? "selected" : "";
		if (!empty($curr_item_selected)) {
			$found = true;
		}
		$lines[] = "<option value=\"{$field_info["field_id"]}\" $curr_item_selected>{$field_info["field_title"]}</option>";
	}

	$lines[] = "</select>";

	if (!$found) {
		$lines[] = "<div class=\"error\"><div style=\"padding: 8px\">{$L["notify_missing_field"]}</div></div>";
	}

	echo implode("\n", $lines);
}
