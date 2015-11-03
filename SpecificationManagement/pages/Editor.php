<?php
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecMenu_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecPrint_api.php';

$sm_api = new SpecMenu_api();
$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();

$types = $sd_api->getTypes();
$docType = null;

if ( !empty( $_POST['types'] ) )
{
   $docType = $_POST['types'];
}

html_page_top1( plugin_lang_get( 'page_title' ) );
html_page_top2();

$sm_api->printWhiteboardMenu();
$sm_api->printPluginMenu();


html_page_bottom1();