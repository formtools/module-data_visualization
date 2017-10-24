<?php

function smarty_function_cache_frequency_dropdown($params, &$smarty)
{
    global $L;

    $name_id = $params["name_id"];
    $default = $params["default"];

    $lines = array();
    $lines[] = "<select name=\"{$name_id}\" id=\"{$name_id}\">";
    $lines[] = "<option value=\"no_cache\" " . (($default == "no_cache") ? "selected" : "") . ">{$L["phrase_do_not_cache"]}</option>";
    $lines[] = "<optgroup label=\"{$L["word_minutes"]}\">";
    $lines[] = "<option value=\"2\" " . (($default == "2") ? "selected" : "") . ">{$L["phrase_every_2_mins"]}</option>";
    $lines[] = "<option value=\"5\" " . (($default == "5") ? "selected" : "") . ">{$L["phrase_every_5_mins"]}</option>";
    $lines[] = "<option value=\"10\" " . (($default == "10") ? "selected" : "") . ">{$L["phrase_every_10_mins"]}</option>";
    $lines[] = "<option value=\"15\" " . (($default == "15") ? "selected" : "") . ">{$L["phrase_every_15_mins"]}</option>";
    $lines[] = "<option value=\"20\" " . (($default == "20") ? "selected" : "") . ">{$L["phrase_every_20_mins"]}</option>";
    $lines[] = "<option value=\"30\" " . (($default == "30") ? "selected" : "") . ">{$L["phrase_every_30_mins"]}</option>";
    $lines[] = "<option value=\"45\" " . (($default == "45") ? "selected" : "") . ">{$L["phrase_every_45_mins"]}</option>";
    $lines[] = "</optgroup>";
    $lines[] = "<optgroup label=\"{$L["word_hours"]}\">";
    $lines[] = "<option value=\"60\" " . (($default == "60") ? "selected" : "") . ">{$L["phrase_every_hour"]}</option>";
    $lines[] = "<option value=\"120\" " . (($default == "120") ? "selected" : "") . ">{$L["phrase_every_2_hours"]}</option>";
    $lines[] = "<option value=\"180\" " . (($default == "180") ? "selected" : "") . ">{$L["phrase_every_3_hours"]}</option>";
    $lines[] = "<option value=\"240\" " . (($default == "240") ? "selected" : "") . ">{$L["phrase_every_4_hours"]}</option>";
    $lines[] = "<option value=\"360\" " . (($default == "360") ? "selected" : "") . ">{$L["phrase_every_6_hours"]}</option>";
    $lines[] = "<option value=\"720\" " . (($default == "720") ? "selected" : "") . ">{$L["phrase_every_12_hours"]}</option>";
    $lines[] = "</optgroup>";
    $lines[] = "<optgroup label=\"{$L["word_days"]}\">";
    $lines[] = "<option value=\"1440\" " . (($default == "1440") ? "selected" : "") . ">{$L["phrase_every_day"]}</option>";
    $lines[] = "<option value=\"2880\" " . (($default == "2880") ? "selected" : "") . ">{$L["phrase_every_2_days"]}</option>";
    $lines[] = "<option value=\"4320\" " . (($default == "4320") ? "selected" : "") . ">{$L["phrase_every_3_days"]}</option>";
    $lines[] = "<option value=\"10080\" " . (($default == "10080") ? "selected" : "") . ">{$L["phrase_every_7_days"]}</option>";
    $lines[] = "<option value=\"20160\" " . (($default == "20160") ? "selected" : "") . ">{$L["phrase_every_14_days"]}</option>";
    $lines[] = "<option value=\"43200\" " . (($default == "43200") ? "selected" : "") . ">{$L["phrase_every_30_days"]}</option>";
    $lines[] = "</optgroup>";
    $lines[] = "</select>";

    echo implode("\n", $lines);
}
