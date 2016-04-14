<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_authorization_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

define( 'COLS', 7 );
$specmanagement_print_api = new specmanagement_print_api();

$obsolete_flag = false;
if ( isset( $_POST['obsolete_flag'] ) )
{
   $obsolete_flag = true;
}
if ( isset( $_POST['non_obsolete_flag'] ) )
{
   $obsolete_flag = false;
}

$edit_page = false;
if ( isset( $_POST['edit'] ) )
{
   $edit_page = true;
}

/**
 * Page content
 */
$specmanagement_print_api->print_page_head( plugin_lang_get( 'menu_manversions' ) );
echo '<div align="center">';
echo '<hr size="1" width="100%" />';
print_table( $edit_page, $obsolete_flag );
html_page_bottom1();
/* **************************** */

/**
 * @param bool $edit_page
 * @param $obsolete_flag
 */
function print_table( $edit_page = false, $obsolete_flag )
{
   $specmanagement_print_api = new specmanagement_print_api();

   if ( $edit_page )
   {
      echo '<form action="' . plugin_page( 'manage_versions_update' ) . '" method="post">';
   }
   $specmanagement_print_api->printTableTop( '100' );
   print_tablehead( $edit_page, $obsolete_flag );
   print_tablebody( $edit_page, $obsolete_flag );
   $specmanagement_print_api->printTableFoot();
   echo '</form>';
}

/**
 * @param $edit_page
 * @param $obsolete_flag
 */
function print_tablebody( $edit_page, $obsolete_flag )
{
   echo '<tbody>';
   print_versions( $edit_page, $obsolete_flag );
   if ( $edit_page )
   {
      print_editbuttons();
   }
   else
   {
      print_tablefooter();
   }
   echo '</tbody>';
}

/**
 * @param $edit_page
 * @param $obsolete_flag
 */
function print_versions( $edit_page, $obsolete_flag )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $specmanagement_print_api = new specmanagement_print_api();

   $obsolote = false;
   if ( $obsolete_flag )
   {
      $obsolote = null;
   }

   if ( $edit_page )
   {
      $versions = version_get_all_rows_with_subs( helper_get_current_project(), null, null );
   }
   else
   {
      $versions = version_get_all_rows_with_subs( helper_get_current_project(), null, $obsolote );
   }

   for ( $version_index = 0; $version_index < count( $versions ); $version_index++ )
   {
      $version = $versions[$version_index];
      $current_type = $specmanagement_database_api->get_type_string( $specmanagement_database_api->get_type_by_version( $version['id'] ) );

      $specmanagement_print_api->printRow();
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
}

function print_tablefooter()
{
   $specmanagement_authorization_api = new specmanagement_authorization_api();

   if ( $specmanagement_authorization_api->userHasGlobalLevel() || $specmanagement_authorization_api->userHasWriteLevel() )
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
   echo '<td colspan="5">';
   echo '<input type="text" name="new_version_name" size="32" maxlength="64"/>';
   echo '&nbsp<input type="date" name="new_version_date"/>';
   echo '&nbsp<input type="submit" name="addversion" class="button" value="' . lang_get( 'add_version_button' ) . '"/>';
   echo '</td>';
   echo '<td colspan="2" class="center">';
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
   $specmanagement_database_api = new specmanagement_database_api();

   echo '<td>';
   if ( $edit_page )
   {
      $types = array();
      $types_rows = $specmanagement_database_api->get_full_types();
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
      $type_id = $specmanagement_database_api->get_type_by_version( $version['id'] );
      $type_string = $specmanagement_database_api->get_type_string( $type_id );
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
      <label for="proj-version-obsolete-<?php echo $version_index ?>">
         <input type="checkbox" id="proj-version-obsolete-<?php echo $version_index ?>"
                name="obsolete<?php echo $version_index ?>" <?php check_checked( (int) $version['obsolete'], ON ); ?> />
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
      <label for="proj-version-released-<?php echo $version_index ?>">
         <input type="checkbox" id="proj-version-released-<?php echo $version_index ?>"
                name="released<?php echo $version_index ?>" <?php check_checked( (int) $version['released'], ON ); ?> />
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

/**
 * @param $edit_page
 * @param $obsolete_flag
 */
function print_tablehead( $edit_page, $obsolete_flag )
{
   echo '<thead>';
   echo '<tr>';
   echo '<td class="form-title" colspan="4">';
   echo plugin_lang_get( 'menu_manversions' );
   echo '</td>';
   if ( !$edit_page )
   {
      echo '<td>';
      echo '<form action="' . plugin_page( 'manage_versions' ) . '" method="post">';
      if ( $obsolete_flag === false )
      {
         echo '<input type="submit" name="obsolete_flag" class="button" value="' . plugin_lang_get( 'versview_obsolete_flag' ) . '"/>';
      }
      else
      {
         echo '<input type="submit" name="non_obsolete_flag" class="button" value="' . plugin_lang_get( 'versview_non_obsolete_flag' ) . '"/>';
      }
      echo '</form>';
      echo '</td>';
   }
   echo '</tr>';

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