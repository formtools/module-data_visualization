<?php

use FormTools\Views;

$views = Views::getFormViews($vis_info["form_id"]);
$access_views = explode(",", $vis_info["access_views"]);

$page_vars["vis_id"] = $vis_id;
$page_vars["vis_info"] = $vis_info;
$page_vars["views"] = $views;
$page_vars["access_views"] = $access_views;
$page_vars["js_messages"] = array(
    "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete", "word_yes", "word_no"
);
$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["head_js"] =<<< EOF

$(function() {
    $("#delete_visualization").bind("click", function() {
        vis_ns.delete_visualization($vis_id);
    });
    $("input[name=access_type]").bind("click change", function() {
        var form_type = this.value;
        if (form_type == "private") {
            $("#custom_clients").show();
        } else {
            $("#custom_clients").hide();
        }
    });
    $("input[name=access_view_mapping]").bind("click change", function() {
        var form_type = this.value;
        if (form_type == "all") {
            $("#custom_views").hide();
        } else {
            $("#custom_views").show();
        }
    });
    $(".form_ids").bind("click", function() {
        var form_id = this.value;
        if (this.checked) {
            $("#f" + form_id + "_views").show();
        } else {
            $("#f" + form_id + "_views").hide();
        }
    });
    $(".view_ids").bind("click", function() {
        var view_id = this.value;
        if ($(this).hasClass("all_views")) {
            if (this.checked) {
                $(this).closest("ul").find(".view_ids").not(".all_views").attr({ checked: "", disabled: "disabled" });
            } else {
                $(this).closest("ul").find(".view_ids").not(".all_views").attr({ disabled: "" });
            }
        }
    });
    $("form").bind("submit", function() {
        ft.select_all("selected_client_ids[]");
    });
});
EOF;

$module->displayPage("templates/field_charts/edit.tpl", $page_vars);
