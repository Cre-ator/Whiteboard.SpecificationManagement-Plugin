<?php
include SPECMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECMANAGEMENT_CORE_URI . 'SpecPrint_api.php';

$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();

$t_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
$types = $sd_api->getTypes();
$document_type = $sd_api->getFirstType();
$sources = array();

/**
 * false if no data was sent
 * true if type was specified and sent
 */
$post = false;
if ( !empty( $_POST['types'] ) )
{
   $post = true;
}

html_page_top1( plugin_lang_get( 'select_doc_title' ) );
html_page_top2();

$sp_api->print_plugin_menu();

echo '<div align="center">';
echo '<hr size="1" width="50%" />';

echo '<table class="width50" cellspacing="1">';
echo '<tr class="row-category">';
echo '<th>' . plugin_lang_get( 'select_doc' ) . '</th>';
echo '</tr>';

$sp_api->printRow();
echo '<td class="center">';

echo '<form method="post" name="form_set_requirement" action="' . plugin_page( 'ChooseDocument' ) . '">';
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
echo '<option value="blank"';
if ( $post && $_POST['types'] == 'blank' )
{
   echo ' selected="selected"';
}
echo '>' . plugin_lang_get( 'select_blankdoc' ) . '</option>';
echo '</select>';
echo '<input type="submit" class="button-small" value="' . lang_get( 'switch' ) . '" />';

if ( $post )
{
   $document_type = $sd_api->getTypeId( $_POST['types'] );
}

echo '</form>';
echo '<form method="post" name="form_set_source" action="' . plugin_page( 'Editor' ) . '">';
if ( $document_type != null || $_POST['types'] != 'blank' )
{
   $source_ids = $sd_api->getSources( $document_type, $t_project_id );

   echo '<select name="version">';
   foreach ( $source_ids as $source )
   {
      echo '<option value="' . $source . '"';
      if ( $post && $_POST['types'] == $source )
      {
         echo ' selected="selected"';
      }
      echo '>' . $source . '</option>';
   }
   echo '</select>';
}

echo '</td>';
echo '</tr>';

$sp_api->printRow();
echo '<td class="center">';

?>
   <input type="submit" name="formSubmit" class="button"
          value="<?php echo plugin_lang_get( 'select_confirm' ); ?>"/>
<?php
echo '</td>';
echo '</tr>';
echo '</form>';
echo '</table>';

html_page_bottom1();