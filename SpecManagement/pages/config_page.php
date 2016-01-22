<?php
require_once SPECMANAGEMENT_CORE_URI . 'constant_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';

$database_api = new database_api();
$print_api = new print_api();

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'AccessLevel' ) );

html_page_top1( plugin_lang_get( 'config_title' ) );
html_page_top2();

print_manage_menu();

echo '<br/>';
echo '<form action="' . plugin_page( 'config_update' ) . '" method="post">';
echo form_security_field( 'plugin_SpecManagement_config_update' );

if ( $print_api->getMantisVersion() == '1.2.' )
{
   echo '<table align="center" class="width75" cellspacing="1">';
}
else
{
   echo '<div class="form-container">';
   echo '<table>';
}

$print_api->printFormTitle( 3, 'config_caption' );
$print_api->printRow();
echo '<td class="category" width="30%">';
echo '<span class="required">*</span>' . plugin_lang_get( 'config_accesslevel' );
echo '</td>';
echo '<td width="200px">';
echo '<select name="AccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'AccessLevel', PLUGINS_SPECMANAGEMENT_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$print_api->printRow();
echo '<td class="category" width="30%">';
echo '<span class="required">*</span>' . plugin_lang_get( 'config_readlevel' );
echo '</td>';
echo '<td width="200px">';
echo '<select name="ReadAccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'ReadAccessLevel', PLUGINS_SPECMANAGEMENT_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$print_api->printRow();
echo '<td class="category" width="30%">';
echo '<span class="required">*</span>' . plugin_lang_get( 'config_writelevel' );
echo '</td>';
echo '<td width="200px">';
echo '<select name="WriteAccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'WriteAccessLevel', PLUGINS_SPECMANAGEMENT_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'config_fields' );
$print_api->printRadioButton( 1, 'ShowFields' );
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'config_menu' );
$print_api->printRadioButton( 1, 'ShowMenu' );
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'config_footer' );
$print_api->printRadioButton( 1, 'ShowInFooter' );
echo '</tr>';

$print_api->printSpacer( 3 );

$print_api->printFormTitle( 3, 'config_document' );
$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'config_typeadd' );
$type = gpc_get_string( 'type', '' );
echo '<td>';
echo '<input type="text" id="type" name="type" size="15" maxlength="128" value="', $type, '">&nbsp';
echo '<input type="submit" name="addtype" class="button" value="' . plugin_lang_get( 'config_add' ) . '">';
echo '</td>';
echo '</tr>';

$print_api->printRow();
$print_api->printCategoryField( 1, 1, 'config_types' );
echo '<td>';


$types_rows = $database_api->getFullTypes();
foreach ( $types_rows as $types_row )
{
   $types[] = $types_row[1];
}

echo '<span class="select">';
echo '<select ' . helper_get_tab_index() . ' id="types" name="types">';
if ( !is_null( $types ) )
{
   foreach ( $types as $type )
   {
      echo '<option value="' . $type . '">' . $type . '</option>';
   }
}
echo '</select>&nbsp';
$new_type = gpc_get_string( 'newtype', '' );
echo '<input type="submit" name="deletetype" class="button" value="' . plugin_lang_get( 'config_del' ) . '">&nbsp';
echo '<input type="text" id="newtype" name="newtype" size="15" maxlength="128" value="', $new_type, '">&nbsp';
echo '<input type="submit" name="changetype" class="button" value="' . plugin_lang_get( 'config_change' ) . '">';

echo '</td>';
echo '</tr>';

$print_api->printSpacer( 3 );

echo '<tr>';
echo '<td class="center" colspan="3">';
echo '<input type="submit" name="change" class="button" value="' . lang_get( 'update_prefs_button' ) . '"/>&nbsp';
echo '<input type="submit" name="reset" class="button" value="' . lang_get( 'reset_prefs_button' ) . '"/>';
echo '</td>';
echo '</tr>';

echo '</table>';

if ( $print_api->getMantisVersion() != '1.2.' )
{
   echo '</div>';
}

echo '</form>';

html_page_bottom1();