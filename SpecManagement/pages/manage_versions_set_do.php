<?php
auth_reauthenticate();

$version_id = gpc_get_int( 'version_id' );
$version = version_get( $version_id );

$new_date_order = gpc_get_string( 'date_order' );
$new_version = gpc_get_string( 'new_version' );
$new_description = gpc_get_string( 'description' );
$new_released = gpc_get_bool( 'released' );
$new_obsolete = gpc_get_bool( 'obsolete' );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $version->project_id );

if ( is_blank( $new_version ) )
{
   trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$new_version = trim( $new_version );

$version->version = $new_version;
$version->description = $new_description;
$version->released = $new_released ? VERSION_RELEASED : VERSION_FUTURE;
$version->obsolete = $new_obsolete;
$version->date_order = $new_date_order;

version_update( $version );
event_signal( 'EVENT_MANAGE_VERSION_UPDATE', array( $version->id ) );//form_security_purge( 'manage_versions_set_do' );

print_successful_redirect( plugin_page( 'manage_versions', true ) );