<?php

class SpecManagementPlugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'Specification Management';
      $this->description = 'Generate and manage your own specified documents';
      $this->page = 'config_page';

      $this->version = '1.1.40';
      $this->requires = array
      (
         'MantisCore' => '1.2.0, <= 1.3.99',
      );

      $this->author = 'Stefan Schwarz, Rainer Dierck';
      $this->contact = '';
      $this->url = '';
   }

   function hooks()
   {
      if ( substr( MANTIS_VERSION, 0, 4 ) > '1.2.' )
      {
         $hooks = array
         (
            'EVENT_LAYOUT_PAGE_FOOTER' => 'footer',
            'EVENT_REPORT_BUG_FORM' => 'bugViewFields',
            'EVENT_REPORT_BUG' => 'bugUpdateData',
            'EVENT_UPDATE_BUG_FORM' => 'bugViewFields',
            'EVENT_UPDATE_BUG' => 'bugUpdateData',
            'EVENT_BUG_ACTION' => 'actiongroupUpdateData',
            'EVENT_BUG_DELETED' => 'deleteBugReference',
            'EVENT_VIEW_BUG_DETAILS' => 'bugViewFields',
            'EVENT_MENU_MAIN' => 'menu',
            'EVENT_MANAGE_VERSION_DELETE' => 'deleteVersion'
         );
      }
      else
      {
         $hooks = array
         (
            'EVENT_LAYOUT_PAGE_FOOTER' => 'footer',
            'EVENT_REPORT_BUG_FORM' => 'bugViewFields',
            'EVENT_REPORT_BUG' => 'bugUpdateData',
            'EVENT_UPDATE_BUG_FORM' => 'bugViewFields',
            'EVENT_UPDATE_BUG' => 'bugUpdateData',
            'EVENT_BUG_ACTION' => 'actiongroupUpdateData',
            'EVENT_BUG_DELETED' => 'deleteBugReference',
            'EVENT_VIEW_BUG_DETAILS' => 'bugViewFields',
            'EVENT_MENU_MAIN' => 'menu'
         );
      }
      return $hooks;
   }

   function init()
   {
      $t_core_path = config_get_global( 'plugin_path' )
         . plugin_get_current()
         . DIRECTORY_SEPARATOR
         . 'core'
         . DIRECTORY_SEPARATOR;
      require_once( $t_core_path . 'specmanagement_constant_api.php' );
   }

   function config()
   {
      return array
      (
         'ShowInFooter' => ON,
         'ShowFields' => ON,
         'ShowUserMenu' => ON,
         'ShowMenu' => ON,
         'ShowDuration' => ON,
         'AccessLevel' => ADMINISTRATOR,
         'ReadAccessLevel' => ADMINISTRATOR,
         'WriteAccessLevel' => ADMINISTRATOR,
         'ShowSpecStatCols' => OFF,
         'CAmount' => 1
      );
   }

   function schema()
   {
      return array
      (
         array
         (
            'CreateTableSQL', array( plugin_table( 'src' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id          I       NOTNULL UNSIGNED,
            p_version_id    I       UNSIGNED,
            work_package    C(250)  DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'ptime' ), "
            id       I    NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id   I    NOTNULL UNSIGNED,
            time     I    NOTNULL UNSIGNED
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'vers' ), "
            id          I   NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            project_id  I   NOTNULL UNSIGNED,
            version_id  I   NOTNULL UNSIGNED,
            type_id     I   UNSIGNED
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'type' ), "
            id           I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            type         C(250)  NOTNULL DEFAULT '',
            opt          C(250)  DEFAULT ''
            " )
         )
      );
   }

   /**
    * Check if user has level greater or equal then plugin access level
    *
    * @return bool - Userlevel is greater or equal then plugin access level
    */
   function getUserHasLevel()
   {
      $project_id = helper_get_current_project();
      $user_id = auth_get_current_user_id();

      return user_get_access_level( $user_id, $project_id ) >= plugin_config_get( 'AccessLevel', PLUGINS_SPECMANAGEMENT_THRESHOLD_LEVEL_DEFAULT );
   }

   function getReadLevel()
   {
      $project_id = helper_get_current_project();
      $user_id = auth_get_current_user_id();

      return user_get_access_level( $user_id, $project_id ) >= plugin_config_get( 'ReadAccessLevel', PLUGINS_SPECMANAGEMENT_READ_LEVEL_DEFAULT );
   }

   function getWriteLevel()
   {
      $project_id = helper_get_current_project();
      $user_id = auth_get_current_user_id();

      return user_get_access_level( $user_id, $project_id ) >= plugin_config_get( 'WriteAccessLevel', PLUGINS_SPECMANAGEMENT_WRITE_LEVEL_DEFAULT );
   }

   /**
    * Show plugin info in mantis footer
    *
    * @return null|string
    */
   function footer()
   {
      if ( plugin_config_get( 'ShowInFooter' ) && $this->getUserHasLevel() )
      {
         return '<address>' . $this->name . ' ' . $this->version . ' Copyright &copy; 2015 by ' . $this->author . '</address>';
      }
      return null;
   }

   /**
    * Add custom plugin fields to bug-specific sites (bug_report, bug_update, bug_view)
    *
    * @param $event
    * @return null
    */
   function bugViewFields( $event )
   {
      require_once( SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php' );
      require_once( SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php' );
      $specmanagement_database_api = new specmanagement_database_api();
      $specmanagement_print_api = new specmanagement_print_api();
      $bug_id = null;
      $type = null;
      $work_package = null;
      $ptime = null;

      switch ( $event )
      {
         case 'EVENT_UPDATE_BUG_FORM':
            $bug_id = gpc_get_int( 'bug_id' );
            break;
         case 'EVENT_VIEW_BUG_DETAILS':
            $bug_id = gpc_get_int( 'id' );
            break;
      }

      if ( $bug_id != null )
      {
         $source_obj = $specmanagement_database_api->get_source_row( $bug_id );
         $work_package = $source_obj[3];
         $ptime_obj = $specmanagement_database_api->get_ptime_row( $bug_id );
         $ptime = $ptime_obj[2];

         if ( 0 == strlen( bug_get_field( $bug_id, 'target_version' ) ) )
         {
            $specmanagement_database_api->update_source_version( $bug_id, null );
         }

         $p_version_id = $source_obj[2];
         if ( !is_null( $p_version_id ) )
         {
            $version_obj = $specmanagement_database_api->get_version_row_by_primary( $p_version_id );
            $type_id = $version_obj[3];
            $type = $specmanagement_database_api->get_type_string( $type_id );
         }
      }

      if ( plugin_config_get( 'ShowFields' ) )
      {
         switch ( $event )
         {
            case 'EVENT_VIEW_BUG_DETAILS':
               if ( $this->getReadLevel() || $this->getWriteLevel() || $this->getUserHasLevel() )
               {
                  $specmanagement_print_api->printBugViewFields( $type, $work_package, $ptime );
               }
               break;
            case 'EVENT_REPORT_BUG_FORM':
               if ( $this->getWriteLevel() || $this->getUserHasLevel() )
               {
                  $specmanagement_print_api->printBugReportFields();
               }
               break;
            case 'EVENT_UPDATE_BUG_FORM':
               if ( $this->getWriteLevel() || $this->getUserHasLevel() )
               {
                  $specmanagement_print_api->printBugUpdateFields( $type, $work_package, $ptime );
               }
               break;
         }
      }
      return null;
   }

   /**
    * Update custom plugin fields
    *
    * @param $event
    * @param BugData $bug
    */
   function bugUpdateData( $event, BugData $bug )
   {
      require_once( SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php' );
      $specmanagement_database_api = new specmanagement_database_api();
      $version_id = null;
      $type_id = null;
      $p_version_id = null;
      $bug_id = $bug->id;
      $project_id = helper_get_current_project();
      $ptime = gpc_get_string( 'ptime', '0' );

      $type = gpc_get_string( 'types', '' );
      $target_version = bug_get_field( $bug_id, 'target_version' );
      $work_package = preg_replace( '/\/\/+/', '/', gpc_get_string( 'work_package', '' ) );

      if ( !is_null( $target_version ) )
      {
         $version_id = version_get_id( $target_version );
         $version_obj = $specmanagement_database_api->get_plugin_version_row_by_version_id( $version_id );
         $p_version_id = $version_obj[0];
         $type_id = $specmanagement_database_api->get_type_id( $type );
      }

      switch ( $event )
      {
         case 'EVENT_REPORT_BUG':
            $specmanagement_database_api->insert_version_row( $project_id, $version_id, $type_id );
            $specmanagement_database_api->insert_source_row( $bug_id, $p_version_id, $work_package );
            $specmanagement_database_api->insert_ptime_row( $bug_id, $ptime );
            break;
         case 'EVENT_UPDATE_BUG':
            if ( strlen( $work_package ) == 0 )
            {
               $work_package = $specmanagement_database_api->get_workpackage_by_bug_id( $bug_id );
            }
            $specmanagement_database_api->update_version_row( $project_id, $version_id, $type_id );
            $specmanagement_database_api->update_source_row( $bug_id, $p_version_id, $work_package );
            $specmanagement_database_api->update_ptime_row( $bug_id, $ptime );
            break;
      }
   }

   /**
    * Updates the version and associated type of the document if several issues
    * are updated
    *
    * @param $event
    * @param $event_type
    * @param $bug_id
    */
   function actiongroupUpdateData( $event, $event_type, $bug_id )
   {
      require_once( SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php' );
      $specmanagement_database_api = new specmanagement_database_api();
      if ( $event_type == 'UP_TARGET_VERSION' )
      {
         $target_version = gpc_get_string( 'target_version', null );
         $p_version_id = null;

         if ( !( is_null( $target_version ) || $target_version == '' ) )
         {
            $version_id = version_get_id( $target_version );
            $version_obj = $specmanagement_database_api->get_plugin_version_row_by_version_id( $version_id );
            $p_version_id = $version_obj[0];
         }
         $specmanagement_database_api->update_source_version( $bug_id, $p_version_id );
      }
   }

   /**
    * If the whiteboard menu plugin isnt installed, show the specificationmanagement menu instead
    *
    * @return null|string
    */
   function menu()
   {
      if ( !plugin_is_installed( 'WhiteboardMenu' ) && plugin_config_get( 'ShowMenu' ) && $this->getUserHasLevel() )
      {
         return '<a href="' . plugin_page( 'choose_document' ) . '">' . plugin_lang_get( 'menu_title' ) . '</a>';
      }
      return null;
   }

   /**
    * Trigger the removal of plugin version data if a mantis version was removed
    */
   function deleteVersion()
   {
      require_once( SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php' );
      $specmanagement_database_api = new specmanagement_database_api();
      $version_id = gpc_get_int( 'version_id' );
      $plugin_version_row = $specmanagement_database_api->get_plugin_version_row_by_version_id( $version_id );
      $p_version_id = $plugin_version_row[0];
      $specmanagement_database_api->update_source_version_set_null( $p_version_id );
      $specmanagement_database_api->delete_version_row( $version_id );
   }

   /**
    * Trigger the removal of plugin data if a bug was removed
    *
    * @param $event
    * @param $bug_id
    */
   function deleteBugReference( $event, $bug_id )
   {
      require_once( SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php' );
      $specmanagement_database_api = new specmanagement_database_api();
      $specmanagement_database_api->delete_source_row_by_bug( $bug_id );
      $specmanagement_database_api->delete_ptime_row( $bug_id );
   }
}
