<?php
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';

calculate_page_content();

function calculate_page_content()
{
   $database_api = new database_api();
   $print_api = new print_api();
   $types = array();
   $types_rows = $database_api->getFullTypes();
   foreach ( $types_rows as $types_row )
   {
      $types[] = $types_row[1];
   }

   $print_api->print_page_head( plugin_lang_get( 'select_doc_title' ) );

   echo '<div align="center">';
   echo '<hr size="1" width="50%" />';
   $print_api->printTableTop( '50' );
   $print_api->printFormTitle( 2, 'select_doc' );
   $print_api->printCategoryField( 1, 1, 'select_type' );
   echo '<td>';
   echo '<form method="post" name="form_set_source" action="' . plugin_page( 'editor' ) . '">';
   print_document_selection( $types );
   $print_api->printRow();
   echo '<td class="center" colspan="2">';
   echo '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get( 'select_confirm' ) . '"/>';
   echo '</td>';
   echo '</tr>';
   echo '</form>';

   $print_api->printTableFoot();
   html_page_bottom1();
}

/**
 * @param $types
 */
function print_document_selection( $types )
{
   $t_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
   $database_api = new database_api();
   echo '<select name="version_id">';
   foreach ( $types as $type )
   {
      $type_string = string_html_specialchars( $type );

      $type_id = $database_api->getTypeId( $type );
      $version_ids = $database_api->getVersionIDs( $type_id, $t_project_id );
      foreach ( $version_ids as $version_id )
      {
         $version_string = version_full_name( $version_id );

         echo '<option value="' . $version_id . '">';
         echo $type_string . " - " . $version_string;
         echo '</option>';
      }

   }
   echo '</select>';
}

