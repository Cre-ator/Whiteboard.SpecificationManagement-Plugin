<?php
form_security_validate('plugin_SpecificationManagement_config_update');

access_ensure_global_level(config_get('SpecificationManagementAccessLevel'));
auth_reauthenticate();

$ShowInFooter = gpc_get_int('ShowInFooter', ON);

if(plugin_config_get('ShowInFooter') != $ShowInFooter)
{
   plugin_config_set('ShowInFooter', $ShowInFooter);
}

$ShowFields = gpc_get_int('ShowFields', ON);

if(plugin_config_get('ShowFields') != $ShowFields)
{
   plugin_config_set('ShowFields', $ShowFields);
}

$ShowMenu = gpc_get_int('ShowMenu', ON);

if(plugin_config_get('ShowMenu') != $ShowMenu)
{
   plugin_config_set('ShowMenu', $ShowMenu);
}

$SpecificationManagementAccessLevel = gpc_get_int('SpecificationManagementAccessLevel');

if(plugin_config_get('SpecificationManagementAccessLevel') != $SpecificationManagementAccessLevel)
{
   plugin_config_set('SpecificationManagementAccessLevel', $SpecificationManagementAccessLevel);
}

form_security_purge('plugin_SpecificationManagement_config_update');

print_successful_redirect(plugin_page('config_page', true));