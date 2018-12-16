/**
 * Contains all JS for the administration section of the Data Visualization module.
 */
var vis_ns = {
	form_fields: {},
	view_fields: {},
	cached_activity_chart_data: null,
	cached_field_chart_data: null,
	default_view_label: null,
	delete_vis_dialog: $("<div id=\"delete_visualization_dialog\"></div>"),

	// N.B. these are also hardcoded in the CSS
	thumb_chart_height: 160,
	thumb_chart_width: 250,
	full_size_chart_height: 300,
	full_size_chart_width: 692
};


/**
 * Called when the user selects a form from one of the dropdowns in the first column. It shows
 * the appropriate View content in the second column.
 */
vis_ns.select_form = function (form_id, load_form_fields) {
	if (form_id == "") {
		$("#view_id")[0].options.length = 0;
		$("#view_id")[0].options[0] = new Option(g.messages["phrase_please_select_form"], "");
		$("#view_id").attr("disabled", "disabled");
		return false;
	} else {
		$("#view_id").attr("disabled", "");
		vis_ns.populate_view_dropdown("view_id", form_id);
	}

	// query the database for the complete list of form fields
	if (load_form_fields) {
		vis_ns.get_form_fields(form_id);
	}
	return false;
}


/**
 * Called when the user selects a View.
 */
vis_ns.select_view = function (view_id) {
	if (view_id == "") {
		$("#field_id")[0].options.length = 0;
		$("#field_id")[0].options[0] = new Option(g.messages["phrase_please_select_view"], "");
		$("#field_id").attr("disabled", "disabled");
		return false;
	} else {
		vis_ns.get_view_fields(view_id);
	}
	return false;
}


/**
 * Populates a dropdown element with a list of Views including a "Please Select" default
 * option.
 */
vis_ns.populate_view_dropdown = function (element_id, form_id) {
	var form_index = null;
	for (var i = 0; i < page_ns.form_views.length; i++) {
		if (form_id == page_ns.form_views[i][0]) {
			form_index = i;
		}
	}
	var default_label = g.messages["phrase_please_select"];
	if (vis_ns.default_view_label != null) {
		default_label = vis_ns.default_view_label;
	}
	$("#" + element_id)[0].options.length = 0;
	$("#" + element_id)[0].options[0] = new Option(default_label, "");

	for (var i = 0; i < page_ns.form_views[form_index][1].length; i++) {
		var view_id = page_ns.form_views[form_index][1][i][0];
		var view_name = page_ns.form_views[form_index][1][i][1];
		$("#" + element_id)[0].options[i + 1] = new Option(view_name, view_id);
	}
}


/**
 * This Ajax function queries the database for a list of form fields.
 */
vis_ns.get_form_fields = function (form_id) {
	if (form_id == "") {
		return false;
	}
	if (typeof vis_ns.form_fields["form_" + form_id] != 'undefined') {
		var form_info = vis_ns.form_fields["form_" + form_id];
		if (!form_info.is_loaded) {
			return;
		}
		vis_ns.populate_field_dropdowns(form_id);
	} else {
		// make a note of the fact that we're loading the fields for this form
		vis_ns.form_fields["form_" + form_id] = { is_loaded: false };

		$.ajax({
			url: g.root_url + "/modules/data_visualization/code/actions.php",
			data: {
				action: "get_form_fields",
				form_id: form_id
			},
			type: "GET",
			dataType: "json",
			success: vis_ns.process_json_field_data,
			error: ft.error_handler
		});
	}
}


/**
 * This Ajax function queries the database for a list of form fields in a View.
 */
vis_ns.get_view_fields = function (view_id) {
	if (view_id == "") {
		return false;
	}
	if (typeof vis_ns.view_fields["view_" + view_id] != 'undefined') {
		var view_info = vis_ns.form_fields["view_" + view_id];
		if (!view_info.is_loaded) {
			return;
		}
		vis_ns.populate_view_field_dropdown(view_id);
	} else {
		// make a note of the fact that we're loading the fields for this form
		vis_ns.view_fields["view_" + view_id] = { is_loaded: false };

		$.ajax({
			url: g.root_url + "/modules/data_visualization/code/actions.php",
			data: {
				action: "get_view_fields",
				view_id: view_id
			},
			type: "GET",
			dataType: "json",
			success: vis_ns.process_json_view_field_data,
			error: ft.error_handler
		});
	}
}


/**
 * This function is passed the result of the database query for the form fields. It populates vis_ns.form_fields
 * with the field info.
 */
