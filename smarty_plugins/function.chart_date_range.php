<?php


function smarty_function_chart_date_range($params, &$smarty)
{
	global $L;

	$name_id = $params["name_id"];
	$default = $params["default"];

	$lines = array();
	$lines[] = "<select name=\"$name_id\" id=\"$name_id\">";
	$lines[] = "<option value=\"everything\" " . (($default == "everything") ? "selected" : "") . ">{$L["phrase_all_time"]}</option>";
	$lines[] = "<optgroup label=\"{$L["phrase_days_or_weeks"]}\">";
	$lines[] = "<option value=\"last_7_days\" " . (($default == "last_7_days") ? "selected" : "") . ">{$L["phrase_last_7_days"]}</option>";
	$lines[] = "<option value=\"last_10_days\" " . (($default == "last_10_days") ? "selected" : "") . ">{$L["phrase_last_10_days"]}</option>";
	$lines[] = "<option value=\"last_14_days\" " . (($default == "last_14_days") ? "selected" : "") . ">{$L["phrase_last_2_weeks"]}</option>";
	$lines[] = "<option value=\"last_21_days\" " . (($default == "last_21_days") ? "selected" : "") . ">{$L["phrase_last_3_weeks"]}</option>";
	$lines[] = "<option value=\"last_30_days\" " . (($default == "last_30_days") ? "selected" : "") . ">{$L["phrase_last_30_days"]}</option>";
	$lines[] = "</optgroup>";
	$lines[] = "<optgroup label=\"{$L["word_months"]}\">";
	$lines[] = "<option value=\"year_to_date\" " . (($default == "year_to_date") ? "selected" : "") . ">{$L["phrase_year_to_date"]}</option>";
	$lines[] = "<option value=\"month_to_date\" " . (($default == "month_to_date") ? "selected" : "") . ">{$L["phrase_month_to_date"]}</option>";
	$lines[] = "<option value=\"last_2_months\" " . (($default == "last_2_months") ? "selected" : "") . ">{$L["phrase_last_2_months"]}</option>";
	$lines[] = "<option value=\"last_3_months\" " . (($default == "last_3_months") ? "selected" : "") . ">{$L["phrase_last_3_months"]}</option>";
	$lines[] = "<option value=\"last_4_months\" " . (($default == "last_4_months") ? "selected" : "") . ">{$L["phrase_last_4_months"]}</option>";
	$lines[] = "<option value=\"last_5_months\" " . (($default == "last_5_months") ? "selected" : "") . ">{$L["phrase_last_5_months"]}</option>";
	$lines[] = "<option value=\"last_6_months\" " . (($default == "last_6_months") ? "selected" : "") . ">{$L["phrase_last_6_months"]}</option>";
	$lines[] = "</optgroup>";
	$lines[] = "<optgroup label=\"{$L["word_years"]}\">";
	$lines[] = "<option value=\"last_12_months\" " . (($default == "last_12_months") ? "selected" : "") . ">{$L["phrase_last_12_months"]}</option>";
	$lines[] = "<option value=\"last_2_years\" " . (($default == "last_2_years") ? "selected" : "") . ">{$L["phrase_last_2_years"]}</option>";
	$lines[] = "<option value=\"last_3_years\" " . (($default == "last_3_years") ? "selected" : "") . ">{$L["phrase_last_3_years"]}</option>";
	$lines[] = "<option value=\"last_4_years\" " . (($default == "last_4_years") ? "selected" : "") . ">{$L["phrase_last_4_years"]}</option>";
	$lines[] = "<option value=\"last_5_years\" " . (($default == "last_5_years") ? "selected" : "") . ">{$L["phrase_last_5_years"]}</option>";
	$lines[] = "</optgroup>";
	$lines[] = "</select>";

	echo implode("\n", $lines);
}
