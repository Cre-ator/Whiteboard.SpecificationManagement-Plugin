<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'AccessLevel' ) );
form_security_validate( 'plugin_SpecManagement_config_update' );

require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_constant_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_config_api.php';

$specmanagement_database_api = new specmanagement_database_api();
$specmanagement_config_api = new specmanagement_config_api();

$option_change = gpc_get_bool( 'change', false );
$option_reset = gpc_get_bool( 'reset', false );
$option_addtype = gpc_get_bool( 'addtype', false );
$option_deltype = gpc_get_bool( 'deletetype', false );
$option_changetype = gpc_get_bool( 'changetype', false );
$option_manage_doc_types = gpc_get_bool( 'manage_doc_types', false );

/**
 * Submit configuration changes
 */
if ( $option_change )
{
   $specmanagement_config_api->updateValue( 'AccessLevel', ADMINISTRATOR );
   $specmanagement_config_api->updateValue( 'ReadAccessLevel', REPORTER );
   $specmanagement_config_api->updateValue( 'WriteAccessLevel', DEVELOPER );

   $specmanagement_config_api->updateButton( 'ShowInFooter' );
   $specmanagement_config_api->updateButton( 'ShowFields' );
   $specmanagement_config_api->updateButton( 'ShowMenu' );
   $specmanagement_config_api->updateButton( 'ShowSpecStatCols' );

   $col_amount = gpc_get_int( 'CAmount', PLUGINS_SPECMANAGEMENT_COLUMN_AMOUNT );
   if ( plugin_config_get( 'CAmount' ) != $col_amount && plugin_config_get( 'CAmount' ) != '' && $col_amount <= PLUGINS_SPECMANAGEMENT_MAX_COLUMNS )
   {
      plugin_config_set( 'CAmount', $col_amount );
   }
   elseif ( plugin_config_get( 'CAmount' ) == '' )
   {
      plugin_config_set( 'CAmount', PLUGINS_SPECMANAGEMENT_COLUMN_AMOUNT );
   }
   $specmanagement_config_api->updateDynamicValues( 'CStatSelect', PLUGINS_SPECMANAGEMENT_COLUMN_STAT_DEFAULT );
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
   if ( isset( $_POST['type'] ) )
   {
      $specmanagement_database_api->insert_type_row( $_POST['type'] );
   }
}

/**
 * Delete a document type
 */
if ( $option_deltype )
{
   if ( isset( $_POST['types'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $specmanagement_database_api->get_type_id( $type_string );

      /*
       * Just delete a type if it is not used!
       */
      if ( !$specmanagement_database_api->check_type_is_used( $type_id ) )
      {
         $specmanagement_database_api->delete_type_row( $type_string );
      }
   }
}

/**
 * Change a document type
 */
if ( $option_changetype )
{
   if ( isset( $_POST['types'] ) && isset( $_POST['newtype'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $specmanagement_database_api->get_type_id( $type_string );
      $new_type_string = $_POST['newtype'];

      $specmanagement_database_api->update_type_row( $type_id, $new_type_string );
   }
}

/**
 *
 */
if ( $option_manage_doc_types )
{
   print_successful_redirect( plugin_page( 'manage_types', true ) );
}

form_security_purge( 'plugin_SpecManagement_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );