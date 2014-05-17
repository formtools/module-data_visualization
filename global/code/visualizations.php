<?php


function dv_add_visualization($info)
{
	global $g_table_prefix, $L;

	$info = ft_sanitize($info);

	$vis_name    = $info["vis_name"];
	$data_source = $info["data_source"];
  $form_id     = $info["form_id"];
  $view_id     = $info["view_id"];

  $field_ids = "";
  if ($data_source == "single_field")
  {
  	$field_ids = $info["single_field_id"];
  }
  else
  {
  	$field_ids = implode(",", $info["field_ids"]);
  }

  $result = mysql_query("
    INSERT INTO {$g_table_prefix}module_data_visualizations (vis_name, vis_type, form_id, view_id, data_source, field_ids)
    VALUES ('$vis_name', 'pie_chart', $form_id, $view_id, '$data_source', '$field_ids')
  ");

  if ($result)
  {
  	$vis_id = mysql_insert_id();
  	return array(true, $L["notify_visualization_added"], $vis_id);
  }
  else
  {
  	return array(true, $L["notify_visualization_not_added"]);
  }
}


/**
 * Retrieves all information about a visualization.
 *
 * @param integer $vis_id
 */
function dv_get_visualization($vis_id)
{
  global $g_table_prefix, $L;

  $vis_id = ft_sanitize($vis_id);
  $query = mysql_query("SELECT * FROM {$g_table_prefix}module_data_visualizations WHERE vis_id = $vis_id");
  $vis_info = mysql_fetch_assoc($query);

  $client_query = mysql_query("SELECT account_id FROM {$g_table_prefix}module_data_visualization_clients WHERE vis_id = $vis_id");
  $client_ids = array();
  while ($row = mysql_fetch_assoc($client_query))
  {
    $client_ids[] = $row["account_id"];
  }
  $vis_info["client_ids"] = $client_ids;

  return $vis_info;
}


/**
 * Retrieves all information needed to display a visualization, regardless of type. All you need to do is pass
 * the $vis_id.
 *
 * @param integer $vis_id
 * @return array
 */
function dv_get_visualization_for_display($vis_id)
{
  global $g_table_prefix, $L;

  $vis_id = ft_sanitize($vis_id);
  $query = mysql_query("SELECT * FROM {$g_table_prefix}module_data_visualizations WHERE vis_id = $vis_id");

  $vis_info = mysql_fetch_assoc($query);
  $form_id = $vis_info["form_id"];
  $view_id = $vis_info["view_id"];
  $cache_update_frequency = $vis_info["cache_update_frequency"];

  $return_info = array();
  $return_info["cache_update_frequency"] = $cache_update_frequency;

  switch ($vis_info["vis_type"])
  {
  	case "activity":
	    $date_range             = $vis_info["date_range"];
	    $submission_count_group = $vis_info["submission_count_group"];

	    // this returns a hash with "period" and "data" keys
	    $return_info = dv_get_cached_activity_info($vis_id, $cache_update_frequency, $form_id, $view_id, $date_range, $submission_count_group);
	    $return_info["vis_type"]   = "activity";
	    $return_info["chart_type"] = $vis_info["chart_type"];
	    $return_info["vis_id"]     = $vis_id;
	    $return_info["vis_name"]   = $vis_info["vis_name"];
	    $return_info["vis_colour"] = $vis_info["colour"];
	    $return_info["line_width"] = $vis_info["line_width"];
  		break;

    case "field":
    	$field_id = $vis_info["field_id"];
    	$ignore_empty_fields = $vis_info["field_chart_ignore_empty_fields"];
    	$return_info = dv_get_cached_field_info($vis_id, $cache_update_frequency, $form_id, $view_id, $field_id, $ignore_empty_fields);
      $return_info["vis_type"]   = "field";
      $return_info["chart_type"] = $vis_info["chart_type"];
      $return_info["vis_id"]     = $vis_id;
      $return_info["vis_name"]   = $vis_info["vis_name"];
      $return_info["vis_colour"]   = $vis_info["colour"];
      $return_info["include_legend_quicklinks"] = $vis_info["include_legend_quicklinks"];
      $return_info["include_legend_full_size"]  = $vis_info["include_legend_full_size"];
      $return_info["pie_chart_format"]    = $vis_info["pie_chart_format"];
      $return_info["ignore_empty_fields"] = $vis_info["field_chart_ignore_empty_fields"];
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
function dv_search_visualizations($search_criteria)
{
  global $g_table_prefix;

  $search_criteria = ft_sanitize($search_criteria);

  $where_clauses = array();
  if (isset($search_criteria["keyword"]) && !empty($search_criteria["keyword"]))
  {
  	$keyword = ft_sanitize($search_criteria["keyword"]);
    $where_clauses[] = "vis_name LIKE '%$keyword%'";
  }
  if (isset($search_criteria["form_id"]) && !empty($search_criteria["form_id"]))
    $where_clauses[] = "form_id = {$search_criteria["form_id"]}";

  if (isset($search_criteria["vis_types"]) && !empty($search_criteria["vis_types"]))
  {
  	$vis_type_clauses = array();
  	foreach ($search_criteria["vis_types"] as $vis_type)
  	{
  		$vis_type_clauses[] = "vis_type = '$vis_type'";
  	}
  	$where_clauses[] = "(" . implode(" OR ", $vis_type_clauses) . ")";
  }
  if (isset($search_criteria["chart_type"]) && !empty($search_criteria["chart_type"]))
  {
  	$where_clauses[] = "chart_type = '{$search_criteria["chart_type"]}'";
  }

  $where_clause = (empty($where_clauses)) ? "" : "WHERE " . implode(" AND ", $where_clauses);

  $query = mysql_query("
    SELECT *
    FROM   {$g_table_prefix}module_data_visualizations
    $where_clause
    ORDER BY vis_id
  ");

  $account_type = $search_criteria["account_type"];
  $view_id      = isset($search_criteria["view_id"]) ? $search_criteria["view_id"] : "";
  $client_id    = isset($search_criteria["client_id"]) ? $search_criteria["client_id"] : "";

  $infohash = array();
  while ($row = mysql_fetch_assoc($query))
  {
    // filter out those visualizations that don't fit in the search
    if (!empty($view_id))
    {
      $access_view_mapping = $row["access_view_mapping"];
      $view_ids = explode(",", $row["access_views"]);

      if ($access_view_mapping == "except")
      {
        if (in_array($view_id, $view_ids))
          continue;
      }
      else if ($access_view_mapping == "only")
      {
        if (!in_array($view_id, $view_ids))
          continue;
      }
    }

    if ($account_type == "client")
    {
      if ($row["access_type"] == "admin")
      {
      	continue;
      }
      else if ($row["access_type"] == "private")
      {
        $client_ids = dv_get_visualization_clients($row["vis_id"]);
        if (!in_array($client_id, $client_ids))
          continue;
      }
    }

    $infohash[] = $row;
  }

  return $infohash;
}


function dv_clear_visualization_cache($vis_id)
{
	global $g_table_prefix;

  @mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_cache WHERE vis_id = $vis_id");
}


function dv_get_num_visualizations()
{
	global $g_table_prefix;
	$result = mysql_query("
	  SELECT count(*) as c
	  FROM {$g_table_prefix}module_data_visualizations
	");
  $info = mysql_fetch_assoc($result);
	return $info["c"];
}

function dv_display_visualization_icon($tamplate, $page_data)
{
	global $g_root_url, $g_table_prefix;

	$form_id = $page_data["form_id"];

	// find out if there are any visualizations to be shown for this form
	$query = mysql_query("
	  SELECT count(*) as c
	  FROM   {$g_table_prefix}module_data_visualizations
	  WHERE  form_id = $form_id AND
	         show_on_submission_listing_page = 'yes'
    ");

	$result = mysql_fetch_assoc($query);
	if ($result["c"] == 0)
	  return;

  echo "<div style=\"float: right; margin-top: -32px;\"><a href=\"#\"><img src=\"$g_root_url/modules/data_visualization/images/icon_visualization_small.png\" /></a></div>";
}


function dv_delete_visualization($vis_id)
{
  global $g_table_prefix, $L;

  if (empty($vis_id) || !is_numeric($vis_id))
  	return array();

	$query = @mysql_query("DELETE FROM {$g_table_prefix}module_data_visualizations WHERE vis_id = $vis_id");

	if (mysql_affected_rows() > 0)
	{
    @mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_clients WHERE vis_id = $vis_id");
    @mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_cache WHERE vis_id = $vis_id");
		return array(true, $L["notify_vis_deleted"]);
	}
	else
	{
		return array(false, $L["notify_vis_not_deleted"]);
	}
}


/**
 * Returns all clients that have been explicitly assigned to access the visualization. This is for visualizations
 * set with "private" access type only.
 *
 * @param $vis_id
 */
function dv_get_visualization_clients($vis_id)
{
  global $g_table_prefix;

  $query = mysql_query("
    SELECT account_id
    FROM   {$g_table_prefix}module_data_visualization_clients
    WHERE  vis_id = $vis_id
  ");

  $client_ids = array();
  while ($row = mysql_fetch_assoc($query))
  {
    $client_ids[] = $row["account_id"];
  }

  return $client_ids;
}


function dv_display_in_pages_module($location, $params)
{
	$attributes = $params["form_tools_all_template_hook_params"];

	if (!isset($attributes["vis_id"]) || empty($attributes["vis_id"]))
	{
	  echo "[Data Visualization hook error: <b>No vis_id attribute</b>]";
	  return;
	}
  if (!isset($attributes["height"]) || empty($attributes["height"]))
  {
    echo "[Data Visualization hook error: <b>No height attribute</b>]";
    return;
  }
  if (!isset($attributes["width"]) || empty($attributes["width"]))
  {
    echo "[Data Visualization hook error: <b>No width attribute</b>]";
    return;
  }

  $attributes = ft_sanitize($attributes);
  $vis_id = $attributes["vis_id"];
  $height = $attributes["height"];
  $width  = $attributes["width"];

  dv_display_visualization($vis_id, $height, $width);
}


function dv_display_visualization($vis_id, $height, $width)
{
  $vis_info = dv_get_visualization_for_display($vis_id);
  $vis_type   = $vis_info["vis_type"];
  $chart_type = $vis_info["chart_type"];
  $title      = ft_sanitize($vis_info["vis_name"]);

  $num_rows = count($vis_info["data"]);

  $js_lines = array();
  for ($i=0; $i<count($vis_info["data"]); $i++)
  {
    $label = $vis_info["data"][$i]["label"];
    $data  = $vis_info["data"][$i]["data"];
    $js_lines[] = "data.setValue($i, 0, \"$label\");";
    $js_lines[] = "data.setValue($i, 1, $data);";
  }

  switch ($chart_type)
  {
  	// line and area charts are specific to Activity Chart visualizations
    case "area_chart":
  	case "line_chart":
  		$chart_class = ($chart_type == "area_chart") ? "AreaChart" : "LineChart";
      $js_lines[] =<<< END
var chart = new google.visualization.$chart_class(document.getElementById("dv_vis_{$vis_id}"));
var settings = {
  width:  $width,
  height: $height,
  color: "{$vis_info["vis_colour"]}",
  lineWidth: {$vis_info["line_width"]},
  title: '$title',
  legend: 'none'
}
END;
      break;

    // Activity AND Field Charts
    case "column_chart":
    	if ($vis_type == "activity")
    	{
        $js_lines[] =<<< END
var chart = new google.visualization.ColumnChart(document.getElementById("dv_vis_{$vis_id}"));
var settings = {
  width:  $width,
  height: $height,
  color: "{$vis_info["vis_colour"]}",
  lineWidth: {$vis_info["line_width"]},
  title: '$title',
  legend: 'none'
}
END;
    	}
      break;

    case "bar_chart":
        $js_lines[] =<<< END
var chart = new google.visualization.BarChart(document.getElementById("dv_vis_{$vis_id}"));
var settings = {
  width:  $width,
  height: $height,
  colors: ["{$vis_info["vis_colour"]}"],
  title: '$title',
  legend: 'none'
}
END;
    	break;

    case "pie_chart":
    	$is_3D  = ($vis_info["pie_chart_format"] == "3D") ? "true" : "false";
    	$legend = ($vis_info["include_legend_full_size"] == "yes") ? "right" : "none";
      $js_lines[] =<<< END
var chart = new google.visualization.PieChart(document.getElementById("dv_vis_{$vis_id}"));
var settings = {
  width:  $width,
  height: $height,
  is3D: $is_3D,
  legend: '$legend',
  title: '$title'
}
END;
    	break;
  }

  $js_lines_str = implode("\n", $js_lines);

  echo <<< END
<script src="https://www.google.com/jsapi"></script>
<div id="dv_vis_{$vis_id}"></div>
<script>
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(vis_drawChart);
function vis_drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn("string", "...");
  data.addColumn("number", "Submissions");
  data.addRows($num_rows);
  $js_lines_str
  chart.draw(data, settings);
}
</script>
END;
}


/**
 * Helper function to return the previous and next Visualization IDs for
 *
 * @param $vis_id
 */
function dv_get_tabset_links($vis_id)
{
	$keyword        = ft_load_module_field("data_visualizations", "keyword", "dv_search_keyword", "");
	$search_form_id = ft_load_module_field("data_visualizations", "form_id", "dv_form_id", "");
	$search_view_id = ft_load_module_field("data_visualizations", "view_id", "dv_view_id", "");
	$vis_types      = ft_load_module_field("data_visualizations", "vis_types", "dv_vis_types", array("activity", "field"));
	$chart_type     = ft_load_module_field("data_visualizations", "chart_type", "dv_chart_type", "");
	$account_type   = ft_load_module_field("data_visualizations", "account_type", "dv_account_type", "admin");
	$client_id      = ft_load_module_field("data_visualizations", "client_id", "dv_client_id", "");

	$search_criteria = array(
	  "keyword"      => $keyword,
	  "form_id"      => $search_form_id,
	  "view_id"      => $search_view_id,
	  "vis_types"    => $vis_types,
	  "chart_type"   => $chart_type,
	  "account_type" => $account_type,
	  "client_id"    => $client_id
	    );

	$results = dv_search_visualizations($search_criteria);

  $return_info = array("prev_link" => "", "next_link" => "");
	$sorted_vis_ids = array();
	$vis_id_to_types = array();
  foreach ($results as $vis_info)
  {
    $sorted_vis_ids[] = $vis_info["vis_id"];
    $vis_id_to_types[$vis_info["vis_id"]] = $vis_info["vis_type"];
  }
  $current_index = array_search($vis_id, $sorted_vis_ids);

  if ($current_index === 0)
  {
    if (count($sorted_vis_ids) > 1)
    {
    	$next_vis_id = $sorted_vis_ids[$current_index+1];
      if ($vis_id_to_types[$next_vis_id] == "activity")
        $return_info["next_link"] = "../activity_charts/edit.php?vis_id=$next_vis_id";
      else
        $return_info["next_link"] = "../field_charts/edit.php?vis_id=$next_vis_id";
    }
  }
  else if ($current_index === count($sorted_vis_ids)-1)
  {
    if (count($sorted_vis_ids) > 1)
    {
    	$prev_vis_id = $sorted_vis_ids[$current_index-1];
      if ($vis_id_to_types[$prev_vis_id] == "activity")
        $return_info["prev_link"] = "../activity_charts/edit.php?vis_id=$prev_vis_id";
      else
        $return_info["prev_link"] = "../field_charts/edit.php?vis_id=$prev_vis_id";
    }
  }
  else
  {
    $prev_vis_id = $sorted_vis_ids[$current_index-1];
    if ($vis_id_to_types[$prev_vis_id] == "activity")
      $return_info["prev_link"] = "../activity_charts/edit.php?vis_id=$prev_vis_id";
  	else
  	  $return_info["prev_link"] = "../field_charts/edit.php?vis_id=$prev_vis_id";

    $next_vis_id = $sorted_vis_ids[$current_index+1];
    if ($vis_id_to_types[$next_vis_id] == "activity")
      $return_info["next_link"] = "../activity_charts/edit.php?vis_id=$next_vis_id";
    else
      $return_info["next_link"] = "../field_charts/edit.php?vis_id=$next_vis_id";
  }

  return $return_info;
}

