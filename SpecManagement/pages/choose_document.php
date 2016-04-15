<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

calculate_page_content();

function calculate_page_content()
{
   $specmanagement_database_api = new specmanagement_database_api();
   $specmanagement_print_api = new specmanagement_print_api();
   $types = array();
   $types_rows = $specmanagement_database_api->get_full_types();
   foreach ( $types_rows as $types_row )
   {
      $types[] = $types_row[1];
   }

   html_page_top1( plugin_lang_get( 'select_doc_title' ) );
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/specmanagement.css">';
   html_page_top2();
   if ( plugin_is_installed( 'WhiteboardMenu' ) )
   {
      require_once WHITEBOARDMENU_CORE_URI . 'whiteboard_print_api.php';
      $whiteboard_print_api = new whiteboard_print_api();
      $whiteboard_print_api->printWhiteboardMenu();
   }

   if ( project_includes_user( helper_get_current_project(), auth_get_current_user_id() ) || helper_get_current_project() == 0 || user_is_administrator( auth_get_current_user_id() ) )
   {
      echo '<div align="center">';
      echo '<hr size="1" width="50%" />';
      $specmanagement_print_api->printTableTop( '50' );
      $specmanagement_print_api->printFormTitle( 2, 'menu_title' );
      $specmanagement_print_api->printCategoryField( 1, 1, 'select_type' );
      echo '<td>';
      echo '<form method="post" name="form_set_source" action="' . plugin_page( 'editor' ) . '">';
      print_document_selection( $types );
      $specmanagement_print_api->printRow();
      echo '<td class="center" colspan="2">';
      echo '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get( 'select_confirm' ) . '"/>';
      echo '</td>';
      echo '</tr>';
      echo '</form>';
      echo '</td>';

      $specmanagement_print_api->printTableFoot();
   }
   else
   {
      echo '<table class="width60"><tr><td class="center">' . lang_get( 'access_denied' ) . '</td></tr></table>';
   }
   html_page_bottom1();
}

/**
 * @param $types
 */
function print_document_selection( $types )
{
   $project_id = gpc_get_int( 'project_id', helper_get_current_project() );
   $specmanagement_database_api = new specmanagement_database_api();

   echo '<select name="version_id">';
   foreach ( $types as $type )
   {
      $type_string = string_html_specialchars( $type );
      $type_id = $specmanagement_database_api->get_type_id( $type );
      $version_id_array = get_version_ids( $type_id, $project_id );
      foreach ( $version_id_array as $version_id )
      {
         $version_spec_project_id = version_get_field( $version_id, 'project_id' );
         if ( project_includes_user( $version_spec_project_id, auth_get_current_user_id() ) || user_is_administrator( auth_get_current_user_id() ) )
         {
            $version_string = version_full_name( $version_id );

            echo '<option value="' . $version_id . '">';
            echo $type_string . " - " . $version_string;
            echo '</option>';
         }
      }
   }
   echo '</select>';
}

/**
 * @param $type_id
 * @param $project_id
 * @return array
 */
function get_version_ids( $type_id, $project_id )
{
   $specmanagement_database_api = new specmanagement_database_api();

   $version_id_array = array();
   $version_ids = $specmanagement_database_api->get_version_ids( $type_id, $project_id );
   foreach ( $version_ids as $version_id )
   {
      array_push( $version_id_array, $version_id );
   }

   if ( $project_id != 0 )
   {
      $sub_project_ids = project_hierarchy_get_all_subprojects( $project_id );
      foreach ( $sub_project_ids as $sub_project_id )
      {
         $version_ids = $specmanagement_database_api->get_version_ids( $type_id, $sub_project_id );
         foreach ( $version_ids as $version_id )
         {
            array_push( $version_id_array, $version_id );
         }
      }
   }
   return $version_id_array;
}