<?php

function smarty_function_line_width_dropdown($params, &$smarty)
{
    global $L;

    $name_id = $params["name_id"];
    $default = $params["default"];

    $lines = array();
    $lines[] = "<select name=\"{$name_id}\" id=\"{$name_id}\">";
    $lines[] = "<option value=\"1\" " . (($default == "1") ? "selected" : "") . ">1</option>";
    $lines[] = "<option value=\"2\" " . (($default == "2") ? "selected" : "") . ">2</option>";
    $lines[] = "<option value=\"3\" " . (($default == "3") ? "selected" : "") . ">3</option>";
    $lines[] = "<option value=\"4\" " . (($default == "4") ? "selected" : "") . ">4</option>";
    $lines[] = "<option value=\"5\" " . (($default == "5") ? "selected" : "") . ">5</option>";
    $lines[] = "<option value=\"6\" " . (($default == "6") ? "selected" : "") . ">6</option>";
    $lines[] = "<option value=\"7\" " . (($default == "7") ? "selected" : "") . ">7</option>";
    $lines[] = "<option value=\"8\" " . (($default == "8") ? "selected" : "") . ">8</option>";
    $lines[] = "<option value=\"9\" " . (($default == "9") ? "selected" : "") . ">9</option>";
    $lines[] = "<option value=\"10\" " . (($default == "10") ? "selected" : "") . ">10</option>";
    $lines[] = "</select>";

    echo implode("\n", $lines);
}
