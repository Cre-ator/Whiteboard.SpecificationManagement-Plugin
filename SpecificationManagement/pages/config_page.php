<?php
require_once( SPECIFICATIONMANAGEMENT_CORE_URI . 'constant_api.php' );
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecConfig_api.php';

$db_api = new SpecDatabase_api();
$sc_api = new SpecConfig_api();

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'AccessLevel' ) );

html_page_top1( plugin_lang_get( 'config_title' ) );
html_page_top2();

print_manage_menu();

echo '<br/>';
echo '<form action="' . plugin_page( 'config_update' ) . '" method="post">';
echo form_security_field( 'plugin_SpecificationManagement_config_update' );

if ( substr( MANTIS_VERSION, 0, 4 ) != '1.2.' )
{
   echo '<table align="center" class="width75" cellspacing="1">';
}
else
{
   echo '<div class="form-container">';
   echo '<table>';
}

$sc_api->printFormTitle( 3, 'config_caption' );
$sc_api->printTableRow();
echo '<td class="category" width="30%">';
echo '<span class="required">*</span>' . plugin_lang_get( 'config_accesslevel' );
echo '</td>';
echo '<td width="200px">';
echo '<select name="AccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'AccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$sc_api->printTableRow();
echo '<td class="category" width="30%">';
echo '<span class="required">*</span>' . plugin_lang_get( 'config_readlevel' );
echo '</td>';
echo '<td width="200px">';
echo '<select name="ReadAccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'ReadAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$sc_api->printTableRow();
echo '<td class="category" width="30%">';
echo '<span class="required">*</span>' . plugin_lang_get( 'config_writelevel' );
echo '</td>';
echo '<td width="200px">';
echo '<select name="WriteAccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'WriteAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$sc_api->printTableRow();
$sc_api->printCategoryField( 1, 1, 'fields' );
$sc_api->printRadioButton( 1, 'ShowFields' );
echo '</tr>';

$sc_api->printTableRow();
$sc_api->printCategoryField( 1, 1, 'menu' );
$sc_api->printRadioButton( 1, 'ShowMenu' );
echo '</tr>';

$sc_api->printTableRow();
$sc_api->printCategoryField( 1, 1, 'footer' );
$sc_api->printRadioButton( 1, 'ShowInFooter' );
echo '</tr>';

$sc_api->printSpacer( 3 );

$sc_api->printFormTitle( 3, 'config_document' );
$sc_api->printTableRow();
$sc_api->printCategoryField( 1, 1, 'config_typeadd' );
$type = gpc_get_string( 'type', '' );
echo '<td>';
echo '<input type="text" id="type" name="type" size="30" maxlength="128" value="', $type, '">';
echo '<input type="submit" name="addtype" class="button" value="' . plugin_lang_get( 'config_add' ) . '">';
echo '</td>';
echo '</tr>';

$sc_api->printTableRow();
$sc_api->printCategoryField( 1, 1, 'config_types' );
echo '<td>';

$types = $db_api->getTypes();

echo '<span class="select">';
echo '<select ' . helper_get_tab_index() . ' id="types" name="types">';
foreach ( $types as $type )
{
   echo '<option value="' . $type . '">' . $type . '</option>';
}
echo '</select>';
echo '<input type="submit" name="deletetype" class="button" value="' . plugin_lang_get( 'config_del' ) . '">';

echo '</td>';
echo '</tr>';

$sc_api->printSpacer( 3 );

echo '<tr>';
echo '<td class="center" colspan="3">';
echo '<input type="submit" name="change" class="button" value="' . lang_get( 'change_configuration' ) . '"/>';
echo '</td>';
echo '</tr>';

echo '</table>';

if ( substr( MANTIS_VERSION, 0, 4 ) != '1.2.' )
{
   echo '</div>';
}

echo '</form>';

html_page_bottom1();