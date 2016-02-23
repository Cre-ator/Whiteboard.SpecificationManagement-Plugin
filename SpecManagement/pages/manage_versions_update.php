<?php
auth_reauthenticate();

require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';

$specmanagement_database_api = new specmanagement_database_api();
$update = gpc_get_bool( 'update', false );
$addversion = gpc_get_bool( 'addversion', false );

/**
 * Submit new version
 */
if ( $addversion && isset( $_POST['new_version'] ) )
{
   $project_id = helper_get_current_project();
   $new_version = $_POST['new_version'];

   if ( version_is_unique( $new_version, $project_id ) )
   {
      version_add( $project_id, $new_version );
   }
}

/**
 * Change all existing versions
 */
if ( $update && isset( $_POST['version_ids'] ) )
{
   $version_ids = $_POST['version_ids'];
   $versions = $_POST['version'];
   $date_order = $_POST['date_order'];
   $type = $_POST['type'];
   $description = $_POST['description'];

   for ( $version_id = 0; $version_id < count( $version_ids ); $version_id++ )
   {
      $version = version_get( $version_ids[$version_id] );
      $project_id = helper_get_current_project();

      $released = null;
      $obsolete = null;

      if ( isset( $_POST['released' . $version_id] ) )
      {
         $released = $_POST['released' . $version_id];
      }
      if ( isset( $_POST['obsolete' . $version_id] ) )
      {
         $obsolete = $_POST['obsolete' . $version_id];
      }

      if ( !is_null( $versions ) )
      {
         $new_version = $versions[$version_id];
         $version->version = trim( $new_version );
      }

      if ( is_null( $released ) )
      {
         $version->released = false;
      }
      else if ( $released == 'on' )
      {
         $version->released = true;
      }

      if ( is_null( $obsolete ) )
      {
         $version->obsolete = false;
      }
      else if ( $obsolete == 'on' )
      {
         $version->obsolete = true;
      }

      if ( !is_null( $date_order ) )
      {
         $new_date_order = $date_order[$version_id];
         $version->date_order = $new_date_order;
      }

      if ( !is_null( $type ) )
      {
         $new_type = $type[$version_id];
         if ( strlen( $new_type ) > 0 )
         {
            $new_type_id = $specmanagement_database_api->getTypeId( $new_type );
            $specmanagement_database_api->updateVersionAssociatedType( $project_id, $version_ids[$version_id], $new_type_id );
         }
         else
         {
            $specmanagement_database_api->updateVersionAssociatedType( $project_id, $version_ids[$version_id], 9999 );
         }
      }

      if ( !is_null( $description ) )
      {
         $new_description = $description[$version_id];
         $version->description = $new_description;
      }

      version_update( $version );

      event_signal( 'EVENT_MANAGE_VERSION_UPDATE', array( $version->id ) );
   }
}

form_security_purge( 'plugin_SpecManagement_manage_versions_update' );

print_successful_redirect( plugin_page( 'manage_versions', true ) );