vis_ns.process_json_field_data = function (data) {
	var form_id = data.form_id;

	var form_info = vis_ns.form_fields["form_" + form_id];
	form_info.fields = data.fields;
	form_info.is_loaded = true;
	vis_ns.form_fields["form_" + form_id] = form_info;

	// now, if the form is still selected, update the field list
	var selected_form_id = $("#form_id").val();
	$("#loading_icon").hide();

	if (selected_form_id == form_id) {
		vis_ns.populate_field_dropdowns(form_id);
	}
}


/**
 * This function is passed the result of the database query for the form fields. It populates vis_ns.form_fields
 * with the field info.
 */
vis_ns.process_json_view_field_data = function (data) {
	var view_id = data.view_id;

	var view_info = vis_ns.view_fields["view_" + view_id];
	view_info.fields = data.fields;
	view_info.is_loaded = true;
	vis_ns.view_fields["view_" + view_id] = view_info;

	// now, if the form is still selected, update the field list
	var selected_view_id = $("#view_id").val();
	$("#loading_icon").hide();

	if (selected_view_id == view_id) {
		vis_ns.populate_view_field_dropdown(view_id);
	}
}

vis_ns.populate_field_dropdowns = function (form_id) {
	var form_info = vis_ns.form_fields["form_" + form_id];
	var fields = form_info.fields;

	var options = "<option value=\"\">" + g.messages["phrase_please_select"] + "</option>";
	for (var i = 0; i < fields.length; i++) {
		var field_id = fields[i][0];
		var field_title = fields[i][1];
		options += "<option value=\"" + field_id + "\">" + field_title + "</option>";
	}
	$("#field_id").html(options).removeAttr("disabled");
}


vis_ns.populate_view_field_dropdown = function (view_id) {
	var view_info = vis_ns.view_fields["view_" + view_id];
	var fields = view_info.fields;

	var options = "<option value=\"\">" + g.messages["phrase_please_select"] + "</option>";
	for (var i = 0; i < fields.length; i++) {
		var field_id = fields[i][0];
		var field_title = fields[i][1];
		options += "<option value=\"" + field_id + "\">" + field_title + "</option>";
	}
	$("#field_id").html(options).removeAttr("disabled");
}


/**
 * Used on the Add and Edit Activity Chart pages. It's used to construct the activity chart on the
 * fly, based on whatever the user is selecting. On the Edit Activity Chart page, the option to choose
 * the form and View appears on a seperate tab, so we know whether or not
 */
vis_ns.update_activity_chart_data = function () {
	var form_id = $("#form_id").val();
	var view_id = $("#view_id").val(); // optional. This may be empty

	if (form_id) {

		// in certain scenarios we don't have an active data set to use. Check for those conditions.
		var use_dud_data = ($("#page_type").val() == "edit" && $("#has_submissions_in_view").val() == "no");
		if (use_dud_data) {
			vis_ns.redraw_activity_chart();
		} else {
			$.ajax({
				url: g.root_url + "/modules/data_visualization/code/actions.php",
				type: "POST",
				dataType: "json",
				data: {
					action: "get_activity_chart_data",
					form_id: form_id,
					view_id: view_id,
					date_range: $("#date_range").val(),
					submission_count_group: $("input[name=submission_count_group]:checked").val()
				},
				success: function (json) {
					vis_ns.cached_activity_chart_data = json;
					vis_ns.redraw_activity_chart();
				}
			});
		}
	}
}


/**
 * This redraws the activity charts on the page (thumbnail + full size) based on whatever information is
 * currently already available in the page.
 */
