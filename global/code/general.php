<?php

/**
 * This returns all visualizations flagged to show up in the quicklinks dialog window.
 *
 * @param $view_id
 */
function dv_get_quicklink_visualizations($form_id, $view_id)
{
  global $g_table_prefix;

  $account_type = ft_is_admin() ? "admin" : "client";

  // Step 1: get all accessible visualizations
  $private_client_accessible_vis_ids = array();
  if ($is_client)
  {
    $query = mysql_query("
      SELECT vis_id
      FROM   {$g_table_prefix}module_data_visualization_clients
      WHERE  account_id = $account_id
        ");
    while ($row = mysql_fetch_assoc($query))
      $private_client_accessible_vis_ids[] = $row["vis_id"];
  }

  $search_criteria = array(
    "form_id"      => $form_id,
    "view_id"      => $view_id,
    "account_type" => $account_type
  );
  $visualizations = dv_search_visualizations($search_criteria);

  $accessible_visualizations = array();
  foreach ($visualizations as $vis_info)
  {
    if ($vis_info["access_type"] == "public")
      $accessible_visualizations[] = $vis_info["vis_id"];
    else
    {
      if ($account_type == "client")
      {
        if ($vis_info["access_type"] != "admin" && in_array($vis_info["export_group_id"], $private_client_accessible_vis_ids))
          $accessible_visualizations[] = $vis_info["vis_id"];
      }
      else
      {
        $accessible_visualizations[] = $vis_info["vis_id"];
      }
    }
  }

  return $accessible_visualizations;
}


/**
 * This adds the quicklink icon to the Submission Listing page. This function is already assigned to those
 * particular hooks on the admin and client Submission Listing pages.
 *
 * It only shows the icon if there's at least ONE visualization to show for this form View.
 *
 * @param array $params
 */
function dv_add_quicklink($params)
{
  global $g_root_url, $g_smarty;

  $module_settings = ft_get_module_settings("", "data_visualization");
  if ($g_smarty->_tpl_vars["page"] == "client_forms" && $module_settings["hide_from_client_accounts"] == "yes") {
    return;
  }

  $account_id = isset($_SESSION["ft"]["account"]["account_id"]) ? $_SESSION["ft"]["account"]["account_id"] : "";
  $form_id = $g_smarty->_tpl_vars["SESSION"]["curr_form_id"];
  $view_id = $g_smarty->_tpl_vars["view_id"];

  $vis_ids = dv_get_quicklink_visualizations($form_id, $view_id);

  if (empty($vis_ids))
    return;


  $vis_id_str = implode(",", $vis_ids);

  // output the visualization IDs right into the page. This will save an HTTP request to retrieve them later
  echo <<< END
<script>g.vis_ids = [$vis_id_str];</script>
END;

  $L = ft_get_module_lang_file_contents("data_visualization");

  $quicklinks = array(
    "icon_url"   => "$g_root_url/modules/data_visualization/images/icon_visualization16x16.png",
    "title_text" => "{$L["phrase_view_visualizations"]}",
    "href"       => "#",
    "onclick"    => "return dv_ns.show_visualizations_dialog()"
  );

  return array("quicklinks" => $quicklinks);
}


/**
 * This function returns a string of JS containing the list of forms and form Views in the page_ns
 * namespace.
 *
 * Its tightly coupled with the calling page, which is kind of crumby; but it can be refactored later
 * as the need arises.
 */
function dv_get_form_view_mapping_js()
{
  $forms = ft_get_forms();

  $js_rows = array();
  $js_rows[] = "var page_ns = {}";
  $js_rows[] = "page_ns.forms = []";
  $views_js_rows = array("page_ns.form_views = []");

  // convert ALL form and View info into Javascript, for use in the page
  foreach ($forms as $form_info)
  {
    // ignore those forms that aren't set up
    if ($form_info["is_complete"] == "no")
      continue;

    $form_id = $form_info["form_id"];
    $form_name = htmlspecialchars($form_info["form_name"]);
    $js_rows[] = "page_ns.forms.push([$form_id, \"$form_name\"])";

    $form_views = ft_get_views($form_id, "all");

    $v = array();
    foreach ($form_views["results"] as $form_view)
    {
      $view_id   = $form_view["view_id"];
      $view_name = htmlspecialchars($form_view["view_name"]);
      $v[] = "[$view_id, \"$view_name\"]";
    }
    $views = join(",", $v);

    $views_js_rows[] = "page_ns.form_views.push([$form_id,[$views]])";
  }

  $js = array_merge($js_rows, $views_js_rows);
  $js = join(";\n", $js);

  return $js;
}



/**
 * This embeds the necessary include files for the Visualization module into the head of the admin and client
 * Submission Listing page. Sadly, at the point this is executed, we don't have access to the page data (namely
 * form ID and View ID) so we can't determine whether or not we NEED to include the code.
 *
 * @param string $location
 * @param array $params
 */
function dv_include_in_head($location, $params)
{
  global $g_root_url, $LANG;

  if ($params["page"] != "admin_forms" && $params["page"] != "client_forms")
    return;

  $module_settings = ft_get_module_settings("", "data_visualization");
  if ($params["page"] == "client_forms" && $module_settings["hide_from_client_accounts"] == "yes") {
  	return;
  }

  $context = "";
  $cache_display = "block";
  if ($params["page"] == "admin_forms")
  {
  	$context = "admin_submission_listing";
  }
  else
  {
    if ($module_settings["clients_may_refresh_cache"] == "no")
    {
    	$cache_display = "none";
    }
    $context = "client_submission_listing";
  }

  $L = ft_get_module_lang_file_contents("data_visualization");
  $vis_messages = dv_get_vis_messages($L);


  echo <<< END
<link type="text/css" rel="stylesheet" href="$g_root_url/modules/data_visualization/global/css/visualizations.css">
<script src="https://www.google.com/jsapi"></script>
<script src="$g_root_url/modules/data_visualization/global/scripts/visualizations.js?v=2"></script>
<script>
$(function() {
  $(".dv_vis_tile_enlarge").live("click", dv_ns.enlarge_visualization);
  $("#dv_vis_full_nav li.back span").live("click", dv_ns.return_to_overview);
  $("#dv_vis_full_nav li.prev span").live("click", dv_ns.show_prev_visualization);
  $("#dv_vis_full_nav li.next span").live("click", dv_ns.show_next_visualization);
  dv_ns.context = "$context";
});

g.quicklinks_dialog_width = {$module_settings["quicklinks_dialog_width"]};
g.quicklinks_dialog_height = {$module_settings["quicklinks_dialog_height"]};
g.vis_tile_size = {$module_settings["quicklinks_dialog_thumb_size"]};

$vis_messages
</script>
<style type="text/css">
#dv_vis_tiles li {
  width: {$module_settings["quicklinks_dialog_thumb_size"]}px;
  height: {$module_settings["quicklinks_dialog_thumb_size"]}px;
}
#dv_vis_refresh_cache {
  display: $cache_display;
}
</style>

