<?php


namespace FormTools\Modules\DataVisualization;

use FormTools\Core;
use FormTools\General as CoreGeneral;
use FormTools\Modules;
use FormTools\ViewFilters;
use PDO, Exception;


class ActivityCharts
{
    /**
     * Adds a new activity chart to the database.
     *
     * @param array $info everything in the POST request from the New Activity Chart page
     */
    public static function addActivityChart($info, $L)
    {
        $db = Core::$db;

        $module_settings = Modules::getModuleSettings("", "data_visualization");

        $view_id = isset($info["view_id"]) ? $info["view_id"] : null;

        // line width is only relevant for line and area charts
        $line_width = isset($info["line_width"]) ? $info["line_width"] : 2;

        try {
            $db->query("
                INSERT INTO {PREFIX}module_data_visualizations (vis_name, vis_type, chart_type,
                    form_id, view_id, cache_update_frequency, date_range, submission_count_group, colour, line_width)
                VALUE (:vis_name, :vis_type, :chart_type, :form_id, :view_id, :cache_update_frequency,
                    :date_range, :submission_count_group, :colour, :line_width)
            ");
            $db->bindAll(array(
                "vis_name" => $info["vis_name"],
                "vis_type" => "activity",
                "chart_type" => $info["chart_type"],
                "form_id" => $info["form_id"],
                "view_id" => $view_id,
                "cache_update_frequency" => $module_settings["default_cache_frequency"],
                "date_range" => $info["date_range"],
                "submission_count_group" => $info["submission_count_group"],
                "colour" => $info["colour"],
                "line_width" => $line_width
            ));
            $db->execute();
        } catch (Exception $e) {
            return array(false, $L["notify_error_creating_activity_chart"] . $e->getMessage(), "");
        }

        return array(true, $L["notify_activity_chart_created"], $db->getInsertId());
    }


    /**
     * Updates the activity chart information.
     *
     * @param integer $vis_id
     * @param array $info
     */
    public static function updateActivityChart($vis_id, $tab, $info, $L)
    {
        try {
            if ($tab == "main") {
                self::updateVisualizationMainTab($vis_id, $info);
            } else if ($tab == "appearance") {
                self::updateVisualizationAppearanceTab($vis_id, $info);
            } else if ($tab == "permissions") {
                self::updateVisualizationPermissionsTab($vis_id, $info);
            }

            // always clear the cache
            Visualizations::clearVisualizationCache($vis_id);
        } catch (Exception $e) {
            return array(false, $L["notify_error_updating_activity_chart"], "");
        }

        return array(true, $L["notify_activity_chart_updated"]);
    }


    /**
     * This is a wrapper for dv_get_activity_info(). It checks the cache to see if there's a recent cache that
     * can be used instead of re-doing the SQL query.
     *
     * @param integer $vis_id
     * @param string $cache_update_frequency an hour (integer), or "no_cache"
     * @param integer $form_id
     * @param integer $view_id
     * @param string $date_range
     * @param string $submission_count_group
     */
    public static function getCachedActivityInfo($vis_id, $cache_update_frequency, $form_id, $view_id, $date_range, $submission_count_group)
    {
        $db = Core::$db;

        // if the user has request NO cache for this Activity Chart, always do a fresh query
        if ($cache_update_frequency == "no_cache") {
            return self::getActivityInfo($form_id, $view_id, $date_range, $submission_count_group);
        }

        // otherwise, check to see if there's the cached data within the cache frequency period specified
        $now = CoreGeneral::getCurrentDatetime();
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
                "period"      => $submission_count_group,
                "last_cached" => $result["last_cached"],
                "data"        => unserialize($result["data"])
            );
        }

        // Here, there's nothing valid in the cache. Run the query and cache the data.
        $return_info = self::getActivityInfo($form_id, $view_id, $date_range, $submission_count_group);

        General::updateVisualizationCache($vis_id, $return_info["data"]);

        // also include the new cache date
        $return_info["last_cached"] = $now;

