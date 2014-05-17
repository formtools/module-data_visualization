<?php

$STRUCTURE = array();
$STRUCTURE["tables"] = array();
$STRUCTURE["tables"]["module_data_visualizations"] = array(
  array(
    "Field"   => "vis_id",
    "Type"    => "mediumint(8) unsigned",
    "Null"    => "NO",
    "Key"     => "PRI",
    "Default" => ""
  ),
  array(
    "Field"   => "vis_name",
    "Type"    => "varchar(255)",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "vis_type",
    "Type"    => "enum('activity','field')",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "chart_type",
    "Type"    => "enum('line_chart','area_chart','column_chart','bar_chart','pie_chart')",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "form_id",
    "Type"    => "mediumint(9)",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "view_id",
    "Type"    => "mediumint(9)",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "field_id",
    "Type"    => "mediumint(9)",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "access_type",
    "Type"    => "enum('admin','public','private')",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => "public"
  ),
  array(
    "Field"   => "access_view_mapping",
    "Type"    => "enum('all','except','only')",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => "all"
  ),
  array(
    "Field"   => "access_views",
    "Type"    => "mediumtext",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "cache_update_frequency",
    "Type"    => "varchar(8)",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "date_range",
    "Type"    => "varchar(20)",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "submission_count_group",
    "Type"    => "enum('year','month','week','day')",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "colour",
    "Type"    => "varchar(10)",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "line_width",
    "Type"    => "tinyint(4)",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "field_chart_ignore_empty_fields",
    "Type"    => "enum('yes','no')",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "pie_chart_format",
    "Type"    => "enum('2D','3D')",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "include_legend_quicklinks",
    "Type"    => "enum('yes','no')",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "include_legend_full_size",
    "Type"    => "enum('yes','no')",
    "Null"    => "YES",
    "Key"     => "",
    "Default" => ""
  )
);
$STRUCTURE["tables"]["module_data_visualization_cache"] = array(
  array(
    "Field"   => "vis_id",
    "Type"    => "mediumint(8) unsigned",
    "Null"    => "NO",
    "Key"     => "PRI",
    "Default" => ""
  ),
  array(
    "Field"   => "last_cached",
    "Type"    => "datetime",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  ),
  array(
    "Field"   => "data",
    "Type"    => "mediumtext",
    "Null"    => "NO",
    "Key"     => "",
    "Default" => ""
  )
);
$STRUCTURE["tables"]["module_data_visualization_clients"] = array(
  array(
    "Field"   => "vis_id",
    "Type"    => "mediumint(8) unsigned",
    "Null"    => "NO",
    "Key"     => "PRI",
    "Default" => ""
  ),
  array(
    "Field"   => "account_id",
    "Type"    => "mediumint(8) unsigned",
    "Null"    => "NO",
    "Key"     => "PRI",
    "Default" => ""
  )
);



$HOOKS = array(
  array(
    "hook_type"       => "template",
    "action_location" => "admin_submission_listings_top",
    "function_name"   => "",
    "hook_function"   => "dv_display_visualization_icon",
    "priority"        => "50"
  ),
  array(
    "hook_type"       => "code",
    "action_location" => "main",
    "function_name"   => "ft_display_submission_listing_quicklinks",
    "hook_function"   => "dv_add_quicklink",
    "priority"        => "50"
  ),
  array(
    "hook_type"       => "template",
    "action_location" => "head_bottom",
    "function_name"   => "",
    "hook_function"   => "dv_include_in_head",
    "priority"        => "50"
  ),
  array(
    "hook_type"       => "code",
    "action_location" => "start",
    "function_name"   => "ft_delete_form",
    "hook_function"   => "dv_delete_form_hook",
    "priority"        => "50"
  ),
  array(
    "hook_type"       => "template",
    "action_location" => "data_visualization",
    "function_name"   => "",
    "hook_function"   => "dv_display_in_pages_module",
    "priority"        => "50"
  )
);


$FILES = array(
  "activity_charts/",
  "activity_charts/add.php",
  "activity_charts/edit.php",
  "activity_charts/index.html",
  "activity_charts/page_advanced.php",
  "activity_charts/page_appearance.php",
  "activity_charts/page_main.php",
  "activity_charts/page_permissions.php",
  "activity_charts/settings.php",
  "database_integrity.php",
  "field_charts/",
  "field_charts/add.php",
  "field_charts/edit.php",
  "field_charts/index.html",
  "field_charts/page_advanced.php",
  "field_charts/page_appearance.php",
  "field_charts/page_main.php",
  "field_charts/page_permissions.php",
  "field_charts/settings.php",
  "global/",
  "global/code/",
  "global/code/actions.php",
  "global/code/activity_charts.php",
  "global/code/field_charts.php",
  "global/code/general.php",
  "global/code/index.html",
  "global/code/module.php",
  "global/code/visualizations.php",
  "global/css/",
  "global/css/index.html",
  "global/css/styles.css",
  "global/css/visualizations.css",
  "global/index.html",
  "global/scripts/",
  "global/scripts/index.html",
  "global/scripts/manage_visualizations.js",
  "global/scripts/visualizations.js",
  "help.php",
  "images/",
  "images/example_area_chart.png",
  "images/example_pie_chart.png",
  "images/icon_visualization.png",
  "images/icon_visualization16x16.png",
  "images/refresh.png",
  "index.php",
  "lang/",
  "lang/en_us.php",
  "lang/index.html",
  "library.php",
  "module.php",
  "no_internet_connection.tpl",
  "settings.php",
  "smarty/",
  "smarty/function.activity_chart_date_range.php",
  "smarty/function.cache_frequency_dropdown.php",
  "smarty/function.colour_dropdown.php",
  "smarty/function.line_width_dropdown.php",
  "templates/",
  "templates/activity_charts/",
  "templates/activity_charts/add.tpl",
  "templates/activity_charts/edit.tpl",
  "templates/activity_charts/settings.tpl",
  "templates/activity_charts/tab_advanced.tpl",
  "templates/activity_charts/tab_appearance.tpl",
  "templates/activity_charts/tab_main.tpl",
  "templates/activity_charts/tab_permissions.tpl",
  "templates/field_charts/",
  "templates/field_charts/add.tpl",
  "templates/field_charts/edit.tpl",
  "templates/field_charts/settings.tpl",
  "templates/field_charts/tab_advanced.tpl",
  "templates/field_charts/tab_appearance.tpl",
  "templates/field_charts/tab_main.tpl",
  "templates/field_charts/tab_permissions.tpl",
  "templates/help.tpl",
  "templates/index.html",
  "templates/index.tpl",
  "templates/settings.tpl"
);

