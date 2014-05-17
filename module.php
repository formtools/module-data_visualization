<?php

/**
 * Visualization Module
 */

$MODULE["author"]          = "Encore Web Studios";
$MODULE["author_email"]    = "formtools@encorewebstudios.com";
$MODULE["author_link"]     = "http://www.encorewebstudios.com";
$MODULE["version"]         = "1.0.3";
$MODULE["date"]            = "2011-09-17";
$MODULE["is_premium"]      = "yes";
$MODULE["origin_language"] = "en_us";


// define the module navigation - the keys are keys defined in the language file. This lets
// the navigation - like everything else - be customized to the users language. The paths are always built
// relative to the module's root, so help/index.php means: /[form tools root]/modules/export_manager/help/index.php
$MODULE["nav"] = array(
  "word_visualizations"    => array('{$module_dir}/index.php', false),
  "phrase_main_settings"   => array('{$module_dir}/settings.php', false),
  "phrase_activity_charts" => array('{$module_dir}/activity_charts/settings.php', true),
  "phrase_field_charts"    => array('{$module_dir}/field_charts/settings.php', true),
  "word_help"              => array('{$module_dir}/help.php', false)
    );