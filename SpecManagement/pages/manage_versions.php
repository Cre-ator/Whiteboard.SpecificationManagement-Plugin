<?php
require_once SPECMANAGEMENT_CORE_URI . 'authorization_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';

define( 'COLS', 6 );
$print_api = new print_api();

$edit_page = false;
if ( isset( $_POST['edit'] ) )
{
   $edit_page = true;
}

/**
 * Page content
 */
echo '<link rel="stylesheet" href="plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR . 'files/specmanagement.css">';
html_page_top1( plugin_lang_get( 'manversions_title' ) );
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

function print_table( $edit_page = false )
{
   $database_api = new database_api();
   $print_api = new print_api();

   if ( $edit_page )
   {
      echo '<form action="' . plugin_page( 'manage_versions_update' ) . '" method="post">';
   }

   if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
   {
      echo '<table class="width100" cellspacing="1" cellpadding="0">';
   }
   else
   {
      echo '<div class="table-container">';
      echo '<table cellspacing="1" cellpadding="0">';
   }

   print_tablehead( $edit_page );

   echo '<tbody>';
   $versions = version_get_all_rows( helper_get_current_project(), null, null );

   for ( $version_index = 0; $version_index < count( $versions ); $version_index++ )
   {
      $version = $versions[$version_index];
      $current_type = $database_api->getTypeString( $database_api->getTypeByVersion( $version['id'] ) );

      $print_api->printRow();
      echo '<input type="hidden" name="version_ids[]" value="' . $version['id'] . '"/>';
      print_name( $edit_page, $version );
      print_released( $edit_page, $version_index, $version );
      print_obsolete( $edit_page, $version_index, $version );
      print_date( $edit_page, $version );
      print_type( $edit_page, $current_type, $version );
      print_description( $edit_page, $version );
      print_action( $edit_page, $version );
      echo '</tr>';
   }

   if ( $edit_page )
   {
      print_editbuttons();
   }
   else
   {
      print_tablefooter();
   }
   echo '</tbody>';
   echo '</table>';
   if ( substr( MANTIS_VERSION, 0, 4 ) != '1.2.' )
   {
      echo '</div>';
   }
   echo '</form>';
}

function print_tablefooter()
{
   $authorization_api = new authorization_api();

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
   }
}

function print_editbuttons()
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
}

function print_action( $edit_page, $version )
{
   if ( $edit_page )
   {
      echo '<td>';
      echo '<a href="' . plugin_page( 'manage_versions_delete' ) . '&version_id=' . $version['id'] . '">';
      echo '<input type="button" value="' . lang_get( 'delete_link' ) . '" />';
      echo '</a>';
      echo '</td>';
   }
}

function print_description( $edit_page, $version )
{
   echo '<td width="100">';
   if ( $edit_page )
   {
      echo '<span class="text">';
      echo '<input type="text" id="proj-version-description" name="description[]" value="' . string_attribute( $version['description'] ) . '"/>';
      echo '</span>';
   }
   else
   {
      echo string_display( $version['description'] );
   }
   echo '</td>';
}

function print_type( $edit_page, $current_type, $version )
{
   $database_api = new database_api();

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
}

function print_date( $edit_page, $version )
{
   echo '<td>';
   if ( $edit_page )
   {
      echo '<span class="input">';
      echo '<label for="proj-version-date-order">';
      echo '<input type="text" id="proj-version-date-order" name="date_order[]" class="datetime" size="15"
                      value="' . ( date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) ) ) . '" />';
      echo '</label>';
      echo '</span>';
   }
   else
   {
      echo date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) );
   }
   echo '</td>';
}

function print_obsolete( $edit_page, $version_index, $version )
{
   echo '<td class="center">';
   if ( $edit_page )
   {
      echo '<span class="checkbox">'; ?>
      <label for="proj-version-obsolete">
         <input type="checkbox" id="proj-version-obsolete"
                name="obsolete<?php echo $version_index ?>" <?php check_checked( (boolean) $version['obsolete'], true ); ?> />
      </label>
      <?php echo '</span>';
   }
   else
   {
      echo trans_bool( $version['obsolete'] );
   }
   echo '</td>';
}

function print_released( $edit_page, $version_index, $version )
{
   echo '<td class="center">';
   if ( $edit_page )
   {
      echo '<span class="checkbox">'; ?>
      <label for="proj-version-released">
         <input type="checkbox" id="proj-version-released"
                name="released<?php echo $version_index ?>" <?php check_checked( (boolean) $version['released'], true ); ?> />
      </label>
      <?php echo '</span>';
   }
   else
   {
      echo trans_bool( $version['released'] );
   }
   echo '</td>';
}

function print_name( $edit_page, $version )
{
   echo '<td width="200">';
   if ( $edit_page )
   {
      echo '<span class="input" style="width:100%;">';
      echo '<label for="proj-version-new-version">';
      echo '<input type="text" id="proj-version-new-version" name="version[]"
                      style="width:100%;" maxlength="64" value="' . string_attribute( $version['version'] ) . '" />';
      echo '</label>';
      echo '</span>';
   }
   else
   {
      echo string_display( version_full_name( $version['id'] ) );
   }
   echo '</td>';
}

function print_tablehead( $edit_page )
{
   $print_api = new print_api();

   $cols = ( COLS - 1 );
   if ( $edit_page )
   {
      $cols = COLS;
   }

   echo '<thead>';
   $print_api->printFormTitle( $cols, 'manversions_thead' );
   echo '<tr class="row-category2">';
   echo '<th class="form-title" colspan="1" width="40%">' . lang_get( 'version' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="10%">' . lang_get( 'released' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="10%">' . lang_get( 'obsolete' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="10%">' . lang_get( 'timestamp' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="10%">' . plugin_lang_get( 'manversions_thdoctype' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="10%">' . lang_get( 'description' ) . '</th>';
   if ( $edit_page )
   {
      echo '<th class="form-title" colspan="1" width="10%">' . lang_get( 'actions' ) . '</th>';
   }
   echo '</tr>';
   echo '</thead>';
}