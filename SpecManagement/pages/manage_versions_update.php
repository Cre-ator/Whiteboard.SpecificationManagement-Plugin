<?php
auth_reauthenticate();

require_once( SPECMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECMANAGEMENT_CORE_URI . 'database_api.php';

$database_api = new database_api();

$option_assign = gpc_get_bool( 'assigntype', false );
$option_addversion = gpc_get_bool( 'addversion', false );

$version_id = null;
$new_type_id = null;
$project_id = null;
$new_version = null;

/**
 * Submit type changes
 */
if ( $option_assign && !is_null( $_POST['version_id'] ) && !is_null( $_POST['types'] ) )
{
   $project_id = helper_get_current_project();
   $version_ids = $_POST['version_id'];
   $new_types = $_POST['types'];

   for ( $index = 0; $index < count( $version_ids ); $index++ )
   {
      $new_type_id = $database_api->getTypeId( $new_types[$index] );
      $version_id = $version_ids[$index];
      $database_api->updateVersionAssociatedType( $project_id, $version_id, $new_type_id );
   }
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