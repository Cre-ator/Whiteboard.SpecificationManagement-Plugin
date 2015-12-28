<?php
include SPECMANAGEMENT_CORE_URI . 'authorization_api.php';
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$print_api = new print_api();

$edit_page = false;
if ( isset( $_POST['edit'] ) )
{
   $edit_page = true;
}

/**
 * Page content
 */
html_page_top1( plugin_lang_get( 'manversions_title' ) );
echo '<link rel="stylesheet" href="plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR . 'files/specmanagement.css">';
html_page_top2();
if ( plugin_is_installed( 'WhiteboardMenu' ) )
{
   $print_api->print_whiteboardplugin_menu();
}
$print_api->print_plugin_menu();
echo '<div align="center">';
echo '<hr size="1" width="100%" />';
print_table( $edit_page );
html_page_bottom1();
/* **************************** */

/**
 * @param bool $edit_page
 */
function print_table( $edit_page = false )
{
   $authorization_api = new authorization_api();
   $database_api = new database_api();
   $print_api = new print_api();

   if ( $edit_page )
   {
      echo '<form action="' . plugin_page( 'manage_versions_update' ) . '" method="post">';
   }
   echo '<table class="width90" cellspacing="1">';

   echo '<thead>';
   $print_api->printFormTitle( 7, 'manversions_thead' );
   echo '<tr class="row-category">';
   $print_api->printTableHeadCol( 1, 'version' );
   $print_api->printTableHeadCol( 1, 'released' );
   $print_api->printTableHeadCol( 1, 'obsolete' );
   $print_api->printTableHeadCol( 1, 'timestamp' );
   echo '<th colspan="1">' . plugin_lang_get( 'manversions_thdoctype' ) . '</th>';
   $print_api->printTableHeadCol( 1, 'description' );
   if ( $edit_page )
   {
      $print_api->printTableHeadCol( 1, 'actions' );
   }
   echo '</tr>';
   echo '</thead>';

   echo '<tbody>';
   $versions = version_get_all_rows( helper_get_current_project(), null, null );

   for ( $version_index = 0; $version_index < count( $versions ); $version_index++ )
   {
      $version = $versions[$version_index];
      $current_type = $database_api->getTypeString( $database_api->getTypeByVersion( $version['id'] ) );

      $print_api->printRow();
      echo '<input type="hidden" name="version_ids[]" value="' . $version['id'] . '"/>';

      /* Name */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="input" style="width:100%;">';
         echo '<input type="text" id="proj-version-new-version" name="version[]"
                style="width:100%;" maxlength="64" value="' . string_attribute( $version['version'] ) . '" />';
         echo '</span>';
      }
      else
      {
         echo string_display( version_full_name( $version['id'] ) );
      }
      echo '</td>';

      /* Released */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="checkbox">'; ?>
         <input type="checkbox" id="proj-version-released"
                name="released<?php echo $version_index ?>" <?php check_checked( (boolean)$version['released'], true ); ?> />
         <?php echo '</span>';
      }
      else
      {
         echo trans_bool( $version['released'] );
      }
      echo '</td>';

      /* Obsolete */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="checkbox">'; ?>
         <input type="checkbox" id="proj-version-obsolete"
                name="obsolete<?php echo $version_index ?>" <?php check_checked( (boolean)$version['obsolete'], true ); ?> />
         <?php echo '</span>';
      }
      else
      {
         echo trans_bool( $version['obsolete'] );
      }
      echo '</td>';

      /* Date */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="input">';
         echo '<input type="text" id="proj-version-date-order" name="date_order[]" class="datetime" size="15"
                value="' . ( date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) ) ) . '" />';
         echo '</span>';
      }
      else
      {
         echo date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) );
      }
      echo '</td>';

      /* Type */
      echo '<td>';
      if ( $edit_page )
      {
         $types = array();
         $types_rows = $database_api->getFullTypes();
         foreach ( $types_rows as $types_row )
         {
            $types[] = $types_row[1];
         }
         echo '<span class="select">';
         echo '<select ' . helper_get_tab_index() . ' id="proj-version-type" name="type[]">';
         echo '<option value=""></option>';
         foreach ( $types as $type )
         {
            echo '<option value="' . $type . '"';
            check_selected( string_attribute( $current_type ), $type );
            echo '>' . $type . '</option>';
         }
         echo '</select>';
      }
      else
      {
         $type_id = $database_api->getTypeByVersion( $version['id'] );
         $type_string = $database_api->getTypeString( $type_id );
         echo string_display( $type_string );
      }
      echo '</td>';

      /* Description */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="textarea">';
         echo '<textarea id="proj-version-description" name="description[]" cols="40" rows="1">';
         echo string_attribute( $version['description'] );
         echo '</textarea>';
         echo '</span>';
      }
      else
      {
         echo string_display( $version['description'] );
      }
      echo '</td>';

      /* Action */
      if ( $edit_page )
      {
         echo '<td>';
         echo '<a href="' . plugin_page( 'manage_versions_delete' ) . '&version_id=' . $version['id'] . '">';
         echo '<input type="button" value="' . lang_get( 'delete_link' ) . '" />';
         echo '</a>';
         echo '</td>';
      }

      echo '</tr>';
   }

   if ( $edit_page )
   {
      echo '<tr>';
      echo '<td colspan="7">';
      echo '<input type="text" name="new_version" size="32" maxlength="64"/>';
      echo '&nbsp<input type="submit" name="addversion" class="button" value="' . lang_get( 'add_version_button' ) . '"/>';
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td colspan="7" class="center">';
      echo '<input type="submit" name="update" class="button" value="' . plugin_lang_get( 'manversions_edit_submit' ) . '"/>';
      echo '</td>';
      echo '</tr>';

      echo '</tbody>';
      echo '</table>';
      echo '</form>';
   }
   else
   {
      if ( $authorization_api->userHasGlobalLevel() || $authorization_api->userHasWriteLevel() )
      {
         echo '<tr>';
         echo '<td colspan="7" class="center">';
         echo '<form action="' . plugin_page( 'manage_versions' ) . '" method="post">';
         echo '<span class="input">';
         echo '<input type="submit" name="edit" class="button" value="' . plugin_lang_get( 'manversions_edit' ) . '"/>';
         echo '</span>';
         echo '</td>';
         echo '</tr>';

         echo '</tbody>';
         echo '</table>';
         echo '</form>';
      }
   }
}