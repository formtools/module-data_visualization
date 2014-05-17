<?php


function data_visualization__install($module_id)
{
  global $g_table_prefix, $g_root_dir, $g_root_url, $LANG;

  $success = "";
  $message = "";
  $encrypted_key = isset($_POST["ek"]) ? $_POST["ek"] : "";
  $module_key    = isset($_POST["k"]) ? $_POST["k"] : "";
  if (empty($encrypted_key) || empty($module_key) || $encrypted_key != crypt($module_key, "jj"))
  {
    $success = false;
  }
  else
  {
  	$success = true;
	  $queries = array();
	  $table1 = $g_table_prefix . "module_data_visualizations";
	  $queries[] = "
			CREATE TABLE $table1 (
			  vis_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  vis_name varchar(255) NOT NULL,
			  vis_type enum('activity','field') NOT NULL,
			  chart_type enum('line_chart','area_chart','column_chart','bar_chart','pie_chart') NOT NULL,
			  form_id mediumint(9) NOT NULL,
			  view_id mediumint(9) DEFAULT NULL,
			  field_id mediumint(9) DEFAULT NULL,
			  access_type enum('admin','public','private') NOT NULL DEFAULT 'public',
			  access_view_mapping enum('all','except','only') NOT NULL DEFAULT 'all',
			  access_views mediumtext NOT NULL,
			  cache_update_frequency varchar(8) NOT NULL,
			  date_range varchar(20) DEFAULT NULL,
			  submission_count_group enum('year','month','week','day') DEFAULT NULL,
			  colour varchar(10) DEFAULT NULL,
			  line_width tinyint(4) DEFAULT NULL,
			  field_chart_ignore_empty_fields enum('yes','no') DEFAULT NULL,
			  pie_chart_format enum('2D','3D') DEFAULT NULL,
			  include_legend_quicklinks enum('yes','no') DEFAULT NULL,
			  include_legend_full_size enum('yes','no') DEFAULT NULL,
			  PRIMARY KEY (vis_id)
			) DEFAULT CHARSET=utf8
	  ";

	  $table2 = $g_table_prefix . "module_data_visualization_cache";
	  $queries[] = "
			CREATE TABLE $table2 (
			  vis_id mediumint(8) unsigned NOT NULL,
			  last_cached datetime NOT NULL,
			  `data` mediumtext NOT NULL,
			  PRIMARY KEY (vis_id)
			) DEFAULT CHARSET=utf8
	  ";

	  $table3 = $g_table_prefix . "module_data_visualization_clients";
	  $queries[] = "
			CREATE TABLE $table3 (
			  vis_id mediumint(8) unsigned NOT NULL,
			  account_id mediumint(8) unsigned NOT NULL,
			  PRIMARY KEY (vis_id,account_id)
			) DEFAULT CHARSET=utf8
	  ";

	  foreach ($queries as $query)
	  {
	    $result = mysql_query($query);
	    if (!$result)
	    {
	      return array(false, $LANG["data_visualization"]["notify_installation_problem_c"] . " <b>" . mysql_error() . "</b>");
	    }
	  }

	  ft_register_hook("template", "data_visualization", "admin_submission_listings_top", "", "dv_display_visualization_icon", 50, true);
	  ft_register_hook("code", "data_visualization", "main", "ft_display_submission_listing_quicklinks", "dv_add_quicklink", 50, true);
	  ft_register_hook("template", "data_visualization", "head_bottom", "", "dv_include_in_head", 50, true);
	  ft_register_hook("code", "data_visualization", "start", "ft_delete_form", "dv_delete_form_hook", 50, true);

	  // a custom hook for use in Smarty pages generated via the Pages module
	  ft_register_hook("template", "data_visualization", "data_visualization", "", "dv_display_in_pages_module", 50, true);

	  $settings = array(

	    // main settings
	    "quicklinks_dialog_width"      => 880,
	    "quicklinks_dialog_height"     => 400,
	    "quicklinks_dialog_thumb_size" => 200,
	    "default_cache_frequency"      => 30,
	    "clients_may_refresh_cache"    => "yes",

	    // Activity Chart default settings
	    "activity_chart_date_range"               => "last_30_days",
	    "activity_chart_submission_count_group"   => "day",
	    "activity_chart_default_chart_type"       => "line_chart",
	    "activity_chart_colour"                   => "blue",
	    "activity_chart_line_width"               => 2,

	    // Field Chart default settings
	    "field_chart_default_chart_type"          => "pie_chart",
	    "field_chart_include_legend_quicklinks"   => "no",
	    "field_chart_colour"                      => "blue",
	    "field_chart_include_legend_full_size"    => "yes",
	    "field_chart_pie_chart_format"            => "2D",
	    "field_chart_ignore_empty_fields"         => "yes"
	  );
	  ft_set_settings($settings, "data_visualization");
  }

  return array($success, $message);
}


function data_visualization__uninstall($module_id)
{
  global $g_table_prefix;

  $table1 = $g_table_prefix . "module_data_visualizations";
  mysql_query("DROP TABLE $table1");
  $table2 = $g_table_prefix . "module_data_visualization_cache";
  mysql_query("DROP TABLE $table2");
  $table3 = $g_table_prefix . "module_data_visualization_clients";
  mysql_query("DROP TABLE $table3");
  return array(true, "");
}

