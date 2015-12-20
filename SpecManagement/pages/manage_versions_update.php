<?php
auth_reauthenticate();

require_once( SPECMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECMANAGEMENT_CORE_URI . 'database_api.php';

$database_api = new database_api();

$option_assign = gpc_get_bool( 'assigntype', false );
$option_setversion = gpc_get_bool( 'setversion', false );
$option_delversion = gpc_get_bool( 'deleteversion', false );
$option_addversion = gpc_get_bool( 'addversion', false );

$version_id = null;
$new_type_id = null;
$project_id = null;
$new_version = null;

/**
 * Submit type changes
 */
if ( $option_assign && !is_null( $_POST['version_id'] ) && !is_null( $_POST['type'] ) )
{
   $project_id = helper_get_current_project();
   $version_id = $_POST['version_id'];
   $new_type_id = $database_api->getTypeId( $_POST['type'] );

   $database_api->updateVersionAssociatedType( $project_id, $version_id, $new_type_id );
}

/**
 * Change a version
 */
if ( $option_setversion && !is_null( $_POST['version_id'] ) )
{
   print_successful_redirect( plugin_page( 'manage_versions_set', true ) . '&version_id=' . $_POST['version_id'] );
}

/**
 * Delete a version
 */
if ( $option_delversion && !is_null( $_POST['version_id'] ) )
{
   print_successful_redirect( plugin_page( 'manage_versions_delete', true ) . '&version_id=' . $_POST['version_id'] );
}

/**
 * Submit new version
 */
if ( $option_addversion && !is_null( $_POST['project_id'] ) && !is_null( $_POST['new_version'] ) )
{
   $project_id = $_POST['project_id'];
   $new_version = $_POST['new_version'];

   if ( version_is_unique( $new_version, $project_id ) )
   {
      version_add( $project_id, $new_version );
   }
}

form_security_purge( 'plugin_SpecManagement_manage_versions_update' );

print_successful_redirect( plugin_page( 'manage_versions', true ) );