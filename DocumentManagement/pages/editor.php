<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_editor_api.php';

if ( isset( $_POST['version_id'] ) )
{
   $print_flag = false;
   if ( isset( $_POST['print'] ) )
   {
      $print_flag = true;
   }

   html_page_top1( plugin_lang_get( 'editor_title' ) );
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/specmanagement.css">';
   if ( !$print_flag )
   {
      html_page_top2();
      if ( plugin_is_installed( 'WhiteboardMenu' ) && file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' ) )
      {
         require_once WHITEBOARDMENU_CORE_URI . 'whiteboard_print_api.php';
         $whiteboard_print_api = new whiteboard_print_api();
         $whiteboard_print_api->printWhiteboardMenu();
      }
   }
   calculate_page_content( $print_flag );
   if ( !$print_flag )
   {
      html_page_bottom1();
   }
}
else
{
   print_successful_redirect( 'plugin.php?page=DocumentManagement/choose_document' );
}

/**
 * @param $print_flag
 */
function calculate_page_content( $print_flag )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $specmanagement_print_api = new specmanagement_print_api();
   $specmanagement_editor_api = new specmanagement_editor_api();
   $version_id = $_POST['version_id'];
   $version_spec_bug_ids = $specmanagement_database_api->get_version_spec_bugs( version_get_field( $version_id, 'version' ) );
   if ( !is_null( $version_spec_bug_ids ) )
   {
      /** get bug and work package data */
      $plugin_version_obj = $specmanagement_database_api->get_plugin_version_row_by_version_id( $version_id );
      $p_version_id = $plugin_version_obj[0];
      foreach ( $version_spec_bug_ids as $version_spec_bug_id )
      {
         $p_source_row = $specmanagement_database_api->get_source_row( $version_spec_bug_id );
         if ( is_null( $p_source_row[2] ) )
         {
            $specmanagement_database_api->update_source_row( $version_spec_bug_id, $p_version_id, '' );
         }
      }
      $work_packages = $specmanagement_database_api->get_document_spec_workpackages( $p_version_id );
      asort( $work_packages );
      $no_work_package_bug_ids = $specmanagement_database_api->get_workpackage_spec_bugs( $p_version_id, '' );
      /** get type options */
      $type_string = $specmanagement_database_api->get_type_string( $specmanagement_database_api->get_type_by_version( $version_id ) );
      $type_id = $specmanagement_database_api->get_type_id( $type_string );
      $type_row = $specmanagement_database_api->get_type_row( $type_id );
      $type_options = explode( ';', $type_row[2] );

      /** generate and print page content */
      $specmanagement_editor_api->print_document_head( $type_string, $version_id, $version_spec_bug_ids, $print_flag );
      if ( $type_options[2] == '1' )
      {
         $specmanagement_editor_api->print_directory( $p_version_id, $work_packages, $no_work_package_bug_ids, $type_options[0], $print_flag );
      }
      $specmanagement_editor_api->print_editor_table_head( $print_flag );
      $specmanagement_editor_api->generate_content( $p_version_id, $work_packages, $no_work_package_bug_ids, $type_options[0], true, $print_flag );
      echo '</table>';
      if ( $type_options[1] == '1' )
      {
         $specmanagement_editor_api->print_expenses_overview( $work_packages, $p_version_id, $print_flag, $no_work_package_bug_ids );
      }
   }
   else
   {
      echo '<br/>';
      $specmanagement_editor_api->print_editor_table_head( $print_flag );
      echo '<tr><td class="center">';
      echo plugin_lang_get( 'editor_no_issues' );
      echo '</td></tr>';
      $specmanagement_print_api->printTableFoot();
   }
}