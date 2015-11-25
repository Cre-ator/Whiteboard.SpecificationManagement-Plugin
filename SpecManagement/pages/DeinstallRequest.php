<?php
include SPECMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';

$sd_api = new SpecDatabase_api();

html_page_top1( plugin_lang_get( 'page_title' ) );
html_page_top2();

echo '<div align="center">';
echo '<hr size="1" width="50%" />';
echo plugin_lang_get( 'request' ) . '<br/><br/>';

echo '<table class="width50" cellspacing="1">';



//$db_api->config_resetPlugin();