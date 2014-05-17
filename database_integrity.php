<?php

$HOOKS = array();
$HOOKS["1.0.7"] = array(
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