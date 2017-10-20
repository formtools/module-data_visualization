<?php

require("../../global/library.php");

use FormTools\Modules;

$module = Modules::initModulePage("admin");

$module->displayPage("templates/help.tpl");
