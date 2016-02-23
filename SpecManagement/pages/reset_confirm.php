<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_constant_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';

$specmanagement_database_api = new specmanagement_database_api();

if ( isset( $_POST['con_reset'] ) )
{
   $specmanagement_database_api->resetPlugin();
}
else
{
   print_successful_redirect( plugin_page( 'config_page', true ) );
}

print_successful_redirect( 'manage_plugin_page.php' );