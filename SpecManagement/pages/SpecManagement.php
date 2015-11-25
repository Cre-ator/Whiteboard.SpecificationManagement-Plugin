<?php
include SPECMANAGEMENT_CORE_URI . 'SpecMenu_api.php';

$sm_api = new SpecMenu_api();

html_page_top1( plugin_lang_get( 'page_title' ) );
html_page_top2();

$sm_api->printWhiteboardMenu();
$sm_api->printPluginMenu();

html_page_bottom1();