<?php

namespace FormTools\Modules\DataVisualization;

use FormTools\Core;
use FormTools\Menus;
use FormTools\Modules;
use PDO, Exception;

class Visualizations
{

    public static function addVisualization($info, $L)
    {
        $db = Core::$db;

        if ($info["data_source"] == "single_field") {
            $field_ids = $info["single_field_id"];
        } else {
            $field_ids = implode(",", $info["field_ids"]);
        }

        try {
            $db->query("
                INSERT INTO {PREFIX}module_data_visualizations (vis_name, vis_type, form_id, view_id, data_source, field_ids)
                VALUES (:vis_name, :vis_type, :form_id, :view_id, :data_source, :field_ids)
            ");
            $db->bindAll(array(
                "vis_name" => $info["vis_name"],
                "vis_type" => "pie_chart",
                "form_id" => $info["form_id"],
                "view_id" => $info["view_id"],
                "data_source" => $info["data_source"],
                "field_ids" => $field_ids
            ));
            $db->execute();

        } catch (Exception $e) {
            return array(true, $L["notify_visualization_not_added"]);
        }

        return array(true, $L["notify_visualization_added"], $db->getInsertId());
    }


    /**
     * Retrieves all information about a visualization.
     *
     * @param integer $vis_id
     */
    public static function getVisualization($vis_id, $L)
    {
        $db = Core::$db;

        $db->query("
            SELECT *
            FROM {PREFIX}module_data_visualizations
            WHERE vis_id = :vis_id
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        $vis_info = $db->fetch();

        $db->query("
            SELECT account_id
            FROM {PREFIX}module_data_visualization_clients
            WHERE vis_id = :vis_id
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        $vis_info["client_ids"] = $db->fetchAll(PDO::FETCH_COLUMN);

        return $vis_info;
    }


    /**
     * Retrieves all information needed to display a visualization, regardless of type. All you need to do is pass
     * the $vis_id.
     *
     * @param integer $vis_id
     * @return array
     */
    public static function getVisualizationForDisplay($vis_id)
    {
        $db = Core::$db;

        $db->query("
            SELECT *
            FROM {PREFIX}module_data_visualizations
            WHERE vis_id = :vis_id
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        $vis_info = $db->fetch();
        $form_id = $vis_info["form_id"];
        $view_id = $vis_info["view_id"];
        $cache_update_frequency = $vis_info["cache_update_frequency"];

        $return_info = array();

        switch ($vis_info["vis_type"]) {
            case "activity":
                $date_range = $vis_info["date_range"];
                $submission_count_group = $vis_info["submission_count_group"];

                // this returns a hash with "period" and "data" keys
                $return_info = ActivityCharts::getCachedActivityInfo($vis_id, $cache_update_frequency, $form_id, $view_id,
                    $date_range, $submission_count_group);
                $return_info["vis_type"] = "activity";
                $return_info["chart_type"] = $vis_info["chart_type"];
                $return_info["vis_id"] = $vis_id;
                $return_info["vis_name"] = $vis_info["vis_name"];
                $return_info["vis_colour"] = $vis_info["colour"];
                $return_info["line_width"] = $vis_info["line_width"];
				$return_info["cache_update_frequency"] = $cache_update_frequency;
                break;

            case "field":
                $field_id = $vis_info["field_id"];
                $ignore_empty_fields = $vis_info["field_chart_ignore_empty_fields"];
                $date_range = $vis_info["date_range"];

                $return_info = FieldCharts::getCachedFieldInfo($vis_id, $cache_update_frequency, $form_id, $view_id, $field_id,
                    $date_range, $ignore_empty_fields);
                $return_info["vis_type"] = "field";
                $return_info["chart_type"] = $vis_info["chart_type"];
                $return_info["vis_id"] = $vis_id;
                $return_info["vis_name"] = $vis_info["vis_name"];
                $return_info["vis_colour"] = $vis_info["colour"];
                $return_info["include_legend_quicklinks"] = $vis_info["include_legend_quicklinks"];
                $return_info["include_legend_full_size"] = $vis_info["include_legend_full_size"];
                $return_info["pie_chart_format"] = $vis_info["pie_chart_format"];
                $return_info["ignore_empty_fields"] = $vis_info["field_chart_ignore_empty_fields"];
				$return_info["cache_update_frequency"] = $cache_update_frequency;
                break;
        }

        return $return_info;
    }


    /**
     * Called in the main visualizations page, this allows the user to search all the visualizations they've created.
     * It also ties into the Quicklinks Dialog, letting the "Edit Visualizations" button link directly to the appropriate
     * subset of visualizations for the form View.
     *
     * For simplicity and speed, ALL results are returned and paginated on the client. This actually makes the following
     * code a little easier, since it requires additional logic performed on the SQL results to figure out if they're
     * appropriate for the search.
     *
     * @param array $search_criteria
     */
    public static function searchVisualizations($search_criteria)
    {
        $db = Core::$db;

        $where_clauses = array();
        if (isset($search_criteria["keyword"]) && !empty($search_criteria["keyword"])) {
            $keyword = $search_criteria["keyword"];
            $where_clauses[] = "vis_name LIKE '%$keyword%'";
        }
        if (isset($search_criteria["form_id"]) && !empty($search_criteria["form_id"])) {
            $where_clauses[] = "form_id = {$search_criteria["form_id"]}";
        }

        if (isset($search_criteria["vis_types"]) && !empty($search_criteria["vis_types"])) {
            $vis_type_clauses = array();
            foreach ($search_criteria["vis_types"] as $vis_type) {
                $vis_type_clauses[] = "vis_type = '$vis_type'";
            }
            $where_clauses[] = "(" . implode(" OR ", $vis_type_clauses) . ")";
        }
        if (isset($search_criteria["chart_type"]) && !empty($search_criteria["chart_type"])) {
            $where_clauses[] = "chart_type = '{$search_criteria["chart_type"]}'";
        }

        $where_clause = (empty($where_clauses)) ? "" : "WHERE " . implode(" AND ", $where_clauses);

        $db->query("
            SELECT *
            FROM   {PREFIX}module_data_visualizations
            $where_clause
            ORDER BY vis_id
        ");
        $db->execute();

        $account_type = $search_criteria["account_type"];
        $view_id = isset($search_criteria["view_id"]) ? $search_criteria["view_id"] : "";
        $client_id = isset($search_criteria["client_id"]) ? $search_criteria["client_id"] : "";

        $infohash = array();
        foreach ($db->fetchAll() as $row) {
            // filter out those visualizations that don't fit in the search
            if (!empty($view_id)) {
                $access_view_mapping = $row["access_view_mapping"];
                $view_ids = explode(",", $row["access_views"]);

                if ($access_view_mapping == "except") {
                    if (in_array($view_id, $view_ids)) {
                        continue;
                    }
                } else {
                    if ($access_view_mapping == "only") {
                        if (!in_array($view_id, $view_ids)) {
                            continue;
                        }
                    }
                }
            }

            // if this is a client account, check the permissions for admin/private forms to confirm the visualization
            // can be viewed
            if ($account_type == "client") {
                if ($row["access_type"] == "admin") {
                    continue;
                } else {
                    if ($row["access_type"] == "private") {
                        $client_ids = self::getVisualizationClients($row["vis_id"]);
                        if (!in_array($client_id, $client_ids)) {
                            continue;
                        }
                    }
                }
            }

            $infohash[] = $row;
        }

        return $infohash;
    }


    /**
     * Clears the cache for an individual Visualization, or ALL visualizations.
     *
     * @param integer $vis_id
     */
    public static function clearVisualizationCache($vis_id = "")
    {
        $db = Core::$db;

        $where_clause = "";
        if (!empty($vis_id)) {
            $where_clause = "WHERE vis_id = $vis_id";
        }

        $db->query("DELETE FROM {PREFIX}module_data_visualization_cache $where_clause");
        $db->execute();

        return array(true, "");
    }


    public static function getNumVisualizations()
    {
        $db = Core::$db;

        $db->query("
            SELECT count(*)
            FROM {PREFIX}module_data_visualizations
        ");
        $db->execute();

        return $db->fetch(PDO::FETCH_COLUMN);
    }


    public static function deleteVisualization($vis_id, $L)
    {
        $db = Core::$db;

        if (empty($vis_id) || !is_numeric($vis_id)) {
            return array();
        }

        $db->query("
            DELETE FROM {PREFIX}module_data_visualizations
            WHERE vis_id = $vis_id
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        if ($db->numRows() > 0) {
            $db->query("
                DELETE FROM {PREFIX}module_data_visualization_clients
                WHERE vis_id = :vis_id
            ");
            $db->bind("vis_id", $vis_id);
            $db->execute();

            $db->query("
                DELETE FROM {PREFIX}module_data_visualization_cache
                WHERE vis_id = :vis_id
            ");
            $db->bind("vis_id", $vis_id);
            $db->execute();

            return array(true, $L["notify_vis_deleted"]);
        } else {
            return array(false, $L["notify_vis_not_deleted"]);
        }
    }


    /**
     * Returns all clients that have been explicitly assigned to access the visualization. This is for visualizations
     * set with "private" access type only.
     *
     * @param $vis_id
     */
    public static function getVisualizationClients($vis_id)
    {
        $db = Core::$db;

        $db->query("
            SELECT account_id
            FROM   {PREFIX}module_data_visualization_clients
            WHERE  vis_id = :vis_id
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        return $db->fetchAll(PDO::FETCH_COLUMN);
    }


    public static function displayVisualization($vis_id, $width, $height, $overridden_settings = array())
    {
        global $g_cache;

        if (!isset($g_cache["data_visualization_{$vis_id}_count"])) {
            $g_cache["data_visualization_{$vis_id}_count"] = 1;
        } else {
            $g_cache["data_visualization_{$vis_id}_count"]++;
        }

        $id_suffix = $g_cache["data_visualization_{$vis_id}_count"];

        $vis_info = self::getVisualizationForDisplay($vis_id);
        $vis_type = $vis_info["vis_type"];
        $chart_type = $vis_info["chart_type"];

        $title = $vis_info["vis_name"];
        if (isset($overridden_settings["title"])) {
            $title = $overridden_settings["title"];
        }

        $title = addcslashes($title, "'");
        $num_rows = count($vis_info["data"]);

        $js_lines = array();
        for ($i = 0; $i<$num_rows; $i++) {
            if ($vis_type === "field") {
                $label = addslashes($vis_info["data"][$i]["field_value"]);
                $data = $vis_info["data"][$i]["count"];
            } else {
                $label = addslashes($vis_info["data"][$i]["label"]);
                $data = $vis_info["data"][$i]["data"];
            }
            $js_lines[] = "data.setValue($i, 0, \"$label\");";
            $js_lines[] = "data.setValue($i, 1, $data);";
        }

        switch ($chart_type) {
            // line and area charts are specific to Activity Chart visualizations
            case "area_chart":
            case "line_chart":
                $chart_class = ($chart_type == "area_chart") ? "AreaChart" : "LineChart";
                $colour = $vis_info["vis_colour"];
                if (isset($overridden_settings["colour"])) {
                    $colour = $overridden_settings["colour"];
                }
                $line_width = $vis_info["line_width"];
                if (isset($overridden_settings["line_width"]) && is_numeric($overridden_settings["line_width"])) {
                    $line_width = $overridden_settings["line_width"];
                }

                $js_lines[] = <<< END
var chart = new google.visualization.$chart_class(document.getElementById("dv_vis_{$vis_id}_{$id_suffix}"));
var settings = {
  width: $width,
  height: $height,
  colors: ["$colour"],
  lineWidth: {$line_width},
  title: '$title',
  legend: 'none'
}
END;
                break;

            // Activity AND Field Charts
            case "column_chart":
                if ($vis_type == "activity") {
                    $colour = $vis_info["vis_colour"];
                    if (isset($overridden_settings["colour"])) {
                        $colour = $overridden_settings["colour"];
                    }
                    $line_width = $vis_info["line_width"];
                    if (isset($overridden_settings["line_width"]) && is_numeric($overridden_settings["line_width"])) {
                        $line_width = $overridden_settings["line_width"];
                    }

                    $js_lines[] = <<< END
var chart = new google.visualization.ColumnChart(document.getElementById("dv_vis_{$vis_id}_{$id_suffix}"));
var settings = {
  width:  $width,
  height: $height,
  colors: ["$colour"],
  lineWidth: {$line_width},
  title: '$title',
  legend: 'none'
}
END;
                }
                if ($vis_type == "field") {
                    $colour = $vis_info["vis_colour"];
                    if (isset($overridden_settings["colour"])) {
                        $colour = $overridden_settings["colour"];
                    }
                    $js_lines[] = <<< END
var chart = new google.visualization.ColumnChart(document.getElementById("dv_vis_{$vis_id}_{$id_suffix}"));
var settings = {
  width:  $width,
  height: $height,
  colors: ["$colour"],
  title: '$title',
  legend: 'none'
}
END;
                }
                break;

            case "bar_chart":
                $colour = $vis_info["vis_colour"];
                if (isset($overridden_settings["colour"])) {
                    $colour = $overridden_settings["colour"];
                }

                $js_lines[] = <<< END
var chart = new google.visualization.BarChart(document.getElementById("dv_vis_{$vis_id}_{$id_suffix}"));
var settings = {
  width:  $width,
  height: $height,
  colors: ["$colour"],
  title:  '$title',
  legend: 'none'
}
END;
                break;

            case "pie_chart":
                $pie_chart_format = $vis_info["pie_chart_format"];
                if (isset($overridden_settings["pie_chart_format"])) {
                    $pie_chart_format = $overridden_settings["pie_chart_format"];
                }

                $is_3D = ($pie_chart_format == "3D") ? "true" : "false";

                $include_legend = $vis_info["include_legend_full_size"];
                if (isset($overridden_settings["include_legend"])) {
                    $include_legend = $overridden_settings["include_legend"];
                }

                $legend = ($include_legend == "yes") ? "right" : "none";

                $js_lines[] = <<< END
var chart = new google.visualization.PieChart(document.getElementById("dv_vis_{$vis_id}_{$id_suffix}"));
var settings = {
  width:   $width,
  height:  $height,
  is3D:    $is_3D,
  legend: '$legend',
  title:  '$title'
}
END;
                break;
        }

        $js_lines_str = implode("\n", $js_lines);

        echo <<< END
<script src="https://www.gstatic.com/charts/loader.js"></script>
<div id="dv_vis_{$vis_id}_{$id_suffix}"></div>
<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(vis_drawChart);
function vis_drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn("string", "");
    data.addColumn("number", "Submissions");
    data.addRows($num_rows);
    $js_lines_str
    chart.draw(data, settings);
}
</script>
END;
    }


	/**
	 * Helper function to return the previous and next Visualization IDs for the Edit Visualization pages.
	 * @param $vis_id
	 * @return array
	 */
    public static function getTabsetLinks($vis_id)
    {
        $keyword = Modules::loadModuleField("data_visualization", "keyword", "dv_search_keyword", "");
        $search_form_id = Modules::loadModuleField("data_visualization", "dv_search_form_id", "dv_form_id", "");
        $search_view_id = Modules::loadModuleField("data_visualization", "dv_search_view_id", "dv_view_id", "");
        $vis_types = Modules::loadModuleField("data_visualization", "vis_types", "dv_vis_types", array("activity", "field"));
        $chart_type = Modules::loadModuleField("data_visualization", "dv_search_chart_type", "dv_chart_type", "");
        $account_type = Modules::loadModuleField("data_visualization", "account_type", "dv_account_type", "admin");
        $client_id = Modules::loadModuleField("data_visualization", "client_id", "dv_client_id", "");

        $search_criteria = array(
            "keyword" => $keyword,
            "form_id" => $search_form_id,
            "view_id" => $search_view_id,
            "vis_types" => $vis_types,
            "chart_type" => $chart_type,
            "account_type" => $account_type,
            "client_id" => $client_id
        );

        $results = self::searchVisualizations($search_criteria);

        $return_info = array(
            "prev_link" => "",
            "next_link" => ""
        );

        // it's possible that there are NO visualization IDs: the user may have done a search that returns no results
        // but be editing a visualization on a different tab
        if (count($results) === 0) {
            return $return_info;
        }

        $ordered_vis_ids = array();
        $vis_id_to_types = array();
        foreach ($results as $vis_info) {
            $ordered_vis_ids[] = $vis_info["vis_id"];
            $vis_id_to_types[$vis_info["vis_id"]] = $vis_info["vis_type"];
        }
        $current_index = array_search($vis_id, $ordered_vis_ids);

        if ($current_index === false) {
            return $return_info;
        }

        $num_results = count($ordered_vis_ids);
        if ($current_index === 0) {
            if ($num_results > 1) {
                $next_vis_id = $ordered_vis_ids[$current_index + 1];
                if ($vis_id_to_types[$next_vis_id] == "activity") {
                    $return_info["next_link"] = "../activity_charts/edit.php?vis_id=$next_vis_id";
                } else {
                    $return_info["next_link"] = "../field_charts/edit.php?vis_id=$next_vis_id";
                }
            }
        } else {
            if ($current_index === $num_results - 1) {
                if ($num_results > 1) {
                    $prev_vis_id = $ordered_vis_ids[$current_index - 1];
                    if ($vis_id_to_types[$prev_vis_id] == "activity") {
                        $return_info["prev_link"] = "../activity_charts/edit.php?vis_id=$prev_vis_id";
                    } else {
                        $return_info["prev_link"] = "../field_charts/edit.php?vis_id=$prev_vis_id";
                    }
                }
            } else {
                $prev_vis_id = $ordered_vis_ids[$current_index - 1];
                if ($vis_id_to_types[$prev_vis_id] == "activity") {
                    $return_info["prev_link"] = "../activity_charts/edit.php?vis_id=$prev_vis_id";
                } else {
                    $return_info["prev_link"] = "../field_charts/edit.php?vis_id=$prev_vis_id";
                }

                $next_vis_id = $ordered_vis_ids[$current_index + 1];
                if ($vis_id_to_types[$next_vis_id] == "activity") {
                    $return_info["next_link"] = "../activity_charts/edit.php?vis_id=$next_vis_id";
                } else {
                    $return_info["next_link"] = "../field_charts/edit.php?vis_id=$next_vis_id";
                }
            }
        }

        return $return_info;
    }


    /**
     * Called on the Advanced tab of the Edit Field Chart and Edit Activity Chart pages. It creates a new
     * Page in the Pages module and assigns that page to a menu.
     *
     * @param array $request
     */
    public static function createPageAndMenuItem($request)
    {
        $db = Core::$db;

        $vis_id = $request["vis_id"];
        $page_title = $request["page_title"];
        $menu_id = $request["menu_id"];
        $menu_position = $request["menu_position"];
        $is_submenu = $request["is_submenu"];

        $pages_module = Modules::getModuleInstance("pages");

        $content =<<< END
<div style="border:1px solid #cccccc">
    {template_hook location="data_visualization" vis_id=$vis_id height=400 width=738}
</div>
END;

        // convert the info to a Pages-module-friendly format
        $info = array(
            "page_name" => $page_title,
            "heading" => $page_title,
            "access_type" => "public",
            "content_type" => "smarty",
            "codemirror_content" => $content,
            "use_wysiwyg_hidden" => "no"
        );
        list ($success, $message, $page_id) = $pages_module->addPage($info);

        $menu_info = Menus::getMenu($menu_id);
        $menu_type = $menu_info["menu_type"];

        // now add the new Page to the menu. If it's being added to the administrator's menu, update the cached menu
        if ($menu_position == "at_start") {
            $db->query("
                UPDATE {PREFIX}menu_items
                SET    list_order = list_order + 1
                WHERE  menu_id = :menu_id
            ");
            $db->bind("menu_id", $menu_id);
            $db->execute();

            $list_order = 1;
        } else {
            if ($menu_position == "at_end") {
                $list_order = count($menu_info["menu_items"]) + 1;
            } else {
                $db->query("
                    UPDATE {PREFIX}menu_items
                    SET    list_order = list_order + 1
                    WHERE  menu_id = :menu_id AND
                           list_order > :list_order
                ");
                $db->bindAll(array(
                    "menu_id" => $menu_id,
                    "list_order" => $menu_position
                ));
                $db->execute();

                $list_order = $menu_position + 1;
            }
        }

        $db->query("
            INSERT INTO {PREFIX}menu_items (menu_id, display_text, page_identifier, url, is_submenu, is_new_sort_group, list_order)
            VALUES (:menu_id, :display_text, :page_identifier, :url, :is_submenu, :is_new_sort_group, :list_order)
        ");
        $db->bindAll(array(
            "menu_id" => $menu_id,
            "display_text" => $page_title,
            "page_identifier" => "page_{$page_id}",
            "url" => "/modules/pages/page.php?id=$page_id",
            "is_submenu" => $is_submenu,
            "is_new_sort_group" => "yes",
            "list_order" => $list_order
        ));
        $db->execute();

        if ($menu_type == "admin") {
            Menus::cacheAccountMenu(Core::$user->getAccountId());
        }

        return array(
            "success" => 1,
            "menu_type" => $menu_type,
            "page_id" => $page_id
        );
    }

}
