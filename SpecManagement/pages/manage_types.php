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
html_page_top1( plugin_lang_get( 'mantypes_title' ) );
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
      echo '<form action="' . plugin_page( 'manage_types_update' ) . '" method="post">';
   }
   echo '<table class="width90" cellspacing="1">';

   echo '<thead>';
   $print_api->printFormTitle( 6, 'mantypes_thead' );
   echo '<tr class="row-category">';
   echo '<th colspan="1">' . plugin_lang_get( 'manversions_thdoctype' ) . '</th>';
   echo '<th colspan="1">' . plugin_lang_get( 'select_show_print_duration' ) . '</th>';
   echo '<th colspan="1">' . plugin_lang_get( 'select_show_expenses_overview' ) . '</th>';
   echo '<th colspan="1">weitere option1</th>';
   echo '<th colspan="1">weitere option2</th>';
   echo '<th colspan="1">weitere option3</th>';
   echo '</tr>';
   echo '</thead>';

   echo '<tbody>';
   $types = $database_api->getFullTypes();

   for ( $type_index = 0; $type_index < count( $types ); $type_index++ )
   {
      $type = $types[$type_index];
      var_dump( $type );

      $print_api->printRow();
      echo '<input type="hidden" name="type_ids[]" value="' . $type[0] . '"/>';

      /* Name */
      echo '<td>';
      echo string_display( $type[1] );
      echo '</td>';

      /* Released */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="checkbox">'; ?>
         <input type="checkbox" id="proj-version-released"
                name="released<?php echo $type_index ?>" <?php /*check_checked( (boolean) $type['released'], true ); */
         ?> />
         <?php echo '</span>';
      }
      else
      {
//         echo trans_bool( $type['released'] );
      }
      echo '</td>';

      /* Obsolete */
      echo '<td>';
      if ( $edit_page )
      {
         echo '<span class="checkbox">'; ?>
         <input type="checkbox" id="proj-version-obsolete"
                name="obsolete<?php echo $type_index ?>" <?php/* check_checked( (boolean) $type['obsolete'], true ); */
         ?> />
         <?php echo '</span>';
      }
      else
      {
//         echo trans_bool( $type['obsolete'] );
      }
      echo '</td>';

      /* Date */
      echo '<td>';
      echo 'option1';
      echo '</td>';

      /* Type */
      echo '<td>';
      echo 'option2';
      echo '</td>';

      /* Description */
      echo '<td>';
      echo 'option3';
      echo '</td>';

      echo '</tr>';
   }

   if ( $edit_page )
   {
      echo '<tr>';
      echo '<td colspan="6">';
      echo '<input type="text" name="new_version" size="32" maxlength="64"/>';
      echo '&nbsp<input type="submit" name="addversion" class="button" value="' . lang_get( 'add_version_button' ) . '"/>';
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td colspan="6" class="center">';
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
         echo '<td colspan="6" class="center">';
         echo '<form action="' . plugin_page( 'manage_types' ) . '" method="post">';
         echo '<span class="input">';
         echo '<input type="submit" name="edit" class="button" value="' . plugin_lang_get( 'mantypes_edit' ) . '"/>';
         echo '</span>';
         echo '</td>';
         echo '</tr>';

         echo '</tbody>';
         echo '</table>';
         echo '</form>';
      }
   }
}