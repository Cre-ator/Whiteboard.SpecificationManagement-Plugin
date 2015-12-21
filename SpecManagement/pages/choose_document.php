<?php
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$database_api = new database_api();
$print_api = new print_api();

$t_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
$types = $database_api->getTypes();
$document_type = $database_api->getFirstType();
$sources = array();

$post = false;
if ( isset( $_POST['types'] ) )
{
   $post = true;
}

html_page_top1( plugin_lang_get( 'select_doc_title' ) );
echo '<link rel="stylesheet" href="plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR . 'files/specmanagement.css">';
html_page_top2();

if ( plugin_is_installed( 'WhiteboardMenu' ) )
{
   $print_api->print_whiteboardplugin_menu();
}

$print_api->print_plugin_menu();

echo '<div align="center">';
echo '<hr size="1" width="50%" />';
echo '<table class="width50" cellspacing="1">';
$print_api->printFormTitle( 2, 'select_doc' );

$print_api->printCategoryField( 1, 1, 'select_type' );
echo '<td>';
echo '<form method="post" name="form_set_requirement" action="' . plugin_page( 'choose_document' ) . '">';
echo '<select name="types">';
foreach ( $types as $type )
{
   echo '<option value="' . $type . '"';
   if ( $post && $_POST['types'] == $type )
   {
      echo ' selected="selected"';
   }
   echo '>' . string_html_specialchars( $type ) . '</option>';
}
echo '</select>';
echo '&nbsp<input type="submit" class="button-small" value="' . lang_get( 'switch' ) . '" />';
echo '</td>';
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'select_version' );
echo '<td>';

if ( $post )
{
   $document_type = $database_api->getTypeId( $_POST['types'] );
}

echo '</form>';
echo '<form method="post" name="form_set_source" action="' . plugin_page( 'editor' ) . '">';
if ( $document_type != null || $_POST['types'] != 'blank' )
{
   $version_ids = $database_api->getVersionIDs( $document_type, $t_project_id );

   echo '<select name="version_id">';
   foreach ( $version_ids as $version_id )
   {
      $version_string = version_full_name( $version_id );
      echo '<option value="' . $version_id . '">' . $version_string . '</option>';
   }
   echo '</select>';
}
echo '</td>';
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'select_show_print_duration' );
echo '<td>';
echo '<input type="checkbox" name="print_duration" value="true" />';
echo '</td>';
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'select_show_expenses_overview' );
echo '<td>';
echo '<input type="checkbox" name="expenses_overview" value="true" />';
echo '</td>';
echo '</tr>';

$print_api->printRow();
echo '<td class="center" colspan="2">';
?>
   <input type="submit" name="formSubmit" class="button"
          value="<?php echo plugin_lang_get( 'select_confirm' ); ?>"/>
<?php
echo '</td>';
echo '</tr>';
echo '</form>';
echo '</table>';

html_page_bottom1();