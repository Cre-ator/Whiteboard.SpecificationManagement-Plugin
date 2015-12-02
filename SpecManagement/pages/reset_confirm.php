<?php
require_once( SPECMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECMANAGEMENT_CORE_URI . 'database_api.php';

$database_api = new database_api();

if ( !empty( $_POST['con_reset'] ) )
{
   $database_api->resetPlugin();
}
else
{
   print_successful_redirect( plugin_page( 'config_page', true ) );
}

print_successful_redirect( 'manage_plugin_page.php' );