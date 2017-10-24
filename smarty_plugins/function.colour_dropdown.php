<?php

function smarty_function_colour_dropdown($params, &$smarty)
{
    global $L;

    $name_id = $params["name_id"];
    $default = $params["default"];

    $lines = array();
    $lines[] = "<select name=\"{$name_id}\" id=\"{$name_id}\">";
    $lines[] = "<option value=\"red\" " . (($default == "red") ? "selected" : "") . ">{$L["word_red"]}</option>";
    $lines[] = "<option value=\"orange\" " . (($default == "orange") ? "selected" : "") . ">{$L["word_orange"]}</option>";
    $lines[] = "<option value=\"yellow\" " . (($default == "yellow") ? "selected" : "") . ">{$L["word_yellow"]}</option>";
    $lines[] = "<option value=\"green\" " . (($default == "green") ? "selected" : "") . ">{$L["word_green"]}</option>";
    $lines[] = "<option value=\"blue\" " . (($default == "blue") ? "selected" : "") . ">{$L["word_blue"]}</option>";
    $lines[] = "<option value=\"indigo\" " . (($default == "indigo") ? "selected" : "") . ">{$L["word_indigo"]}</option>";
    $lines[] = "<option value=\"violet\" " . (($default == "violet") ? "selected" : "") . ">{$L["word_violet"]}</option>";
    $lines[] = "<option value=\"black\" " . (($default == "black") ? "selected" : "") . ">{$L["word_black"]}</option>";
    $lines[] = "<option value=\"gray\" " . (($default == "gray") ? "selected" : "") . ">{$L["word_grey"]}</option>";
    $lines[] = "</select>";

    echo implode("\n", $lines);
}
