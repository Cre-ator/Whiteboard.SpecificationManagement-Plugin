<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'AccessLevel' ) );

form_security_validate( 'plugin_SpecManagement_config_update' );

require_once( SPECMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECMANAGEMENT_CORE_URI . 'SpecConfig_api.php';

$db_api = new SpecDatabase_api();
$sc_api = new SpecConfig_api();

$option_change = gpc_get_bool( 'change', false );
$option_reset = gpc_get_bool( 'reset', false );
$option_addtype = gpc_get_bool( 'addtype', false );
$option_deltype = gpc_get_bool( 'deletetype', false );

if ( $option_change )
{
   $sc_api->updateValue( 'AccessLevel', ADMINISTRATOR );
   $sc_api->updateValue( 'ReadAccessLevel', REPORTER );
   $sc_api->updateValue( 'WriteAccessLevel', DEVELOPER );

   $sc_api->updateButton( 'ShowInFooter' );
   $sc_api->updateButton( 'ShowFields' );
   $sc_api->updateButton( 'ShowMenu' );
   $sc_api->updateButton( 'ShowDuration' );
}

if ( $option_reset )
{
   print_successful_redirect( plugin_page( 'reset_ensure', true ) );
}

if ( $option_addtype )
{
   if ( !empty( $_POST['type'] ) )
   {
      $db_api->addType( $_POST['type'] );
   }
}

if ( $option_deltype )
{
   $db_api->deleteType( $_POST['types'] );
}

form_security_purge( 'plugin_SpecManagement_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );