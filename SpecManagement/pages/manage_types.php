<?php
require_once SPECMANAGEMENT_CORE_URI . 'authorization_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';

define( 'COLS', 4 );
$print_api = new print_api();

$edit_page = false;
if ( isset( $_POST['edit'] ) )
{
   $edit_page = true;
}

/**
 * Page content
 */
$print_api->print_page_head( plugin_lang_get( 'mantypes_title' ) );
echo '<div align="center">';
echo '<hr size="1" width="100%" />';
print_table( $edit_page );
html_page_bottom1();
/* **************************** */

function print_table( $edit_page = false )
{
   $print_api = new print_api();

   if ( $edit_page )
   {
      echo '<form action="' . plugin_page( 'manage_types_update' ) . '" method="post">';
   }
   $print_api->printTableTop( '100' );
   print_tablehead();
   print_tablebody( $edit_page );
   $print_api->printTableFoot();
   echo '</form>';
}

/**
 * @param $edit_page
 */
function print_tablebody( $edit_page )
{
   echo '<tbody>';
   print_types( $edit_page );
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
 */
function print_types( $edit_page )
{
   $database_api = new database_api();
   $print_api = new print_api();

   $types = $database_api->getFullTypes();
   for ( $type_index = 0; $type_index < count( $types ); $type_index++ )
   {
      $type = $types[$type_index];

      $type_id = $type[0];
      $type_string = $type[1];

      $type_options_set = $type[2];
      $type_options = explode( ';', $type_options_set );

      $option_show_duration = $type_options[0];
      $option_show_expenses_overview = $type_options[1];
      $option_show_directory = $type_options[2];

      $print_api->printRow();
      echo '<input type="hidden" name="type_ids[]" value="' . $type_id . '"/>';

      print_name( $type_string );
      print_duration( $edit_page, $type_index, $option_show_duration );
      print_expoverview( $edit_page, $type_index, $option_show_expenses_overview );
      print_dictionary( $edit_page, $type_index, $option_show_directory );
      echo '</tr>';
   }
}

function print_tablefooter()
{
   $authorization_api = new authorization_api();

   if ( $authorization_api->userHasGlobalLevel() || $authorization_api->userHasWriteLevel() )
   {
      echo '<tr>';
      echo '<td colspan="' . COLS . '" class="center">';
      echo '<form action="' . plugin_page( 'manage_types' ) . '" method="post">';
      echo '<span class="input">';
      echo '<input type="submit" name="edit" class="button" value="' . plugin_lang_get( 'mantypes_edit' ) . '"/>';
      echo '</span>';
      echo '</td>';
      echo '</tr>';
   }
}

function print_editbuttons()
{
   echo '<tr>';
   echo '<td colspan="' . COLS . '">';
   echo '<input type="text" name="new_version" size="32" maxlength="64"/>';
   echo '&nbsp<input type="submit" name="addversion" class="button" value="' . lang_get( 'add_version_button' ) . '"/>';
   echo '</td>';
   echo '</tr>';

   echo '<tr>';
   echo '<td colspan="' . COLS . '" class="center">';
   echo '<input type="submit" name="update" class="button" value="' . plugin_lang_get( 'manversions_edit_submit' ) . '"/>';
   echo '</td>';
   echo '</tr>';
}

function print_dictionary( $edit_page, $type_index, $option_show_directory )
{
   echo '<td class="center">';
   if ( $edit_page )
   {
      echo '<span class="checkbox">'; ?>
      <label>
         <input type="checkbox"
                name="showdy<?php echo $type_index ?>" <?php check_checked( (boolean) $option_show_directory, true );
         ?> />
      </label>
      <?php echo '</span>';
   }
   else
   {
      echo trans_bool( $option_show_directory );
   }
   echo '</td>';
}

function print_expoverview( $edit_page, $type_index, $option_show_expenses_overview )
{
   echo '<td class="center">';
   if ( $edit_page )
   {
      echo '<span class="checkbox">'; ?>
      <label>
         <input type="checkbox"
                name="showeo<?php echo $type_index ?>" <?php check_checked( (boolean) $option_show_expenses_overview, true );
         ?> />
      </label>
      <?php echo '</span>';
   }
   else
   {
      echo trans_bool( $option_show_expenses_overview );
   }
   echo '</td>';
}

function print_duration( $edit_page, $type_index, $option_show_duration )
{
   echo '<td class="center">';
   if ( $edit_page )
   {
      echo '<span class="checkbox">'; ?>
      <label>
         <input type="checkbox"
                name="showpt<?php echo $type_index ?>" <?php check_checked( (boolean) $option_show_duration, true );
         ?> />
      </label>
      <?php echo '</span>';
   }
   else
   {
      echo trans_bool( $option_show_duration );
   }
   echo '</td>';
}

function print_name( $type_string )
{
   echo '<td>';
   echo string_display( $type_string );
   echo '</td>';
}

function print_tablehead()
{
   $print_api = new print_api();

   $col_width = 100 / COLS;
   echo '<thead>';
   $print_api->printFormTitle( COLS, 'mantypes_thead' );
   echo '<tr class="row-category2">';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'manversions_thdoctype' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'mantypes_show_print_duration' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'mantypes_show_expenses_overview' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'mantypes_show_directory' ) . '</th>';
   echo '</tr>';
   echo '</thead>';
}