<?php
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecMenu_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecPrint_api.php';

$sm_api = new SpecMenu_api();
$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();

$types = $sd_api->getTypes();

html_page_top1( plugin_lang_get( 'page_title' ) );
html_page_top2();

$sm_api->printWhiteboardMenu();
$sm_api->printPluginMenu();

echo '<div align="center">';
echo '<hr size="1" width="50%" />';

echo '<table class="width50" cellspacing="1">';
echo '<tr class="row-category">';
echo '<th>' . plugin_lang_get( 'select_doc' ) . '</th>';
echo '</tr>';

echo '<form action="' . plugin_page( 'Editor' ) . '" method="post">';
$sp_api->printRow();
echo '<td class="center">';
echo '<select ' . helper_get_tab_index() . 'id="types" name="types">';
foreach ( $types as $type )
{
   echo '<option value="' . $type . '">' . string_html_specialchars( $type ) . '</option>';
}
echo '<option value="blank">' . plugin_lang_get( 'select_blandoc' ) . '</option>';
echo '</select>';
echo '</td>';
echo '</tr>';

echo '<td class="center">';
?>
   <input type="submit" name="formSubmit" class="button"
          value="<?php echo plugin_lang_get( 'select_confirm' ); ?>"/>
<?php
echo '</td>';
echo '</tr>';
echo '</table>';

html_page_bottom1();