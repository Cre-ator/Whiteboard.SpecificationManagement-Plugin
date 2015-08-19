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
         'EVENT_REPORT_BUG_FORM' => 'bug_report_fields',
         'EVENT_REPORT_BUG' => 'bug_report_data',
         'EVENT_UPDATE_BUG_FORM' => 'bug_update_fields',
         'EVENT_UPDATE_BUG' => 'bug_update_data',
      
         'EVENT_VIEW_BUG_DETAILS' => 'bug_view_fields',
         'EVENT_VIEW_BUG_EXTRA' => 'view_bug_extra',
         'EVENT_VIEW_BUGNOTES_START' => 'view_bugnotes_start',
         'EVENT_VIEW_BUGNOTE' => 'view_bugnote',
      
         'EVENT_BUGNOTE_ADD_FORM' => 'bugnote_add_form',
         'EVENT_BUGNOTE_ADD' => 'bugnote_add',
         'EVENT_BUGNOTE_EDIT_FORM' => 'bugnote_edit_form',
         'EVENT_BUGNOTE_EDIT' => 'bugnote_edit',
      
         'EVENT_MANAGE_PROJECT_CREATE_FORM' => 'project_create_form',
         'EVENT_MANAGE_PROJECT_CREATE' => 'project_update',
         'EVENT_MANAGE_PROJECT_UPDATE_FORM' => 'project_update_form',
         'EVENT_MANAGE_PROJECT_UPDATE' => 'project_update',
      
         'EVENT_LAYOUT_RESOURCES' => 'event_layout_resources',
      
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
   	return array
   	(
   	   array('AddColumnSQL', array(db_get_table('bug'), "
            requirement    C(128)     DEFAULT \" '' \",
            resource       C(128)     DEFAULT \" '' \"
            ")
   	   )
   	);
   }
   
   function footer()
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);
      
      if (plugin_config_get('ShowInFooter') == 1 && $t_user_has_level)
      {
         return '<address>' . $this->name . ' ' . $this->version . ' Copyright &copy; 2015 by ' . $this->author . '</address>';
      }
      return '';
   }
   
   function bug_view_fields()
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);
      
      $f_bug_id = gpc_get_int('id');
      $t_bug = bug_get($f_bug_id, true);
      
      $t_requirement = string_display_line($t_bug->requirement);
      $t_resource = string_display_line($t_bug->resource);
      
      if (plugin_config_get('ShowFields') == 1 && $t_user_has_level)
      {
         echo '<tr>';
         echo '<th class="requirement category">', plugin_lang_get('bug_add_form_specification_req'), '</th>';
         echo '<td class="requirement" colspan="5">', $t_requirement, '</td>';
         echo '</tr>';
         
         echo '<tr>';
         echo '<th class="resource category">', plugin_lang_get('bug_add_form_specification_src'), '</th>';
         echo '<td class="resource" colspan="5">', $t_resource, '</td>';
         echo '</tr>';
      }
      return '';
   }
   
   function bug_update_fields()
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);
      
      $f_bug_id = gpc_get_int('bug_id');
      $t_bug = bug_get($f_bug_id, true);

      $t_requirement = string_attribute($t_bug->requirement);
      $t_resource = string_textarea($t_bug->resource);
      
      if (plugin_config_get('ShowFields') == 1 && $t_user_has_level)
      {
         echo '<tr>';
         echo '<th class="category"><label for="requirement">' . plugin_lang_get('bug_add_form_specification_req') . '</label></th>';
         echo '<td colspan="5"><input ', helper_get_tab_index(), ' type="text" id="requirement" name="requirement" size="105" maxlength="128" value="', $t_requirement, '" />';
         echo '</td></tr>';
         
         echo '<tr>';
         echo '<th class="category"><label for="resource">' . plugin_lang_get('bug_add_form_specification_src') . '</label></th>';
         echo '<td colspan="5"><input ', helper_get_tab_index(), ' type="text" id="resource" name="resource" size="105" maxlength="128" value="', $t_resource, '" />';
         echo '</td></tr>';
      }
      return '';
   }
   
   function bug_update_data()
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);
      
      $f_bug_id = gpc_get_int('bug_id');
      $t_bug = bug_get($f_bug_id, true);

      $f_requirement = gpc_get_string('requirement', $t_bug->requirement);
      $f_resource = gpc_get_string('resource', $t_bug->resource);
      
      $t_query = 'UPDATE mantis_bug_table';
      $t_query = $t_query .= ' SET requirement =' . '"' . $f_requirement . '",';
      $t_query = $t_query .= ' resource =' . '"' . $f_resource . '"';
      $t_query = $t_query .= ' WHERE mantis_bug_table.id = ' . $f_bug_id;
      $t_exec = db_query($t_query);
   }

   function bug_report_fields()
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);

      $f_requirement = gpc_get_string('requirement', '');
      $f_resource = gpc_get_string('resource', '');
      
      if (plugin_config_get('ShowFields') == 1 && $t_user_has_level)
      {
         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get('bug_add_form_specification_req') . '</span></label>';
         echo '<span class="input">';
         echo '<input ', helper_get_tab_index(), 'type="text" id="requirement" name="requirement" size="105" maxlength="128" value="', string_attribute($f_requirement), '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';
         
         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get('bug_add_form_specification_src') . '</span></label>';
         echo '<span class="input">';
         echo '<input ', helper_get_tab_index(), 'type="text" id="resource" name="resource" size="105" maxlength="128" value="', string_attribute($f_resource), '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';
      }
   }

   function bug_report_data($event, BugData $f_bug)
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('SpecificationManagementAccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT);
            
      $f_bug_id = $f_bug->id;
      $t_bug = bug_get($f_bug_id, true);
      
      $f_requirement = gpc_get_string('requirement', $t_bug->requirement);
      $f_resource = gpc_get_string('resource', $t_bug->resource);
      
      $t_query = 'UPDATE mantis_bug_table';
      $t_query = $t_query .= ' SET requirement =' . '"' . $f_requirement . '",';
      $t_query = $t_query .= ' resource =' . '"' . $f_resource . '"';
      $t_query = $t_query .= ' WHERE mantis_bug_table.id = ' . $f_bug_id;
      $t_exec = db_query($t_query);
   }
   
   function menu()
   {
      $t_project_id = helper_get_current_project();
      $t_user_id = auth_get_current_user_id();
      $t_user_has_level = user_get_access_level($t_user_id, $t_project_id) >= plugin_config_get('UserProjectAccessLevel', PLUGINS_USERPROJECTVIEWVIEW_THRESHOLD_LEVEL_DEFAULT);
      
      if (plugin_config_get('ShowMenu') == 1 && $t_user_has_level)
      {
         return '<a href="' . plugin_page('SpecManager') . '">' . plugin_lang_get('menu_title') . '</a>';
      }
      return '';
   }
}