<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'AccessLevel' ) );

form_security_validate( 'plugin_SpecificationManagement_config_update' );

require_once( SPECIFICATIONMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecConfig_api.php';

$db_api = new SpecDatabase_api();
$sc_api = new SpecConfig_api();

$option_addtype = gpc_get_bool( 'addtype', false );
$option_change = gpc_get_bool( 'change', false );
$option_delete = gpc_get_bool( 'deletetype', false );

if ( $option_change )
{
   $sc_api->updateValue( 'AccessLevel', ADMINISTRATOR );

   $sc_api->updateButton( 'ShowInFooter' );
   $sc_api->updateButton( 'ShowFields' );
   $sc_api->updateButton( 'ShowMenu' );
}

if ( $option_addtype )
{
   if ( !empty( $_POST['type'] ) )
   {
      $db_api->addType( $_POST['type'] );
   }
}

if ( $option_delete )
{
   if ( !empty( $_POST['types'] ) )
   {
      $db_api->deleteType( $_POST['types'] );
   }
}

form_security_purge( 'plugin_SpecificationManagement_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );