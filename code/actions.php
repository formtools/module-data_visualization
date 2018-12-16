<?php

require_once("../../../global/library.php");

use FormTools\Fields;
use FormTools\Menus;
use FormTools\Modules;
use FormTools\ViewFields;
use FormTools\Modules\DataVisualization\FieldCharts;
use FormTools\Modules\DataVisualization\ActivityCharts;
use FormTools\Modules\DataVisualization\Visualizations;

$module = Modules::initModulePage("client");

// the action to take and the ID of the page where it will be displayed (allows for
// multiple calls on same page to load content in unique areas)
$action = $request["action"];

switch ($action) {
    case "get_form_fields":
        $form_id = $request["form_id"];
        $form_fields = Fields::getFormFields($form_id);
        $js_info = array();
        foreach ($form_fields as $field_info) {
            $js_info[] = array($field_info["field_id"], htmlspecialchars($field_info["field_title"], ENT_QUOTES));
        }
        send_json(array(
            "success" => true,
            "form_id" => $form_id,
            "fields" => $js_info
        ));
        break;

    case "get_view_fields":
        $view_id = $request["view_id"];
        $view_fields = ViewFields::getViewFields($view_id);
        $js_info = array();
        foreach ($view_fields as $field_info) {
            $js_info[] = array($field_info["field_id"], htmlspecialchars($field_info["field_title"], ENT_QUOTES));
        }
        echo json_encode(array(
            "success" => true,
            "view_id" => $view_id,
            "fields" => $js_info
        ));
        break;

    case "get_visualization":
        $vis_id = $request["vis_id"];
        $vis_data = Visualizations::getVisualizationForDisplay($vis_id);
        send_json($vis_data);
        break;

    case "get_activity_chart_data":
        $form_id    = $request["form_id"];
        $view_id    = isset($request["view_id"]) ? $request["view_id"] : "";
        $date_range = $request["date_range"];
        $submission_count_group = $request["submission_count_group"];
        $activity_info = ActivityCharts::getActivityInfo($form_id, $view_id, $date_range, $submission_count_group);
        send_json($activity_info);
        break;

    case "get_field_chart_data":
        $form_id  = $request["form_id"];
        $view_id  = $request["view_id"];
        $field_id = $request["field_id"];
		$date_range = $request["date_range"];
        $ignore_empty_fields = $request["ignore_empty_fields"];
        $data = FieldCharts::getFieldChartInfo($form_id, $view_id, $field_id, $date_range, $ignore_empty_fields);
        send_json($data);
        break;

    case "get_menu":
        $menu_id = $request["menu_id"];
        $menu_info = Menus::getMenu($menu_id);

        $html = "<select id=\"menu_position\">"
              . "<option value=\"at_start\">At Start</option>"
              . "<option value=\"at_end\">At End</option>"
              . "<optgroup label=\"After\">";

        foreach ($menu_info["menu_items"] as $menu_item) {
            $prefix = ($menu_item["is_submenu"] == "yes") ? "&#8212; " : "";
            $html .= "<option value=\"{$menu_item["list_order"]}\">{$prefix}{$menu_item["display_text"]}</option>";
        }

        $html .= "</optgroup></select>";
        echo $html;
        break;

    case "create_page_and_menu_item":
        $result = Visualizations::createPageAndMenuItem($request);
        send_json($result);
        break;

    case "clear_visualization_cache":
        $vis_id = $request["vis_id"];
        Visualizations::clearVisualizationCache($vis_id);
        $vis_data = Visualizations::getVisualizationForDisplay($vis_id);
        send_json($vis_data);
        break;
}


function send_json($response)
{
    header("Content-Type: application/json");
    echo json_encode($response);
}
