/**
 * Code for	 the Data Visualizations dialog window. This is used on the Submission Listing pages for the admin and
 * client, but also in the Admin section to provide a simple way to view all visualizations in a result set. It
 * contains all the juicy javascript to retrieve and display the visualization data, handle the dialog window
 * creation, etc.
 *
 * Prerequisites:
 * - the google jsapi has been loaded
 * - a few language strings have been loaded into the javascript g.vis_messages namespace.
 *
 * Take a look at the calling pages to see what info is being supplied.
 */

if (typeof google != "undefined") {
  google.load("visualization", "1", {packages:["corechart"]});
}

var dv_ns = {};
dv_ns.visualization_dialog = $("<div id=\"dv_visualization_dialog\">"
                             + "<ul id=\"dv_vis_tiles\"></ul>"
                             + "<div id=\"dv_vis_full\">"
                               + "<div id=\"dv_vis_full_chart\"></div>"
                               + "<div id=\"dv_vis_bottom_row\">"
                                 + "<div id=\"dv_vis_cache_info\">Last cached: <span></span></div>" // <img src=\"" + g.root_url + "/global/images/loading_small.gif\" />
                                 + "<div id=\"dv_vis_full_nav\"></div>"
                               + "</div>"
                             + "</div>"
                          + "</div>");
dv_ns.preload_loading_icon = new Image(32, 32);
dv_ns.preload_loading_icon.src = g.root_url + "/global/images/loading.gif";
dv_ns.context = ""; // manage_visualizations, admin_submission_listing, client_submission_listing - set on page load

// keeps track of the current state of the dialog
dv_ns.current_page    = "details"; // "overview" (all visualizations), or "details" (full screen single vis)
dv_ns.selected_vis_id = null;
dv_ns.vis_data = {}; // an object with "visX" keys, containing the JSON data returned from the server



/**
 * Called when the user clicks on the visualizations quicklink icon. It only appears if there is at least one
 * visualization to display for the current form View.
 */
dv_ns.show_visualizations_dialog = function() {
  var buttons = [{ text: g.vis_messages.word_close, click: function() { $("#dv_visualization_dialog").dialog("close"); } }];;
  if (dv_ns.context == "admin_submission_listing") {
    buttons = [
      {
        text: g.vis_messages.phrase_manage_visualizations,
        click: function() {
          window.location = g.root_url + "/modules/data_visualization/index.php?form_id=" + ms.form_id + "&view_id=" + ms.view_id +
            "&source=admin_submission_listing";
        }
      },
      {
        text: g.vis_messages.word_close,
        click: function() { $("#dv_visualization_dialog").dialog("close"); }
      }
    ];
  }

  ft.create_dialog({
    title:       g.vis_messages.word_visualizations,
    dialog:      dv_ns.visualization_dialog,
    width:       g.quicklinks_dialog_width,
    height:      g.quicklinks_dialog_height,
    open:        dv_ns.open_visualization_dialog,
    resize_stop: dv_ns.resize_dialog,
    buttons:     buttons
  });

  return false;
}


/**
 * This is called when the dialog window has just opened. It figures out what to start requesting from the server
 * and how to display it. If there's only a single visualization, we display it full size in the window. If there is
 * more than one, we display them in clickable tiles (which expands them to the full size and shows nav to toggle between
 * each ).
 */
dv_ns.open_visualization_dialog = function() {
  var num_visualizations = g.vis_ids.length;

  // this should never happen
  if (num_visualizations == 0) {
    $(g.visualization_dialog).dialog("close");
    return;
  }

  // if there's more than one visualization, create some placeholder tiles for them. They'll be
  // loaded once the data is returned from the server
  if (num_visualizations > 1) {
    dv_ns.current_page = "overview";

    var tiles_html = "";
    for (var i=0; i<num_visualizations; i++) {
      tiles_html += "<li id=\"dv_vis_tile_" + g.vis_ids[i] + "\">"
                    + "<div class=\"dv_vis_tile_heading\">"
                      + "<div class=\"dv_vis_tile_enlarge hidden\"></div>"
                      + "<div class=\"dv_vis_tile_title\"></div>"
                    + "</div>"
                    + "<div class=\"dv_vis_tile_chart\"></div>"
                  + "</li>";
    }
    $("#dv_vis_tiles").html(tiles_html);
  }

  // now create separate Ajax requests for each visualization
  for (var i=0; i<num_visualizations; i++) {
    $.ajax({
      url:      g.root_url + "/modules/data_visualization/global/code/actions.php",
      type:     "POST",
      dataType: "json",
      data: {
        action: "get_visualization",
        vis_id: g.vis_ids[i]
      },
      success: dv_ns.get_visualization_response,
      error: function(a, b, c) {

      }
    });
  }
}


/**
 * Called when the request for a visualization has returned from the server. This displays an individual
 * visualization.
 */
