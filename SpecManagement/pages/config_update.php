<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'AccessLevel' ) );

form_security_validate( 'plugin_SpecManagement_config_update' );

require_once( SPECMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'config_api.php';

$database_api = new database_api();
$config_api = new config_api();

$option_change = gpc_get_bool( 'change', false );
$option_reset = gpc_get_bool( 'reset', false );
$option_addtype = gpc_get_bool( 'addtype', false );
$option_deltype = gpc_get_bool( 'deletetype', false );
$option_changetype = gpc_get_bool( 'changetype', false );

/**
 * Submit configuration changes
 */
if ( $option_change )
{
   $config_api->updateValue( 'AccessLevel', ADMINISTRATOR );
   $config_api->updateValue( 'ReadAccessLevel', REPORTER );
   $config_api->updateValue( 'WriteAccessLevel', DEVELOPER );

   $config_api->updateButton( 'ShowInFooter' );
   $config_api->updateButton( 'ShowFields' );
   $config_api->updateButton( 'ShowMenu' );
}

/**
 * Submit configuration reset
 */
if ( $option_reset )
{
   print_successful_redirect( plugin_page( 'reset_ensure', true ) );
}

/**
 * Add a document type
 */
if ( $option_addtype )
{
   if ( !empty( $_POST['type'] ) )
   {
      $database_api->addType( $_POST['type'] );
   }
}

/**
 * Delete a document type
 */
if ( $option_deltype )
{
   $type_string = $_POST['types'];
   $type_id = $database_api->getTypeId( $type_string );

   /*
    * Just delete a type if it is not used!
    */
   if ( !$database_api->checkTypeIsUsed( $type_id ) )
   {
      $database_api->deleteType( $type_string );
   }
}

/**
 * Change a document type
 */
if ( $option_changetype )
{
   if ( !empty( $_POST['types'] ) && !empty( $_POST['newtype'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $database_api->getTypeId( $type_string );
      $new_type_string = $_POST['newtype'];

      $database_api->updateType( $type_id, $new_type_string );
   }
}

form_security_purge( 'plugin_SpecManagement_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );