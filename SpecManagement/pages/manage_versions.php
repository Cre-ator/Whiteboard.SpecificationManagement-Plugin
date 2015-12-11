<?php
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$database_api = new database_api();
$print_api = new print_api();

html_page_top1( plugin_lang_get( 'manversions_title' ) );
html_page_top2();

$print_api->print_plugin_menu();

echo '<div align="center">';
echo '<hr size="1" width="50%" />';
echo '<table class="width90" cellspacing="1">';

echo '<thead>';
$print_api->printFormTitle( 6, 'manversions_thead' );
echo '<tr class="row-category">';
$print_api->printTableHeadCol( 1, 'version' );
$print_api->printTableHeadCol( 1, 'released' );
$print_api->printTableHeadCol( 1, 'obsolete' );
$print_api->printTableHeadCol( 1, 'timestamp' );
echo '<th colspan="1">' . plugin_lang_get( 'manversions_thdoctype' ) . '</th>';
echo '<th colspan="1">' . plugin_lang_get( 'manversions_thaddchangetype' ) . '</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';

$versions = version_get_all_rows( helper_get_current_project() );

foreach ( $versions as $version )
{
   echo '<form action="' . plugin_page( 'manage_versions_update' ) . '" method="post">';
   $current_type = $database_api->getTypeString( $database_api->getTypeByVersion( $version['id'] ) );

   $print_api->printRow();
   echo '<td>' . string_display( version_full_name( $version['id'] ) ) . '</td>';
   echo '<input type="hidden" name="version_id" value="' . $version['id'] . '"/>';
   echo '<td>' . trans_bool( $version['released'] ) . '</td>';
   echo '<td>' . trans_bool( $version['obsolete'] ) . '</td>';
   echo '<td>' . date( config_get( 'complete_date_format' ), $version['date_order'] ) . '</td>';
   echo '<td>' . string_display( $current_type ) . '</td>';

   echo '<td>';
   $types = $database_api->getTypes();
   echo '<span class="select">';
   echo '<select ' . helper_get_tab_index() . ' id="types" name="types">';
   foreach ( $types as $type )
   {
      echo '<option value="' . $type . '">' . $type . '</option>';
   }
   echo '</select>&nbsp';
   echo '<input type="submit" name="assigntype" class="button" value="' . plugin_lang_get( 'manversions_assigntype' ) . '">';
   echo '</td>';

   echo '</tr>';
   echo '</form>';
}

echo '<form action="' . plugin_page( 'manage_versions_update' ) . '" method="post">';
echo '<tr>';
echo '<td colspan="6">';
echo '<input type="hidden" name="project_id" value="' . helper_get_current_project() . '"/>';
echo '<input type="text" name="new_version" size="32" maxlength="64"/>';
echo '<input type="submit" name="addversion" class="button" value="' . lang_get( 'add_version_button' ) . '"/>';
echo '</td>';
echo '</tr>';
echo '</form>';

echo '</tbody>';

echo '</table>';


html_page_bottom1();