dv_ns.get_visualization_response = function(json) {
  var vis_id   = json.vis_id;
  var vis_name = json.vis_name;
  var vis_type = json.vis_type;

  if (g.vis_ids.length == 1) {
    var width  = $("#dv_visualization_dialog").dialog("option", "width") - 20;
    var height = $('#dv_visualization_dialog').closest('.ui-dialog').height() - 130;
    dv_ns.draw_activity_chart(json, false, $("#dv_visualization_dialog")[0], width, height);
  } else {
    var target_el = $("#dv_vis_tile_" + vis_id + " .dv_vis_tile_chart")[0];
    $("#dv_vis_tile_" + vis_id + " .dv_vis_tile_title").html(vis_name);
    $("#dv_vis_tile_" + vis_id + " .dv_vis_tile_enlarge").removeClass("hidden");

    if (vis_type == "activity") {
      dv_ns.draw_activity_chart(json, false, target_el, g.vis_tile_size, g.vis_tile_size - 20);
    } else if (vis_type == "field") {
      dv_ns.draw_field_chart(json, false, target_el, g.vis_tile_size, g.vis_tile_size - 20);
    }
  }

  dv_ns.vis_data["vis_" + json.vis_id] = json;
}


dv_ns.draw_activity_chart = function(json, show_title, target_el, width, height) {
  var vis_id     = json.vis_id;
  var vis_colour = json.vis_colour;
  var line_width = json.line_width;
  var chart_type = json.chart_type;

  var data = new google.visualization.DataTable();
  data.addColumn("string", json.period);
  data.addColumn("number", "Submissions");

  var num_rows = json.data.length;
  if (typeof json.data.length == 'undefined') {
    num_rows = 0;
  }

  data.addRows(num_rows);
  for (var i=0, j=num_rows; i<j; i++) {
    data.setValue(i, 0, json.data[i].label);
    data.setValue(i, 1, json.data[i].data);
  }

  switch (json.chart_type){
    case "line_chart":
      var chart = new google.visualization.LineChart(target_el);
      break;
    case "area_chart":
      var chart = new google.visualization.AreaChart(target_el);
      break;
    case "column_chart":
      var chart = new google.visualization.ColumnChart(target_el);
      break;
  }

  var settings = {
    width:     width,
    height:    height,
    legend:    "none",
    colors:    [vis_colour],
    lineWidth: line_width
  };

  if (show_title) {
    settings.title = json.vis_name;
  }

  chart.draw(data, settings);
}


dv_ns.draw_field_chart = function(json, is_full_size, target_el, width, height) {
  var vis_id     = json.vis_id;
  var chart_type = json.chart_type;

  var data = new google.visualization.DataTable();
  data.addColumn("string", json.period);
  data.addColumn("number", "Submissions");
  data.addRows(json.data.length);
  for (var i=0, j=json.data.length; i<j; i++) {
    data.setValue(i, 0, json.data[i].label);
    data.setValue(i, 1, json.data[i].data);
  }

  switch (json.chart_type){
    case "pie_chart":
      var chart = new google.visualization.PieChart(target_el);
      break;
    case "bar_chart":
      var chart = new google.visualization.BarChart(target_el);
      break;
    case "column_chart":
      var chart = new google.visualization.ColumnChart(target_el);
      break;
  }

  var settings = {
    width:     width,
    height:    height,
    legend:    "none"
  };

  if (json.chart_type == "pie_chart") {
    settings.is3D = (json.pie_chart_format == "3D") ? true : false;
    if (is_full_size) {
      settings.legend = (json.include_legend_full_size == "yes") ? "right" : "none";
    } else {
      settings.legend = (json.include_legend_quicklinks == "yes") ? "right" : "none";
    }
  }
  if (is_full_size) {
    settings.title = json.vis_name;
  }

  chart.draw(data, settings);
}


dv_ns.enlarge_visualization = function(e) {
  var tile = $(e.target).closest("li");
  var vis_id = tile.attr("id").replace(/dv_vis_tile_/, "");
  var data = dv_ns.vis_data["vis_" + vis_id];
  dv_ns.selected_vis_id = parseInt(vis_id, 10);
  $("#dv_vis_tiles").hide();
  dv_ns.redraw_full_screen_visualization(vis_id);

  // create the navigation
  $("#dv_vis_full_nav").html(dv_ns.create_nav(vis_id));
}


dv_ns.resize_dialog = function(e, ui) {
  if (dv_ns.current_page == "details") {
    dv_ns.redraw_full_screen_visualization(dv_ns.selected_vis_id);
  }
}


