<?php


/**
 * Adds a new field chart.
 *
 * @param array $info
 */
function dv_add_field_chart($info)
{
  global $g_table_prefix, $L;

  $info = ft_sanitize($info);

  $module_settings = ft_get_module_settings("", "data_visualization");
  $vis_name   = $info["vis_name"];
  $chart_type = $info["chart_type"];
  $form_id    = $info["form_id"];
  $view_id    = $info["view_id"];
  $field_id   = $info["field_id"];
  $field_chart_ignore_empty_fields = $info["field_chart_ignore_empty_fields"];

  $pie_chart_format = "";
  $include_legend_quicklinks = "";
  $include_legend_full_size = "";
  $colour = "blue";
  if ($info["chart_type"] == "pie_chart")
  {
    $pie_chart_format          = $info["pie_chart_format"];
    $include_legend_quicklinks = $info["include_legend_quicklinks"];
    $include_legend_full_size  = $info["include_legend_full_size"];
    $colour = $info["colour_old"];
  }
  else
  {
    $colour = $info["colour"];
  }

  $cache_update_frequency = $module_settings["default_cache_frequency"];

  $query = mysql_query("
    INSERT INTO {$g_table_prefix}module_data_visualizations (vis_name, vis_type, chart_type,
      form_id, view_id, field_id, cache_update_frequency, field_chart_ignore_empty_fields, pie_chart_format, colour,
      include_legend_quicklinks, include_legend_full_size)
    VALUE ('$vis_name', 'field', '$chart_type', $form_id, $view_id, $field_id,
      '$cache_update_frequency', '$field_chart_ignore_empty_fields', '$pie_chart_format', '$colour',
      '$include_legend_quicklinks', '$include_legend_full_size')
  ") or die(mysql_error());

  if ($query)
  {
    $vis_id = mysql_insert_id();
    return array(true, $L["notify_field_chart_created"], $vis_id);
  }
  else
  {
    return array(false, $L["notify_error_creating_field_chart"], "");
  }
}


/**
 * This is a wrapper for dv_get_activity_info(). It checks the cache to see if there's a recent cache that
 * can be used instead of re-doing the SQL query.
 *
 * @param integer $vis_id
 * @param string $cache_update_frequency an hour (integer), or "no_cache"
 * @param integer $form_id
 * @param integer $view_id
 * @param integer $field_id
 */
function dv_get_cached_field_info($vis_id, $cache_update_frequency, $form_id, $view_id, $field_id, $ignore_empty_fields)
{
  global $g_table_prefix;

  // if the user has request NO cache for this Activity Chart, always do a fresh query
  if ($cache_update_frequency == "no_cache")
  {
    $return_info = dv_get_field_chart_info($form_id, $view_id, $field_id, $ignore_empty_fields);
    $return_info["cache_update_frequency"] = $cache_update_frequency;
    return $return_info;
  }

  // otherwise, check to see if there's the cached data within the cache frequency period specified
  $query = mysql_query("
    SELECT *
    FROM   {$g_table_prefix}module_data_visualization_cache
    WHERE  vis_id = $vis_id AND
           last_cached >= DATE_SUB(NOW(), INTERVAL $cache_update_frequency MINUTE)
    LIMIT 1
  ");

  // great! used the cached value
  if (mysql_num_rows($query) == 1)
  {
  	$result = mysql_fetch_assoc($query);
    return array(
      "form_id"     => $form_id,
      "view_id"     => $view_id,
      "field_id"    => $field_id,
      "data"        => unserialize($result["data"]),
      "last_cached" => $result["last_cached"],
      "cache_update_frequency" => $cache_update_frequency
    );
  }

  // Here, there's nothing valid in the cache. Run the query and cache the data.
  $return_info = dv_get_field_chart_info($form_id, $view_id, $field_id, $ignore_empty_fields);
  $return_info["cache_update_frequency"] = $cache_update_frequency;

  mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_cache WHERE vis_id = $vis_id");
  $data = ft_sanitize(serialize($return_info["data"]));

  $now = ft_get_current_datetime();
  $insert_query = "INSERT INTO {$g_table_prefix}module_data_visualization_cache (vis_id, last_cached, data) VALUES ($vis_id, '$now', '$data')";
  mysql_query($insert_query) or die($insert_query . " - " . mysql_error());

  return $return_info;
}


function dv_get_field_chart_info($form_id, $view_id, $field_id, $ignore_empty_fields)
{
  global $g_table_prefix;

  $result = ft_get_field_col_by_field_id($form_id, $field_id);
  $col_name = $result[$field_id];

  $filter_where_clause = "";
  if (!empty($view_id))
  {
    $filter_sql_clauses = ft_get_view_filter_sql($view_id);
	  if (!empty($filter_sql_clauses)) {
	    $filter_where_clause = " AND (" . implode(" AND ", $filter_sql_clauses) . ")";
	  }
  }

  if ($ignore_empty_fields == "yes")
  {
    $query = mysql_query("
      SELECT $col_name as field_value, count(*) as c
      FROM   {$g_table_prefix}form_{$form_id}
      WHERE  $col_name IS NOT NULL AND TRIM($col_name) != ''
             $filter_where_clause
      GROUP BY field_value
    ");
  }
  else
  {
    $query = mysql_query("
      SELECT
      CASE
        WHEN $col_name IS NULL OR $col_name = ''
        THEN NULL
        ELSE $col_name
      END as field_value, count(*) as c
      FROM {$g_table_prefix}form_{$form_id}
      WHERE 1 = 1
            $filter_where_clause
      GROUP BY field_value
    ");
  }

  $results = array();
  while ($row = mysql_fetch_assoc($query))
  {
    $results[] = array(
      "label" => $row["field_value"],
      "data"  => $row["c"]
    );
  }

  // if this field is assigned to an Option List, we sort the results by the order in which the options
  // were specified and pass along the labels, not actual stored values. Otherwise we just return the alphabetical
  // results
  $form_field = ft_get_form_field($field_id, array("include_field_settings" => true));
  $field_type_info = ft_get_field_type($form_field["field_type_id"]);

  if (!empty($field_type_info["raw_field_type_map_multi_select_id"]))
  {
    $option_list_setting_id = $field_type_info["raw_field_type_map_multi_select_id"];
    $option_list_id = (isset($form_field["settings"][$option_list_setting_id])) ? $form_field["settings"][$option_list_setting_id] : "";

    if (!empty($option_list_id))
    {
    	$option_list = ft_get_option_list_options($option_list_id);
    	$map = array();
    	foreach ($option_list as $grouped)
    	{
    		if (!is_array($grouped["options"]))
    		  continue;

    		foreach ($grouped["options"] as $option)
    		{
    		  $map[$option["option_value"]] = $option["option_name"];
    		}
    	}

      $updated_results = array();
      while (list($val, $name) = each($map))
      {
        foreach ($results as $result)
        {
          if ($val == $result["label"])
          {
            $updated_results[] = array(
              "label" => $map[$result["label"]],
              "data"  => $result["data"]
            );
            break;
          }
        }
      }

      $results = $updated_results;
    }
  }

  $now = ft_get_current_datetime();

  return array(
    "form_id"     => $form_id,
    "view_id"     => $view_id,
    "field_id"    => $field_id,
    "data"        => $results,
    "last_cached" => $now
  );
}


/**
 * Updates the appropriate tab of the field chart.
 *
 * @param integer $vis_id
 * @param array $info
 */
function dv_update_field_chart($vis_id, $tab, $info)
{
  global $g_table_prefix, $L;

  $info = ft_sanitize($info);

  switch ($tab)
  {
    case "main":
      $vis_name   = $info["vis_name"];
      $form_id    = $info["form_id"];
      $view_id    = $info["view_id"];
      $field_id   = $info["field_id"];
      $cache_update_frequency = $info["cache_update_frequency"];

      $query = mysql_query("
        UPDATE {$g_table_prefix}module_data_visualizations
        SET    vis_name = '$vis_name',
               form_id = $form_id,
               view_id = $view_id,
               field_id = $field_id,
               cache_update_frequency = '$cache_update_frequency'
        WHERE  vis_id = $vis_id
      ") or die(mysql_error());
      break;

    case "appearance":
      $chart_type = $info["chart_type"];
      $field_chart_ignore_empty_fields = $info["field_chart_ignore_empty_fields"];

      $pie_chart_format = "";
      $include_legend_quicklinks = "";
      $include_legend_full_size = "";
      $colour = "";
      if ($info["chart_type"] == "pie_chart")
      {
        $pie_chart_format          = $info["pie_chart_format"];
        $include_legend_quicklinks = $info["include_legend_quicklinks"];
        $include_legend_full_size  = $info["include_legend_full_size"];
        $colour = $info["colour_old"];
      }
      else
      {
      	$colour = $info["colour"];
      }

      $query = mysql_query("
        UPDATE {$g_table_prefix}module_data_visualizations
        SET    chart_type = '$chart_type',
               field_chart_ignore_empty_fields = '$field_chart_ignore_empty_fields',
               pie_chart_format = '$pie_chart_format',
               colour = '$colour',
               include_legend_quicklinks = '$include_legend_quicklinks',
               include_legend_full_size = '$include_legend_full_size'
        WHERE  vis_id = $vis_id
      ") or die(mysql_error());
      break;

    case "permissions":
      $selected_client_ids = (isset($info["selected_client_ids"])) ? $info["selected_client_ids"] : array();
		  mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_clients WHERE vis_id = $vis_id");
		  foreach ($selected_client_ids as $account_id)
		  {
		    mysql_query("
		      INSERT INTO {$g_table_prefix}module_data_visualization_clients (vis_id, account_id)
		      VALUES ($vis_id, $account_id)
		        ");
		  }
		  $access_type         = $info["access_type"];
		  $access_view_mapping = $info["access_view_mapping"];

      $access_views = "";
      if ($access_view_mapping != "all") {
        $view_ids = (isset($info["view_ids"])) ? $info["view_ids"] : array();
        $access_views = implode(",", $view_ids);
      }

      $query = mysql_query("
        UPDATE {$g_table_prefix}module_data_visualizations
        SET    access_type = '$access_type',
               access_view_mapping = '$access_view_mapping',
               access_views = '$access_views'
        WHERE  vis_id = $vis_id
      ") or die(mysql_error());
  }

  dv_clear_visualization_cache($vis_id);

  if ($query)
  {
    return array(true, $L["notify_field_chart_updated"]);
  }
  else
  {
    return array(false, $L["notify_error_updating_field_chart"], "");
  }
}
