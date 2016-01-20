<?php
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';

auth_reauthenticate();

$database_api = new database_api();
$version_id = gpc_get_int( 'version_id' );
$version = version_get( $version_id );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $version->project_id );

helper_ensure_confirmed( lang_get( 'version_delete_sure' ) .
   '<br/>' . lang_get( 'version_label' ) . lang_get( 'word_separator' ) . string_display_line( $version->version ),
   lang_get( 'delete_version_button' ) );

$plugin_version_row = $database_api->getVersionRowByVersionId( $version_id );
$p_version_id = $plugin_version_row[0];

$database_api->updateSourceVersionSetNull( $p_version_id );
$database_api->deleteVersionRow( $version_id );
version_remove( $version_id );

print_successful_redirect( plugin_page( 'manage_versions', true ) );