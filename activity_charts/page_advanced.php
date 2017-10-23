<?php

use FormTools\Core;
use FormTools\Modules;

$root_url = Core::getRootUrl();
$pages_module_available = Modules::checkModuleEnabled("pages");

$success = true;
$message = "";
if (isset($_GET["msg"]) && $_GET["msg"] == "page_created") {
	$success = true;
	$message = $L["notify_page_created_and_assigned"];
}

$page_vars["pages_module_available"] = $pages_module_available;
$page_vars["vis_id"] = $vis_id;
$page_vars["vis_info"] = $vis_info;
$page_vars["js_messages"] = array(
    "phrase_please_select", "phrase_please_select_form", "word_edit", "word_delete",
    "word_cancel", "word_yes", "word_no"
);
$page_vars["css_files"] = array(
    "$root_url/global/codemirror/lib/codemirror.css",
);
$page_vars["js_files"] = array(
    "$root_url/global/codemirror/lib/codemirror.js",
    "$root_url/global/codemirror/mode/xml/xml.js",
    "$root_url/global/codemirror/mode/smarty/smarty.js",
    "$root_url/global/codemirror/mode/php/php.js",
    "$root_url/global/codemirror/mode/htmlmixed/htmlmixed.js",
    "$root_url/global/codemirror/mode/css/css.js",
    "$root_url/global/codemirror/mode/javascript/javascript.js",
    "$root_url/global/codemirror/mode/clike/clike.js"
);

$page_vars["module_js_messages"] = array("phrase_delete_visualization", "confirm_delete_visualization");
$page_vars["head_js"] =<<< END
$(function() {
    vis_ns.init_create_page_and_menu_item_dialog();
    $("#menu_id").bind("change", function() {
        vis_ns.select_menu(this.value);
    });
    $("#delete_visualization").bind("click", function() {
        vis_ns.delete_visualization($vis_id);
    });
});
END;

$module->displayPage("templates/activity_charts/edit.tpl", $page_vars);
