<?php

require_once("../../../../global/library.php");
ft_init_module_page("client");

// the action to take and the ID of the page where it will be displayed (allows for
// multiple calls on same page to load content in unique areas)
$request = array_merge($_POST, $_GET);
$action  = $request["action"];

// Find out if we need to return anything back with the response. This mechanism allows us to pass any information
// between the Ajax submit function and the Ajax return function. Usage:
//   "return_vals[]=question1:answer1&return_vals[]=question2:answer2&..."
$return_val_str = "";
if (isset($request["return_vals"]))
{
  $vals = array();
  foreach ($request["return_vals"] as $pair)
  {
    list($key, $value) = split(":", $pair);
    $vals[] = "$key: \"$value\"";
  }
  $return_val_str = ", " . join(", ", $vals);
}


switch ($action)
{
  case "get_form_fields":
    $form_id = $request["form_id"];
    $form_fields = ft_get_form_fields($form_id);

    $js_info = array();
    foreach ($form_fields as $field_info)
      $js_info[] = "[{$field_info["field_id"]}, \"" . htmlspecialchars($field_info["field_title"], ENT_QUOTES) . "\"]";

    $js_array = "[" . join(", ", $js_info) . "]";

    echo "{ \"success\": true, \"form_id\": $form_id, \"fields\": " . $js_array . " }";
    break;

  case "get_view_fields":
    $view_id = $request["view_id"];
    $view_fields = ft_get_view_fields($view_id);

    $js_info = array();
    foreach ($view_fields as $field_info)
      $js_info[] = "[{$field_info["field_id"]}, \"" . htmlspecialchars($field_info["field_title"], ENT_QUOTES) . "\"]";

    $js_array = "[" . join(", ", $js_info) . "]";

    echo "{ \"success\": true, \"view_id\": $view_id, \"fields\": " . $js_array . " }";
    break;

  case "get_visualization":
    $vis_id = $request["vis_id"];
    $vis_data = dv_get_visualization_for_display($vis_id);
    echo ft_convert_to_json($vis_data);
    break;

  case "get_activity_chart_data":
    $form_id    = $request["form_id"];
    $view_id    = isset($request["view_id"]) ? $request["view_id"] : "";
    $date_range = $request["date_range"];
    $submission_count_group = $request["submission_count_group"];
    $activity_info = dv_get_activity_info($form_id, $view_id, $date_range, $submission_count_group);
    echo ft_convert_to_json($activity_info);
    break;

  case "get_field_chart_data":
    $form_id  = $request["form_id"];
    $view_id  = $request["view_id"];
    $field_id = $request["field_id"];
    $ignore_empty_fields = $request["ignore_empty_fields"];
    $data = dv_get_field_chart_info($form_id, $view_id, $field_id, $ignore_empty_fields);
    echo ft_convert_to_json($data);
    break;

   // permissions. Small security hole, but it IS one
  case "get_menu":
    $menu_id = $request["menu_id"];
    $menu_info = ft_get_menu($menu_id);

    $html = "<select id=\"menu_position\">"
          . "<option value=\"at_start\">At Start</option>"
          . "<option value=\"at_end\">At End</option>"
          . "<optgroup label=\"After\">";

    foreach ($menu_info["menu_items"] as $menu_item)
    {
      $prefix = ($menu_item["is_submenu"] == "yes") ? "&#8212; " : "";
      $html .= "<option value=\"{$menu_item["list_order"]}\">{$prefix}{$menu_item["display_text"]}</option>";
    }
    $html .= "</optgroup>"
          . "</select>";

    echo $html;
    break;

  case "create_page_and_menu_item":
    $result = dv_create_page_and_menu_item($request);
    echo ft_convert_to_json($result);
    break;

  case "clear_visualization_cache":
    $vis_id = $request["vis_id"];
    dv_clear_visualization_cache($vis_id);
    $vis_data = dv_get_visualization_for_display($vis_id);
    echo ft_convert_to_json($vis_data);
    break;
}