        return $return_info;
    }


    /**
     * This returns all results for a form View activity report. Note: it never checks the cache. For that, use
     * dv_get_cached_activity_info() - generally, that's the function you'll want to use. This function just lets
     * you bypass the cache to ensure you always get the most up to date results.
     *
     * @param integer $form_id
     * @param integer $view_id (optional)
     * @param string $date_range
     * @param string $submission_count_group "day" or "month" (week is VERY complicated - will add in later version)
     */
    public static function getActivityInfo($form_id, $view_id, $date_range, $submission_count_group)
    {
        $db = Core::$db;

        $filter_sql_clauses = array();
        if (!empty($view_id)) {
            $filter_sql_clauses = ViewFilters::getViewFilterSql($view_id);
        }

        $date_range_clause = "";
        switch ($date_range) {
            case "year_to_date":
                $date_range_clause = "YEAR(submission_date) = YEAR(CURDATE())";
                break;
            case "month_to_date":
                $date_range_clause = "YEAR(submission_date) = YEAR(CURDATE()) AND MONTH(submission_date) = MONTH(CURDATE())";
                break;
            default:
                if (array_key_exists($date_range, General::$intervalMap)) {
                    $value = General::$intervalMap[$date_range];
                    $date_range_clause = "submission_date >= DATE_SUB(NOW(), INTERVAL {$value} DAY)";
                }
                break;
        }

        $where_clauses = array("is_finalized = 'yes'");
        if (!empty($date_range_clause)) {
            $where_clauses[] = $date_range_clause;
        }
        if (!empty($filter_sql_clauses)) {
            $where_clauses[] = "(" . implode(" AND ", $filter_sql_clauses) . ")";
        }
        $where_clause = "WHERE " . implode(" AND ", $where_clauses);

        // do a quick test to confirm that there's at least a single result with the parameters specified
        $db->query("
            SELECT count(*)
            FROM {PREFIX}form_{$form_id}
            $where_clause
        ");
        $db->execute();
        $count = $db->fetch(PDO::FETCH_COLUMN);

        // shut it down!
        if ($count == 0) {
            return array(
                "period" => $submission_count_group,
                "data"   => array()
            );
        }

        list ($select_clause, $stats) = self::getActivityAccountSelectQueryInfo($submission_count_group, $date_range,
            $date_range_clause, $form_id, $filter_sql_clauses);

        $db->query("
            SELECT $select_clause, count(*) as total
            FROM {PREFIX}form_{$form_id}
            $where_clause
            GROUP BY period
        ");
        $db->execute();
        $rows = $db->fetchAll();

        foreach ($rows as $row) {
            if (array_key_exists($row["period"], $stats)) {
                $stats[$row["period"]]["data"] = $row["total"];
            } else {
                if ($submission_count_group == "day") {
                    $date = date("M jS", CoreGeneral::convertDatetimeToTimestamp($row["period"] . " 00:00:00"));
                } else {
                    list($year, $month) = explode("/", $row["period"]);
                    $date = date("M Y", mktime(0, 0, 0, $month, 1, $year));
                }
                $stats[$row["period"]] = array(
                    "label" => $date,
                    "data"  => $row["total"]
                );
            }
        }

        $results = array();
        while (list($key, $value) = each($stats)) {
            $results[] = $value;
        }

        return array(
            "period" => $submission_count_group,
            "data"   => $results
        );
    }

	/**
	 * Helper function for dv_get_activity_info(). This looks at the count grouping (day / month) and returns
	 * the appropriate SELECT query clause.
	 * @param $submission_count_group
	 * @param $date_range
	 * @param $date_range_clause
	 * @param $form_id
	 * @param $filter_sql_clauses
	 * @return array
	 */
    public static function getActivityAccountSelectQueryInfo($submission_count_group, $date_range, $date_range_clause,
        $form_id, $filter_sql_clauses)
    {
        $db = Core::$db;

        $filter_sql_clause = "";
        if (!empty($filter_sql_clauses)) {
            $filter_sql_clause = "(" . implode(" AND ", $filter_sql_clauses) . ")";
        }

        // SELECT clause
        $select_clause = "";
        if ($submission_count_group == "day") {
            $select_clause = "DATE(submission_date) as period";
        } else if ($submission_count_group == "month") {
            $select_clause = "CONCAT(YEAR(submission_date), '/', MONTH(submission_date)) as period";
        }

        $day_in_secs = 60 * 60 * 24;

        // now figure out what days / months we actually need to return data for
        $stats  = array();
        if ($submission_count_group == "day") {
            // if no date range clause was specified, the user wants to return everything. In which case, the start date
            // is dependant on what's stored in the database. [The end date is ALWAYS today - regardless of how messed up the
            // submission data info actually is]
            if (empty($date_range_clause))  {
                $where_clause = "";
                if (!empty($filter_sql_clause)) {
                    $where_clause = "WHERE $filter_sql_clause";
                }

                $db->query("
                    SELECT DATE(submission_date)
                    FROM   {PREFIX}form_{$form_id}
                    $where_clause
                    ORDER BY submission_date ASC
                    LIMIT 1
                ");
                $db->execute();

                $first_day = $db->fetch(PDO::FETCH_COLUMN);
            } else {
                switch ($date_range) {
                    case "year_to_date":
                        $first_day = date("Y") . "-01-01";
                        break;
                    case "month_to_date":
                        $first_day = date("Y-m") . "-01";
                        break;
                    default:
                        $first_day = date("Y-m-d", date("U") - (General::$intervalMap[$date_range] * $day_in_secs));
                        break;
                }
            }

            // each result is a DAY. Make a list of all possible days; we'll overlay the actual counts after
            // we get the results of the DB query. This ensures there are no gaps [be better to do this client-side...!]
            $current_day_unix_time = CoreGeneral::convertDatetimeToTimestamp($first_day . " 00:00:00");
            $last_day_unix_time    = CoreGeneral::convertDatetimeToTimestamp(date("Y-m-d") . " 00:00:00");
            while ($current_day_unix_time <= $last_day_unix_time) {
                $date = date("Y-m-d", $current_day_unix_time);
                $stats[$date] = array(
                    "label" => date("M jS", CoreGeneral::convertDatetimeToTimestamp($date . " 00:00:00")),
                    "data"  => 0
                );
                $current_day_unix_time += $day_in_secs;
            }
        } else if ($submission_count_group == "week") {
            /*
                $select = "CONCAT(YEAR(submission_date), '/', WEEK(submission_date, 0)) as period";

                // if no date range clause was specified, the user wants to return "everything". In which case, the start and end date
                // are dependant on whatever's stored in the database
                $first_day = "";
                $last_day  = "";
                if (empty($date_range_clause))
                {
                  $first_day_query = mysql_query("
                    SELECT CONCAT(YEAR(submission_date), '/', WEEK(submission_date, 0)) as wk
                    FROM   {PREFIX}form_{$form_id}
                    ORDER BY submission_date ASC
                    LIMIT 1
                  ");
                  $first_day_result = mysql_fetch_assoc($first_day_query);

                  $last_day_query  = mysql_query("
                    SELECT CONCAT(YEAR(submission_date), '/', WEEK(submission_date, 0)) as wk
                    FROM   {PREFIX}form_{$form_id}
                    ORDER BY submission_date DESC
                    LIMIT 1
                  ");
                  $last_day_result = mysql_fetch_assoc($last_day_query);

                  list($start_year, $start_week) = explode("/", $first_day_result["wk"]);
                  list($end_year, $end_week)     = explode("/", $last_day_result["wk"]);

                  $curr_year = $start_year;
                  $curr_week = $start_week;
                  while (true)
                  {
                    $days = ($curr_week) * 7;
                    echo "($curr_year - week: $curr_week, $days) ";

                    $range_start = date("Y m d", strtotime("{$curr_year}-01-01 + $days days"));
                    $days += 6;
                    $range_end   = date("Y m d", strtotime("{$curr_year}-01-01 + $days days"));
                    echo "$range_start - $range_end\n";

                    if ($curr_week >= 52)
                    {
                      $curr_year++;
                      $curr_week = 0;
                    }
                    else
                      $curr_week++;

                    if ($curr_year == $end_year && $curr_week > $end_week)
                    {
                      break;
                    }
                  }

                  for ($i=$start_int; $i<=$end_int; $i++)
                  {
                    $year = floor($i / 53);
                    $week = $i % 53;
                    $days = ($week) * 7;

                    echo "($year - week: $week, $days)";

                    $range_start = date("Y m d", strtotime("{$year}-01-01 + $days days"));
                    $days += 6;
                    $range_end   = date("Y m d", strtotime("{$year}-01-01 + $days days"));

                    echo "$range_start - $range_end\n";
                  }
                }
                else
                {
                  $first_day_query = mysql_query("
                    SELECT DATE(submission_date) as d
                    FROM   {PREFIX}form_{$form_id}
                    WHERE  $date_range_clause
                    ORDER BY submission_date ASC
                    LIMIT 1
                  ");

                  $first_day_result = mysql_fetch_assoc($first_day_query);
                  $first_day = General::convertDatetimeToTimestamp($first_day_result["d"] . " 00:00:00");

                  // since the user was searching a specific date range, all of them have today as the final day
                  $last_day = date("Y-m-d");
                }
            */

        } else if ($submission_count_group == "month") {

            // if no date range clause was specified, the user wants to return everything. In which case, the start date
            // is therefore dependant on whatever's stored in the database
            $first_day = "";
            list($last_year, $last_month, $last_day) = explode("-", date("Y-m-d"));
            if (empty($date_range_clause)) {
                $where_clause = "";
                if (!empty($filter_sql_clause)) {
                    $where_clause = "WHERE $filter_sql_clause";
                }

                $db->query("
                    SELECT DATE(submission_date)
                    FROM   {PREFIX}form_{$form_id}
                    $where_clause
                    ORDER BY submission_date ASC
                    LIMIT 1
                ");
                $db->execute();

                $first_month = $db->fetch(PDO::FETCH_COLUMN);
                list ($start_year, $start_month, $start_day) = explode("-", $first_month);
            } else {
                switch ($date_range) {
                    case "year_to_date":
                        $first_day = date("Y") . "-01-01";
                        break;
                    case "month_to_date":
                        $first_day = date("Y-m") . "-01";
                        break;
                    default:
                        $first_day = date("Y-m-d", date("U") - (General::$intervalMap[$date_range] * $day_in_secs));
                        break;
                }
                list($start_year, $start_month, $start_day) = explode("-", $first_day);
            }

            // each result is a MONTH. Make a list of all possible months; we'll overlay the actual counts after
            // we get the results of the DB query
            $start_int = ($start_year * 12) + $start_month;
            $end_int   = ($last_year * 12) + $last_month;

            $curr_int = $start_int;
            while ($curr_int <= $end_int) {
                $year  = floor($curr_int / 12);
                $month = $curr_int % 12;
                $u = mktime(0, 0, 0, $month, 1, $year);
                $key = date("Y/n", $u);
                $stats[$key] = array(
                    "label" => date("M Y", $u),
                    "data"  => 0
                );
                $curr_int++;
            }
        }

        return array($select_clause, $stats);
    }


    private static function updateVisualizationMainTab($vis_id, $info)
    {
        $db = Core::$db;

        $db->query("
            UPDATE {PREFIX}module_data_visualizations
            SET    vis_name = :vis_name,
                   form_id = :form_id,
                   cache_update_frequency = :cache_update_frequency
            WHERE  vis_id = :vis_id
        ");
        $db->bindAll(array(
            "vis_name" => $info["vis_name"],
            "form_id" => $info["form_id"],
            "cache_update_frequency" => $info["cache_update_frequency"],
            "vis_id" => $vis_id
        ));
        $db->execute();
    }


    private static function updateVisualizationAppearanceTab($vis_id, $info)
    {
        $db = Core::$db;

        $db->query("
            UPDATE {PREFIX}module_data_visualizations
            SET    chart_type = :chart_type,
                   date_range = :date_range,
                   submission_count_group = :submission_count_group,
                   colour = :colour,
                   line_width = :line_width
            WHERE  vis_id = :vis_id
        ");
        $db->bindAll(array(
            "chart_type" => $info["chart_type"],
            "date_range" => $info["date_range"],
            "submission_count_group" => $info["submission_count_group"],
            "colour" => $info["colour"],
            "line_width" => isset($info["line_width"]) ? $info["line_width"] : 2,
            "vis_id" => $vis_id
        ));
        $db->execute();
    }


    private static function updateVisualizationPermissionsTab($vis_id, $info)
    {
        $db = Core::$db;

        $selected_client_ids = (isset($info["selected_client_ids"])) ? $info["selected_client_ids"] : array();

        $db->query("
            DELETE FROM {PREFIX}module_data_visualization_clients
            WHERE vis_id = :vis_id
        ");
        $db->bind("vis_id", $vis_id);
        $db->execute();

        foreach ($selected_client_ids as $account_id) {
            $db->query("
                INSERT INTO {PREFIX}module_data_visualization_clients (vis_id, account_id)
                VALUES ($vis_id, $account_id)
            ");
            $db->bindAll(array(
                "vis_id" => $vis_id,
                "account_id" => $account_id
            ));
            $db->execute();
        }

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
            "access_type" => $info["access_type"],
            "access_view_mapping" => $access_view_mapping,
            "access_views" => $access_views,
            "vis_id" => $vis_id
        ));
        $db->execute();
    }
}
