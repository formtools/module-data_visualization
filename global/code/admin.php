<?php


function dv_search_visualizations($search_criteria)
{
	global $g_table_prefix, $L;

	print_r($search_criteria);
	exit;

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


function dv_get_visualizations($num_per_page, $page_num = 1)
{
  global $g_table_prefix;

  if ($num_per_page == "all")
  {
    $query = mysql_query("
      SELECT *
      FROM   {$g_table_prefix}module_data_visualizations
      ORDER BY vis_id
        ");
  }
  else
  {
    // determine the offset
    if (empty($page_num)) { $page_num = 1; }
    $first_item = ($page_num - 1) * $num_per_page;

    $query = mysql_query("
      SELECT *
      FROM   {$g_table_prefix}module_data_visualizations
      ORDER BY vis_id
      LIMIT $first_item, $num_per_page
        ") or handle_error(mysql_error());
  }

  $count_query = mysql_query("SELECT count(*) as c FROM {$g_table_prefix}module_data_visualizations");
  $count_hash = mysql_fetch_assoc($count_query);
  $num_results = $count_hash["c"];

  $infohash = array();
  while ($field = mysql_fetch_assoc($query))
  {
    $infohash[] = $field;
  }

  $return_hash["results"] = $infohash;
  $return_hash["num_results"] = $num_results;

  return $return_hash;
}


function dv_get_visualization($vis_id)
{
	global $g_table_prefix;

	$query = mysql_query("SELECT * FROM {$g_table_prefix}module_data_visualizations WHERE vis_id = $vis_id");
	$result = mysql_fetch_assoc($query);

	return $result;
}


function dv_get_visualization_data($vis_id)
{
	global $g_table_prefix;

  $vis_info = dv_get_visualization($vis_id);
  $form_id = $vis_info["form_id"];
  $view_id = $vis_info["view_id"];

  if ($vis_info["vis_type"] == "pie_chart")
  {
    if ($vis_info["data_source"] == "single_field")
    {
    	$field_id = $vis_info["field_ids"];
    	$return_info = ft_get_field_col_by_field_id($form_id, $field_id);
      $col_name = $return_info[$field_id];

      $query = mysql_query("
        SELECT $col_name as column_name, count(*) AS column_value
        FROM   {$g_table_prefix}form_{$form_id}
        GROUP BY $col_name
          ");
    }

    // for multiple fields, we get the AVERAGE of each field and return that
    else if ($vis_info["data_source"] == "multiple_fields")
    {

    }
  }

  $results = array();
  while ($row = mysql_fetch_assoc($query))
  {
  	$results[] = $row;
  }

  $vis_info["data"] = $results;

  return $vis_info;
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
