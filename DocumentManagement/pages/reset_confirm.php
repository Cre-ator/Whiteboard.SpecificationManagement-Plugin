<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'specmanagement_constant_api.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'specmanagement_database_api.php' );

$specmanagement_database_api = new specmanagement_database_api();

if ( isset( $_POST['con_reset'] ) )
{
   $specmanagement_database_api->reset_plugin();
}
else
{
   print_successful_redirect( plugin_page( 'config_page', true ) );
}

print_successful_redirect( 'manage_plugin_page.php' );