vis_ns.redraw_activity_chart = function () {
	if (typeof google == "undefined") {
		return;
	}
	var title = $("#vis_name").val();
	var colour = $("#colour").val();
	var chart_type = $("input[name=chart_type]:checked").val();
	var submission_count_group = $("input[name=submission_count_group]:checked").val();
	var line_width = $("#line_width").val();

	var data = new google.visualization.DataTable();
	data.addColumn('string', "Day");
	data.addColumn('number', 'Submission Count');

	if (vis_ns.cached_activity_chart_data == null) {
		data.addRows(5);
		var use_dud_data = ($("#page_type").val() == "edit" && $("#has_submissions_in_view").val() == "no");
		if (use_dud_data) {
			switch (submission_count_group) {
				case "month":
					data.setValue(0, 0, "Jan 2011");
					data.setValue(0, 1, 10);
					data.setValue(1, 0, "Feb 2011");
					data.setValue(1, 1, 14);
					data.setValue(2, 0, "Mar 2011");
					data.setValue(2, 1, 20);
					data.setValue(3, 0, "Apr 2011");
					data.setValue(3, 1, 2);
					data.setValue(4, 0, "May 2011");
					data.setValue(4, 1, 4);
					break;
				case "day":
					data.setValue(0, 0, "Jan 1");
					data.setValue(0, 1, 10);
					data.setValue(1, 0, "Jan 2");
					data.setValue(1, 1, 14);
					data.setValue(2, 0, "Jan 3");
					data.setValue(2, 1, 20);
					data.setValue(3, 0, "Jan 4");
					data.setValue(3, 1, 2);
					data.setValue(4, 0, "Jan 5");
					data.setValue(4, 1, 4);
					break;
			}
		}
	} else {
		json = vis_ns.cached_activity_chart_data;
		var data = new google.visualization.DataTable();
		data.addColumn('string', json.period);
		data.addColumn('number', 'Submissions');
		if (json.data.length) {
			data.addRows(json.data.length);
			for (var i = 0, j = json.data.length; i < j; i++) {
				data.setValue(i, 0, json.data[i].label);
				data.setValue(i, 1, json.data[i].data);
			}
		}
	}

	var thumb = document.getElementById("thumb_chart");
	var full_size = document.getElementById("full_size_chart");

	switch (chart_type) {
		case "line_chart":
			var thumb_chart = new google.visualization.LineChart(thumb);
			var full_size_chart = new google.visualization.LineChart(full_size);
			break;
		case "area_chart":
			var thumb_chart = new google.visualization.AreaChart(thumb);
			var full_size_chart = new google.visualization.AreaChart(full_size);
			break;
		case "column_chart":
			var thumb_chart = new google.visualization.ColumnChart(thumb);
			var full_size_chart = new google.visualization.ColumnChart(full_size);
			break;
	}

	if (thumb_chart) {
		thumb_chart.draw(data, {
			width: vis_ns.thumb_chart_width,
			height: vis_ns.thumb_chart_height,
			title: title,
			legend: "none",
			colors: [colour],
			lineWidth: line_width
		});
		full_size_chart.draw(data, {
			width: vis_ns.full_size_chart_width,
			height: vis_ns.full_size_chart_height,
			title: title,
			legend: "none",
			colors: [colour],
			lineWidth: line_width
		});
	}
}


/**
 * Used on the Add and Edit Field Chart pages. It's used to construct the activity chart on the
 * fly, based on whatever the user is selecting.
 */
vis_ns.update_field_chart_data = function () {
	var view_id = $("#view_id").val();
	var form_id = $("#form_id").val();
	var field_id = $("#field_id").val();
	var date_range = $("#date_range").val();
	var ignore_empty_fields = $("input[name=field_chart_ignore_empty_fields]:checked").val();

	if (view_id && form_id && field_id) {
		$.ajax({
			url: g.root_url + "/modules/data_visualization/code/actions.php",
			type: "POST",
			dataType: "json",
			data: {
				cache: false,
				action: "get_field_chart_data",
				date_range: date_range,
				form_id: form_id,
				view_id: view_id,
				field_id: field_id,
				ignore_empty_fields: ignore_empty_fields
			},
			success: function (json) {
				vis_ns.cached_field_chart_data = json;
				vis_ns.redraw_field_chart();
			}
		});
	}
}


