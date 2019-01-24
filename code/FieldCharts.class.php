<?php


namespace FormTools\Modules\DataVisualization;

use FormTools\Core;
use FormTools\Fields;
use FormTools\FieldTypes;
use FormTools\General as CoreGeneral;
use FormTools\OptionLists;
use FormTools\Modules;
use FormTools\ViewFilters;
use Exception;


class FieldCharts
{

	/**
	 * Adds a new field chart.
	 * @param $info
	 * @param $L
	 * @return array
	 */
    public static function addFieldChart($info, $L)
    {
        $db = Core::$db;

        $module_settings = Modules::getModuleSettings("", "data_visualization");

        $pie_chart_format = "";
        $include_legend_quicklinks = "";
        $include_legend_full_size = "";
        if ($info["chart_type"] == "pie_chart") {
            $pie_chart_format = $info["pie_chart_format"];
            $include_legend_quicklinks = $info["include_legend_quicklinks"];
            $include_legend_full_size = $info["include_legend_full_size"];
            $colour = $info["colour_old"];
        } else {
            $colour = $info["colour"];
        }

        try {
            $db->query("
                INSERT INTO {PREFIX}module_data_visualizations (vis_name, vis_type, chart_type,
                    form_id, view_id, field_id, cache_update_frequency, field_chart_ignore_empty_fields, pie_chart_format, colour,
                    include_legend_quicklinks, include_legend_full_size)
                VALUE (:vis_name, :vis_type, :chart_type, :form_id, :view_id, :field_id,
                    :cache_update_frequency, :field_chart_ignore_empty_fields, :pie_chart_format, :colour,
                    :include_legend_quicklinks, :include_legend_full_size)
            ");
            $db->bindAll(array(
                "vis_name" => $info["vis_name"],
                "vis_type" => "field",
                "chart_type" => $info["chart_type"],
                "form_id" => $info["form_id"],
                "view_id" => $info["view_id"],
                "field_id" => $info["field_id"],
                "cache_update_frequency" => $module_settings["default_cache_frequency"],
                "field_chart_ignore_empty_fields" => $info["field_chart_ignore_empty_fields"],
                "pie_chart_format" => $pie_chart_format,
                "colour" => $colour,
                "include_legend_quicklinks" => $include_legend_quicklinks,
                "include_legend_full_size" => $include_legend_full_size
            ));
            $db->execute();
        } catch (Exception $e) {
            return array(false, $L["notify_error_creating_field_chart"], "");
        }

        return array(true, $L["notify_field_chart_created"], $db->getInsertId());
    }


    /**
     * This is a wrapper for dv_get_activity_info(). It checks the cache to see if there's a recent cache that
     * can be used instead of re-doing the SQL query.
	 * @param $vis_id
	 * @param $cache_update_frequency
	 * @param $form_id
	 * @param $view_id
	 * @param $field_id
	 * @param $date_range
	 * @param $ignore_empty_fields
	 * @return array
	 */
    public static function getCachedFieldInfo($vis_id, $cache_update_frequency, $form_id, $view_id, $field_id, $date_range,
		$ignore_empty_fields)
    {
        $db = Core::$db;

        // if the user has request NO cache for this Activity Chart, always do a fresh query
        if ($cache_update_frequency == "no_cache") {
            $return_info = self::getFieldChartInfo($form_id, $view_id, $field_id, $date_range, $ignore_empty_fields);
            $return_info["cache_update_frequency"] = $cache_update_frequency;
            return $return_info;
        }

        // otherwise, check to see if there's the cached data within the cache frequency period specified
        $db->query("
            SELECT *
            FROM   {PREFIX}module_data_visualization_cache
            WHERE  vis_id = :vis_id AND
                   last_cached >= DATE_SUB(NOW(), INTERVAL $cache_update_frequency MINUTE)
            LIMIT 1
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        // great! used the cached value
        if ($db->numRows() == 1) {
            $result = $db->fetch();
            return array(
                "form_id" => $form_id,
                "view_id" => $view_id,
                "field_id" => $field_id,
                "data" => unserialize($result["data"]),
                "last_cached" => $result["last_cached"],
                "cache_update_frequency" => $cache_update_frequency
            );
        }

        // Here, there's nothing valid in the cache. Run the query and cache the data.
        $return_info = self::getFieldChartInfo($form_id, $view_id, $field_id, $date_range, $ignore_empty_fields);
        $return_info["cache_update_frequency"] = $cache_update_frequency;

        General::updateVisualizationCache($vis_id, $return_info["data"]);

        return $return_info;
    }


    public static function getFieldChartInfo($form_id, $view_id, $field_id, $date_range, $ignore_empty_fields)
    {
        $db = Core::$db;

        $result = Fields::getFieldColByFieldId($form_id, $field_id);
        $col_name = $result[$field_id];

		$date_range_clause = "";
		switch ($date_range) {
			case "year_to_date":
				$date_range_clause = "AND YEAR(submission_date) = YEAR(CURDATE())";
				break;
			case "month_to_date":
				$date_range_clause = "AND YEAR(submission_date) = YEAR(CURDATE()) AND MONTH(submission_date) = MONTH(CURDATE())";
				break;
			default:
				if (array_key_exists($date_range, General::$intervalMap)) {
					$value = General::$intervalMap[$date_range];
					$date_range_clause = "AND submission_date >= DATE_SUB(NOW(), INTERVAL {$value} DAY)";
				}
				break;
		}

        $filter_where_clause = "";
        if (!empty($view_id)) {
            $filter_sql_clauses = ViewFilters::getViewFilterSql($view_id);
            if (!empty($filter_sql_clauses)) {
                $filter_where_clause = " AND (" . implode(" AND ", $filter_sql_clauses) . ")";
            }
        }

        if ($ignore_empty_fields == "yes") {
            $db->query("
                SELECT $col_name as field_value, count(*) as count
                FROM   {PREFIX}form_{$form_id}
                WHERE  is_finalized = 'yes' 
                	   AND $col_name IS NOT NULL AND TRIM($col_name) != ''
                       $filter_where_clause
                       $date_range_clause
                GROUP BY field_value
            ");
        } else {
            $db->query("
                SELECT
                  CASE
                    WHEN $col_name IS NULL OR $col_name = ''
                    THEN NULL
                    ELSE $col_name
                  END as field_value, count(*) as count
                  FROM {PREFIX}form_{$form_id}
                  WHERE is_finalized = 'yes'
                        $filter_where_clause
                        $date_range_clause
                  GROUP BY field_value
            ");
        }

        $db->execute();
        $results = $db->fetchAll();

        // if this field is assigned to an Option List, we sort the results by the order in which the options
        // were specified and pass along the labels, not actual stored values. Otherwise we just return the alphabetical
        // results
        $form_field = Fields::getFormField($field_id, array("include_field_settings" => true));
        $field_type_info = FieldTypes::getFieldType($form_field["field_type_id"]);

        // radios, checkboxes, select + multi-select are handled separately: we need to look at the option list & get the
        // display values
        $raw_field_type_map_multi_select_id = $field_type_info["raw_field_type_map_multi_select_id"];
        if (!empty($raw_field_type_map_multi_select_id) && isset($form_field["settings"][$raw_field_type_map_multi_select_id])) {
            $results = self::parseMultiSelectData($form_field["settings"][$raw_field_type_map_multi_select_id], $results);
        }

        $now = CoreGeneral::getCurrentDatetime();

        return array(
            "form_id" => $form_id,
            "view_id" => $view_id,
            "field_id" => $field_id,
            "date_range" => $date_range,
            "data" => $results,
            "last_cached" => $now
        );
    }


	/**
	 * Updates the appropriate tab of the field chart.
	 * @param $vis_id
	 * @param $tab
	 * @param $info
	 * @param $L
	 * @return array
	 */
    public static function updateFieldChart($vis_id, $tab, $info, $L)
    {
        $success = true;
        if ($tab == "main") {
            list ($success, $message) = self::updateFieldChartMainTab($vis_id, $info);
        } else if ($tab == "appearance") {
            list ($success, $message) = self::updateFieldChartAppearanceTab($vis_id, $info);
        } else if ($tab == "permissions") {
            list ($success, $message) = self::updateFieldChartPermissionsTab($vis_id, $info);
        }

        Visualizations::clearVisualizationCache($vis_id);

        if ($success) {
            return array(true, $L["notify_field_chart_updated"]);
        } else {
            return array(false, $L["notify_error_updating_field_chart"], "");
        }
    }


    private static function updateFieldChartMainTab($vis_id, $info)
    {
        $db = Core::$db;

        try {
            $db->query("
                UPDATE {PREFIX}module_data_visualizations
                SET    vis_name = :vis_name,
                       form_id = :form_id,
                       view_id = :view_id,
                       field_id = :field_id,
                       cache_update_frequency = :cache_update_frequency
                WHERE  vis_id = :vis_id
            ");
            $db->bindAll(array(
               "vis_name" => $info["vis_name"],
                "form_id" => $info["form_id"],
                "view_id" => $info["view_id"],
                "field_id" => $info["field_id"],
                "cache_update_frequency" => $info["cache_update_frequency"],
                "vis_id" => $vis_id
            ));
            $db->execute();
        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }

        return array(true, "");
    }


    private static function updateFieldChartAppearanceTab($vis_id, $info)
    {
        $db = Core::$db;

        try {
            $pie_chart_format = "";
            $include_legend_quicklinks = "";
            $include_legend_full_size = "";
            if ($info["chart_type"] == "pie_chart") {
                $pie_chart_format = $info["pie_chart_format"];
                $include_legend_quicklinks = $info["include_legend_quicklinks"];
                $include_legend_full_size = $info["include_legend_full_size"];
                $colour = $info["colour_old"];
            } else {
                $colour = $info["colour"];
            }

            $db->query("
                UPDATE {PREFIX}module_data_visualizations
                SET    chart_type = :chart_type,
                	   date_range = :date_range,
                       field_chart_ignore_empty_fields = :field_chart_ignore_empty_fields,
                       pie_chart_format = :pie_chart_format,
                       colour = :colour,
                       include_legend_quicklinks = :include_legend_quicklinks,
                       include_legend_full_size = :include_legend_full_size
                WHERE  vis_id = :vis_id
            ");
            $db->bindAll(array(
                "chart_type" => $info["chart_type"],
                "date_range" => $info["date_range"],
                "field_chart_ignore_empty_fields" =>  $info["field_chart_ignore_empty_fields"],
                "pie_chart_format" => $pie_chart_format,
                "colour" => $colour,
                "include_legend_quicklinks" => $include_legend_quicklinks,
                "include_legend_full_size" => $include_legend_full_size,
                "vis_id" => $vis_id
            ));
            $db->execute();
        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }

        return array(true, "");
    }


    public static function updateFieldChartPermissionsTab($vis_id, $info)
    {
        $db = Core::$db;

        $selected_client_ids = (isset($info["selected_client_ids"])) ? $info["selected_client_ids"] : array();

        $db->query("DELETE FROM {PREFIX}module_data_visualization_clients WHERE vis_id = :vis_id");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        foreach ($selected_client_ids as $account_id) {
            $db->query("
                INSERT INTO {PREFIX}module_data_visualization_clients (vis_id, account_id)
                VALUES (:vis_id, :account_id)
            ");
            $db->bindAll(array(
                "vis_id" => $vis_id,
                "account_id" => $account_id
            ));
            $db->execute();
        }
        $access_type = $info["access_type"];
        $access_view_mapping = $info["access_view_mapping"];

        $access_views = "";
        if ($access_view_mapping != "all") {
            $view_ids = (isset($info["view_ids"])) ? $info["view_ids"] : array();
            $access_views = implode(",", $view_ids);
        }

        $db->query("
            UPDATE {PREFIX}module_data_visualizations
            SET    access_type = :access_type,
                   access_view_mapping = :access_view_mapping,
                   access_views = :access_views
            WHERE  vis_id = :vis_id
        ");
        $db->bindAll(array(
            "access_type" => $access_type,
            "access_view_mapping" => $access_view_mapping,
            "access_views" => $access_views,
            "vis_id" => $vis_id
        ));

        $db->execute();

        return array(true, "");
    }


    private static function parseMultiSelectData($option_list_id, $results)
    {
        if (!empty($option_list_id)) {
            $option_list = OptionLists::getOptionListOptions($option_list_id);

            $option_list_map = array();
            foreach ($option_list as $grouped) {
                if (!is_array($grouped["options"])) {
                    continue;
                }
                foreach ($grouped["options"] as $option) {
                    $option_list_map[$option["option_value"]] = $option["option_name"];
                }
            }

            $updated_results = array();
            foreach ($results as $result) {
                if (!array_key_exists($result["field_value"], $option_list_map)) {
                    continue;
                }

                $updated_results[] = array(
                    "field_value" => $option_list_map[$result["field_value"]],
                    "count" => $result["count"]
                );
            }

            $results = $updated_results;
        }

        return $results;
    }
}