END;
}

/**
 * This hook gets fired any time the administrator deletes a form. It automatically deletes all assigned visualizations.
 *
 * @param array $info
 */
function dv_delete_form_hook($info)
{
	global $g_table_prefix;

  $form_id = $info["form_id"];
  if (empty($form_id) || !is_numeric($form_id))
    return;

  $form_query = mysql_query("
    SELECT vis_id
    FROM   {$g_table_prefix}module_data_visualizations
    WHERE  form_id = $form_id
  ");

  $vis_ids = array();
  while ($row = mysql_fetch_assoc($form_query))
    $vis_ids[] = $row["vis_id"];

  if (!empty($vis_ids))
  {
    $vis_id_str = implode(",", $vis_ids);
    mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_clients WHERE vis_id IN ($vis_id_str)");
    mysql_query("DELETE FROM {$g_table_prefix}module_data_visualization_cache WHERE vis_id IN ($vis_id_str)");
    mysql_query("DELETE FROM {$g_table_prefix}module_data_visualizations WHERE vis_id IN ($vis_id_str)");
  }
}


/**
 * Helper function used wherever the quicklinks dialog is being used. It outputs all required language strings in a
 * g.vis_messages namespace. The assumption is that it's being output in a <script> block and the g object has been
 * defined.
 *
 * @param array $L the contents of the Data Visualization language file. This is passed as a param because it's not
 *     defined as a global outside of the module.
 */
function dv_get_vis_messages($L)
{
	global $LANG;

  $js =<<< END

g.vis_messages = {};
g.vis_messages.word_visualizations = "{$L["word_visualizations"]}";
g.vis_messages.word_close = "{$LANG["word_close"]}";
g.vis_messages.phrase_manage_visualizations = "{$L["phrase_manage_visualizations"]}";
g.vis_messages.word_visualizations = "{$L["word_visualizations"]}";
g.vis_messages.phrase_edit_visualization = "{$L["phrase_edit_visualization"]}";
g.vis_messages.phrase_prev_arrow = "{$L["phrase_prev_arrow"]}";
g.vis_messages.phrase_next_arrow = "{$L["phrase_next_arrow"]}";
g.vis_messages.phrase_back_to_vis_list = "{$L["phrase_back_to_vis_list"]}";
g.vis_messages.phrase_last_cached_c = "{$L["phrase_last_cached_c"]}";
g.vis_messages.phrase_not_cached = "{$L["phrase_not_cached"]}";

END;

  return $js;
}