vis_ns.redraw_field_chart = function () {
	if (typeof google == "undefined") {
		return;
	}
	var title = $("#vis_name").val();
	var chart_type = $("input[name=chart_type]:checked").val();

	var data = new google.visualization.DataTable();
	data.addColumn('string', "");
	data.addColumn('number', 'Submission Count');

	if (vis_ns.cached_field_chart_data == null) {
		data.addRows(5);
	} else {
		json = vis_ns.cached_field_chart_data;
		if (json.data.length) {
			data.addRows(json.data.length);

			for (var i = 0, j = json.data.length; i < j; i++) {
				var label = (json.data[i].field_value === null) ? "" : json.data[i].field_value.toString();
				data.setValue(i, 0, label);
				data.setValue(i, 1, json.data[i].count);
			}
		}
	}

	var thumb_chart_el = document.getElementById("thumb_chart");
	var full_size_chart_el = document.getElementById("full_size_chart");
	switch (chart_type) {
		case "pie_chart":
			var thumb_chart = new google.visualization.PieChart(thumb_chart_el);
			var full_size_chart = new google.visualization.PieChart(full_size_chart_el);
			break;
		case "bar_chart":
			var thumb_chart = new google.visualization.BarChart(thumb_chart_el);
			var full_size_chart = new google.visualization.BarChart(full_size_chart_el);
			break;
		case "column_chart":
			var thumb_chart = new google.visualization.ColumnChart(thumb_chart_el);
			var full_size_chart = new google.visualization.ColumnChart(full_size_chart_el);
			break;
	}

	if (thumb_chart) {
		var thumb_settings = {
			width: vis_ns.thumb_chart_width,
			height: vis_ns.thumb_chart_height,
			title: title,
			legend: 'none'
		};
		var full_size_settings = {
			width: vis_ns.full_size_chart_width,
			height: vis_ns.full_size_chart_height,
			title: title,
			legend: 'none'
		};

		if (chart_type == "pie_chart") {
			var pie_chart_format = $("input[name=pie_chart_format]:checked").val();
			var include_legend_quicklinks = $("input[name=include_legend_quicklinks]:checked").val();
			var include_legend_full_size = $("input[name=include_legend_full_size]:checked").val();

			thumb_settings.is3D = (pie_chart_format == "3D") ? true : false;
			thumb_settings.legend = (include_legend_quicklinks == "yes") ? "right" : "none";

			full_size_settings.is3D = (pie_chart_format == "3D") ? true : false;
			full_size_settings.legend = (include_legend_full_size == "yes") ? "right" : "none";
		} else {
			var colour = $("#colour").val();
			thumb_settings.colors = [colour];
			full_size_settings.colors = [colour];
		}

		thumb_chart.draw(data, thumb_settings);
		full_size_chart.draw(data, full_size_settings);
	}
}


/**
 * This is used in the admin section on the Edit Visualization and Visualization List pages.
 */
vis_ns.delete_visualization = function (vis_id) {
	var redirect_page = g.root_url + "/modules/data_visualization/index.php";

	ft.create_dialog({
		dialog: vis_ns.delete_vis_dialog,
		title: g.messages["phrase_delete_visualization"],
		content: g.messages["confirm_delete_visualization"],
		popup_type: "warning",
		buttons: [{
			text: g.messages["word_yes"],
			click: function () {
				window.location = redirect_page + "?delete=" + vis_id;
			}
		},
			{
				text: g.messages["word_no"],
				click: function () {
					$(this).dialog("close");
				}
			}]
	});
}


vis_ns.init_create_page_and_menu_item_dialog = function () {
	$("#add_to_menu").bind("click", function () {
		var vis_id = $("#vis_id").val();
		ft.create_dialog({
			dialog: $("#add_to_menu_dialog"),
			title: "Create Page and Add to Menu",
			width: 600,
			buttons: [
				{
					text: "Create Page",
					click: function () {
						ft.dialog_activity_icon($("#add_to_menu_dialog"), "show");
						$.ajax({
							url: g.root_url + "/modules/data_visualization/code/actions.php",
							data: {
								action: "create_page_and_menu_item",
								vis_id: $("#vis_id").val(),
								menu_id: $("#menu_id").val(),
								page_title: $("#page_title").val(),
								menu_position: $("#menu_position").val(),
								is_submenu: $("input[name=is_submenu]:checked").val()
							},
							type: "POST",
							dataType: "json",
							success: function (response) {
								ft.dialog_activity_icon($("#add_to_menu_dialog"), "hide");
								if (response.success == 1) {
									if (response.menu_type == "admin") {
										window.location = "edit.php?page=advanced&vis_id=" + vis_id + "&msg=page_created";
									} else {
										ft.display_message("ft_message", 1, "The menu has been updated with the new page.");
										$("#add_to_menu_dialog").dialog("close");
									}
								}
							},
							error: function (a, b, c) {

							}
						});
					}
				},
				{
					text: g.messages["word_cancel"],
					click: function () {
						$(this).dialog("close");
					}
				}
			]
		});
	});
}

vis_ns.select_menu = function (menu_id) {
	if (menu_id == "") {
		$("#position_div").html("Please select menu");
	} else {
		ft.dialog_activity_icon($("#add_to_menu_dialog"), "show");
		$.ajax({
			url: g.root_url + "/modules/data_visualization/code/actions.php",
			data: {
				action: "get_menu",
				menu_id: menu_id
			},
			type: "POST",
			dataType: "html",
			success: function (html) {
				ft.dialog_activity_icon($("#add_to_menu_dialog"), "hide");
				$("#position_div").html(html);
			},
			error: function (a, b, c) {

			}
		});
	}
}