dv_ns.redraw_full_screen_visualization = function(vis_id) {
  dv_ns.current_page = "details";
  var width  = $("#dv_visualization_dialog").dialog("option", "width") - 20;
  var height = $('#dv_visualization_dialog').closest('.ui-dialog').height() - 130;

  $("#dv_vis_full, #dv_vis_bottom_row").show();

  var vis_type = dv_ns.vis_data["vis_" + vis_id].vis_type;
  if (vis_type == "activity") {
    dv_ns.draw_activity_chart(dv_ns.vis_data["vis_" + vis_id], true, $("#dv_vis_full_chart")[0], width, height);
  } else if (vis_type == "field") {
    dv_ns.draw_field_chart(dv_ns.vis_data["vis_" + vis_id], true, $("#dv_vis_full_chart")[0], width, height);
  }

  var title = g.vis_messages.word_visualizations + " <span class=\"light_grey\">&raquo;</span> <span class=\"vis_name\">"
      + dv_ns.vis_data["vis_" + vis_id].vis_name + "</span>";

  console.log(dv_ns.vis_data["vis_" + vis_id]);

  $("#dv_vis_cache_info span").html(dv_ns.vis_data["vis_" + vis_id].last_cached);

  var buttons = [{ text: g.vis_messages.word_close, click: function() { $("#dv_visualization_dialog").dialog("close"); } }];
  if (dv_ns.context == "admin_submission_listing" || dv_ns.context == "manage_visualizations") {
    buttons = [
      {
	    text: g.vis_messages.phrase_edit_visualization,
	    click: function() {
	      var folder = (vis_type == "activity") ? "activity_charts" : "field_charts";
	      window.location = g.root_url + "/modules/data_visualization/" + folder + "/edit.php?page=appearance&vis_id=" + vis_id;
	    }
      },
	  {
	    text: g.vis_messages.word_close,
	    click: function() { $("#dv_visualization_dialog").dialog("close"); }
	  }
	];
  }

  $("#dv_visualization_dialog").dialog({ buttons: buttons });
}


dv_ns.create_nav = function(vis_id) {
  var prev_class = "none";
  var next_class = "none";

  if (g.vis_ids.length > 1) {
    if (vis_id != g.vis_ids[0]) {
      prev_class = "";
    }
    if (vis_id != g.vis_ids[g.vis_ids.length-1]) {
      next_class = "";
    }
  }

  var html = "<ul>"
             + "<li class=\"prev\"><span class=\"" + prev_class + "\">&laquo prev</span></li>"
             + "<li class=\"back\"><span>back to visualization list</span></li>"
             + "<li class=\"next\"><span class=\"" + next_class + "\">next &raquo;</span></li>"
           + "</ul>";

  return html;
}


dv_ns.return_to_overview = function() {
  $("#dv_vis_bottom_row, #dv_vis_full").hide();
  $("#dv_vis_tiles").show();
  dv_ns.current_page = "overview";
  dv_ns.redraw_visualization_list();

  var buttons = [{ text: g.vis_messages.word_close, click: function() { $("#dv_visualization_dialog").dialog("close"); } }];;
  if (dv_ns.context == "admin_submission_listing") {
    buttons = [
      {
        text: g.vis_messages.phrase_manage_visualizations,
        click: function() {
          window.location = g.root_url + "/modules/data_visualization/index.php?form_id=" + ms.form_id + "&view_id=" + ms.view_id;
        }
      },
      {
        text: g.vis_messages.word_close,
        click: function() { $("#dv_visualization_dialog").dialog("close"); }
      }
    ];
  }

  $("#dv_visualization_dialog").dialog({ buttons: buttons });
}


dv_ns.show_prev_visualization = function() {
  if (g.vis_ids[0] == dv_ns.selected_vis_id) {
    return;
  }
  var index = $.inArray(dv_ns.selected_vis_id, g.vis_ids);
  var new_vis_id = g.vis_ids[index-1];
  dv_ns.selected_vis_id = new_vis_id;
  $("#dv_vis_full_nav").html(dv_ns.create_nav(new_vis_id));
  dv_ns.redraw_full_screen_visualization(dv_ns.selected_vis_id);
}


dv_ns.show_next_visualization = function() {
  if (g.vis_ids[g.vis_ids.length-1] == dv_ns.selected_vis_id) {
    return;
  }
  var index = $.inArray(dv_ns.selected_vis_id, g.vis_ids);
  var new_vis_id = g.vis_ids[index+1];
  dv_ns.selected_vis_id = new_vis_id;
  $("#dv_vis_full_nav").html(dv_ns.create_nav(new_vis_id));
  dv_ns.redraw_full_screen_visualization(new_vis_id);
}


/**
 * Called anytime the user returns to the visualization list. It redraws any of the visualizations
 * already loaded into memory. This is because sometimes they get screwed up when toggling the hide/show.
 */
dv_ns.redraw_visualization_list = function() {
  return;
  for (var i=0; i<g.vis_ids.length; i++) {
    if (!dv_ns.vis_data["vis_" + g.vis_ids[i]]) {
      continue;
    }
    dv_ns.get_visualization_response(dv_ns.vis_data["vis_" + g.vis_ids[i]]);
  }
}

