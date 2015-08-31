<?php
form_security_validate( 'plugin_SpecificationManagement_config_update' );

access_ensure_global_level( config_get( 'SpecManagementAccessLevel' ) );
auth_reauthenticate();

$ShowInFooter = gpc_get_int( 'ShowInFooter', ON );

if ( plugin_config_get( 'ShowInFooter' ) != $ShowInFooter )
{
	plugin_config_set( 'ShowInFooter', $ShowInFooter );
}

$ShowFields = gpc_get_int( 'ShowFields', ON );

if ( plugin_config_get( 'ShowFields' ) != $ShowFields )
{
	plugin_config_set( 'ShowFields', $ShowFields );
}

$ShowMenu = gpc_get_int( 'ShowMenu', ON );

if ( plugin_config_get( 'ShowMenu' ) != $ShowMenu )
{
	plugin_config_set( 'ShowMenu', $ShowMenu );
}

$SpecManagementAccessLevel = gpc_get_int( 'SpecManagementAccessLevel' );

if ( plugin_config_get( 'SpecManagementAccessLevel' ) != $SpecManagementAccessLevel )
{
	plugin_config_set( 'SpecManagementAccessLevel', $SpecManagementAccessLevel );
}

form_security_purge( 'plugin_SpecificationManagement_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );