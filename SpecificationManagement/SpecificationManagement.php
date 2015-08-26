<?php

class SpecificationManagementPlugin extends MantisPlugin
{	
   function register()
   {
      $this->name        = 'SpecificationManagement';
      $this->description = 'Adds fields for management specifications to bug reports.';
      $this->page        = 'config_page';

      $this->version     = '1.0.1';
      $this->requires    = array
      (
         'MantisCore' => '1.2.0, <= 1.3.1'
      );

      $this->author      = 'Stefan Schwarz';
      $this->contact     = '';
      $this->url         = '';
   }
      
   function hooks()
   {
      $hooks = array
      (
         'EVENT_LAYOUT_PAGE_FOOTER' => 'footer',
      		
         'EVENT_REPORT_BUG_FORM' => 'bugreportFields',
         'EVENT_REPORT_BUG' => 'bugreportData',
      		
         'EVENT_UPDATE_BUG_FORM' => 'bugupdateFields',
         'EVENT_UPDATE_BUG' => 'bugupdateData',
      
         'EVENT_VIEW_BUG_DETAILS' => 'bugviewFields',

         'EVENT_MENU_MAIN' => 'menu'
      );
      return $hooks;
   }
   
   function init()
   {
      $t_core_path = config_get_global('plugin_path')
                   . plugin_get_current()
                   . DIRECTORY_SEPARATOR
                   . 'core'
                   . DIRECTORY_SEPARATOR;
      require_once($t_core_path . 'constant_api.php');
   }

   function config()
   {
      return array
      (
         'ShowInFooter' => ON,
         'ShowFields' => ON,
         'ShowUserMenu' => ON,
         'ShowMenu' => ON,
         'SpecificationManagementAccessLevel' => ADMINISTRATOR
      );
   }
   
