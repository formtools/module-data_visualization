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
        "hook_type"       => "code",
        "action_location" => "main",
        "function_name"   => "FormTools\\Submissions::displaySubmissionListingQuicklinks",
        "hook_function"   => "addQuicklink",
        "priority"        => "50"
    ),
    array(
        "hook_type"       => "template",
        "action_location" => "head_bottom",
        "function_name"   => "",
        "hook_function"   => "includeInHead",
        "priority"        => "50"
    ),
    array(
        "hook_type"       => "code",
        "action_location" => "start",
        "function_name"   => "FormTools\\Forms::deleteForm",
        "hook_function"   => "deleteFormHook",
        "priority"        => "50"
    ),
    array(
        "hook_type"       => "template",
        "action_location" => "data_visualization",
        "function_name"   => "",
        "hook_function"   => "displayInPagesModule",
        "priority"        => "50"
    )
);


$FILES = array(

);

