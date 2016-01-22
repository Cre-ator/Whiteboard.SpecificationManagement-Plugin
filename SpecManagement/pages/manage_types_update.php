<?php
auth_reauthenticate();

require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';

$database_api = new database_api();
$update = gpc_get_bool( 'update', false );

/**
 * Change all existing types
 */
if ( $update && isset( $_POST['type_ids'] ) )
{
   $type_ids = $_POST['type_ids'];

   for ( $type_index = 0; $type_index < count( $type_ids ); $type_index++ )
   {
      $project_id = helper_get_current_project();
      $type_id = $type_ids[$type_index];

      /* initialize option array */
      $type_options = array();
      /* initialize option values */
      $show_pt = null;
      $show_eo = null;
      $show_dy = null;

      /* get informations about changes */
      if ( isset( $_POST['showpt' . $type_index] ) )
      {
         $show_pt = $_POST['showpt' . $type_index];
         if ( $show_pt == 'on' )
         {
            $show_pt = 1;
         }
      }

      if ( isset( $_POST['showeo' . $type_index] ) )
      {
         $show_eo = $_POST['showeo' . $type_index];
         if ( $show_eo == 'on' )
         {
            $show_eo = 1;
         }
      }

      if ( isset( $_POST['showdy' . $type_index] ) )
      {
         $show_dy = $_POST['showdy' . $type_index];
         if ( $show_dy == 'on' )
         {
            $show_dy = 1;
         }
      }

      /* fill array with option values */
      array_push( $type_options, $show_pt );
      array_push( $type_options, $show_eo );
      array_push( $type_options, $show_dy );
      /* generate option string */
      $type_options_set = implode( ';', $type_options );
      /* fill database with option string */
      $database_api->updateTypeOptions( $type_id, $type_options_set );
   }
}

form_security_purge( 'plugin_SpecManagement_manage_types_update' );

print_successful_redirect( plugin_page( 'manage_types', true ) );