   function schema()
   {
   	if (substr(MANTIS_VERSION, 0, 4) == '1.2.')
   	{
   		return array
   		(
				array('AddColumnSQL', array(db_get_table('mantis_bug_table'), "
	            requirement    C(128)     DEFAULT \" '' \",
	            resource       C(128)     DEFAULT \" '' \"
	            ")
				)
   		);
   	}
   	else
   	{
   		return array
   		(
				array('AddColumnSQL', array(db_get_table('bug'), "
	            requirement    C(128)     DEFAULT \" '' \",
	            resource       C(128)     DEFAULT \" '' \"
	            ")
				)
   		);
   	}
   }
   
   function getUserHasLevel()
   {
   	$projectId = helper_get_current_project();
   	$userId = auth_get_current_user_id();
   	
   	return user_get_access_level($userId, $projectId) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);
   }
   
   function footer()
   {
      if (plugin_config_get('ShowInFooter') == 1 && $this->getUserHasLevel())
      {
         return '<address>' . $this->name . ' ' . $this->version . ' Copyright &copy; 2015 by ' . $this->author . '</address>';
      }
      return '';
   }
   
   function bugviewFields()
   {
      $bugId = gpc_get_int('id');
      $bug = bug_get($bugId, true);
      
      $requirement = string_display_line($bug->requirement);
      $resource = string_display_line($bug->resource);
      
      if (plugin_config_get('ShowFields') == 1 && $this->getUserHasLevel())
      {
      	if (substr(MANTIS_VERSION, 0, 4) == '1.2.')
      	{
      		echo '<tr class="row-1">';
      		echo '<td class="category">', plugin_lang_get('bug_add_form_specification_req'), '</td>';
      		echo '<td colspan="5">', $requirement, '</td>';
      		echo '</tr>';
      	
      		echo '<tr class="row-1">';
      		echo '<td class="category">', plugin_lang_get('bug_add_form_specification_src'), '</td>';
      		echo '<td colspan="5">', $resource, '</td>';
      		echo '</tr>';
      	}
      	else
      	{
	         echo '<tr>';
	         echo '<th class="requirement category">', plugin_lang_get('bug_add_form_specification_req'), '</th>';
	         echo '<td class="requirement" colspan="5">', $requirement, '</td>';
	         echo '</tr>';
	         
	         echo '<tr>';
	         echo '<th class="resource category">', plugin_lang_get('bug_add_form_specification_src'), '</th>';
	         echo '<td class="resource" colspan="5">', $resource, '</td>';
	         echo '</tr>';
      	}
      }
      return '';
   }
   
   function bugupdateFields()
   {
      $bugId = gpc_get_int('bug_id');
      $bug = bug_get($bugId, true);

      $requirement = string_attribute($bug->requirement);
      $resource = string_textarea($bug->resource);
      
      if (plugin_config_get('ShowFields') == 1 && $this->getUserHasLevel())
      {
      	if (substr(MANTIS_VERSION, 0, 4) == '1.2.')
      	{
      		echo '<tr class="row-1">';
      		echo '<td class="category">' . plugin_lang_get('bug_add_form_specification_req') . '</td>';
      		echo '<td colspan="5"><input ', helper_get_tab_index(), ' type="text" id="requirement" name="requirement" size="105" maxlength="128" value="', $requirement, '" /></td>';
      		echo '</tr>';
      		
      		echo '<tr class="row-1">';
      		echo '<td class="category">' . plugin_lang_get('bug_add_form_specification_src') . '</td>';
      		echo '<td colspan="5"><input ', helper_get_tab_index(), ' type="text" id="resource" name="resource" size="105" maxlength="128" value="', $resource, '" /></td>';
      		echo '</tr>';
      	}
      	else
      	{
      		echo '<tr>';
      		echo '<th class="category"><label for="requirement">' . plugin_lang_get('bug_add_form_specification_req') . '</label></th>';
      		echo '<td colspan="5"><input ', helper_get_tab_index(), ' type="text" id="requirement" name="requirement" size="105" maxlength="128" value="', $requirement, '" /></td>';
      		echo '</tr>';
      		 
      		echo '<tr>';
      		echo '<th class="category"><label for="resource">' . plugin_lang_get('bug_add_form_specification_src') . '</label></th>';
      		echo '<td colspan="5"><input ', helper_get_tab_index(), ' type="text" id="resource" name="resource" size="105" maxlength="128" value="', $resource, '" /></td>';
      		echo '</tr>';
      	}
      }
      return '';
   }
   
   function bugupdateData()
   {
   	include SPECIFICATIONMANAGEMENT_CORE_URI . 'PluginManager.php';
   	
   	$pluginManager = new PluginManager();
      $bugId = gpc_get_int('bug_id');
      $bug = bug_get($bugId, true);

      $requirement = gpc_get_string('requirement', $bug->requirement);
      $resource = gpc_get_string('resource', $bug->resource);
      
      $pluginManager->updateReqResInBugtableByBugId($requirement, $resource, $bugId);
   }

   function bugreportFields()
   {
      $requirement = gpc_get_string('requirement', '');
      $resource = gpc_get_string('resource', '');
      
      if (plugin_config_get('ShowFields') == 1 && $this->getUserHasLevel())
      {     	
      	if (substr(MANTIS_VERSION, 0, 4) == '1.2.')
      	{
      		echo '<tr class="row-1">';
      		echo '<td class="category">' . plugin_lang_get('bug_add_form_specification_req') . '</td>';
      		echo '<td><input ', helper_get_tab_index(), ' type="text" id="requirement" name="requirement" size="105" maxlength="128" value="', $requirement, '" /></td>';
      		echo '</tr>';

      		echo '<tr class="row-1">';
      		echo '<td class="category">' . plugin_lang_get('bug_add_form_specification_src') . '</td>';
      		echo '<td><input ', helper_get_tab_index(), ' type="text" id="resource" name="resource" size="105" maxlength="128" value="', $resource, '" /></td>';
      		echo '</tr>';
      	}
      	else
      	{
      		echo '<div class="field-container">';
      		echo '<label><span>' . plugin_lang_get('bug_add_form_specification_req') . '</span></label>';
      		echo '<span class="input">';
      		echo '<input ', helper_get_tab_index(), 'type="text" id="requirement" name="requirement" size="105" maxlength="128" value="', string_attribute($requirement), '" />';
      		echo '</span>';
      		echo '<span class="label-style"></span>';
      		echo '</div>';
      		 
      		echo '<div class="field-container">';
      		echo '<label><span>' . plugin_lang_get('bug_add_form_specification_src') . '</span></label>';
      		echo '<span class="input">';
      		echo '<input ', helper_get_tab_index(), 'type="text" id="resource" name="resource" size="105" maxlength="128" value="', string_attribute($resource), '" />';
      		echo '</span>';
      		echo '<span class="label-style"></span>';
      		echo '</div>';
      	}
      }
      return '';
   }

   function bugreportData($event, BugData $incBug)
   {
   	include SPECIFICATIONMANAGEMENT_CORE_URI . 'PluginManager.php';
		
   	$pluginManager = new PluginManager();
   	$bugId = $incBug->id;
      $bug = bug_get($bugId, true);
      
      $requirement = gpc_get_string('requirement', $bug->requirement);
      $resource = gpc_get_string('resource', $bug->resource);
      
      $pluginManager->updateReqResInBugtableByBugId($requirement, $resource, $bugId);
   }
   
   function menu()
   {
      if (plugin_config_get('ShowMenu') == 1 && $this->getUserHasLevel())
      {
         return '<a href="' . plugin_page('SpecManager') . '">' . plugin_lang_get('menu_title') . '</a>';
      }
      return '';
   }
}