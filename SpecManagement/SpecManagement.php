<?php

class SpecManagementPlugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'Specification Management';
      $this->description = 'Adds fields for management specs to bug reports.';
      $this->page = 'config_page';

      $this->version = '1.0.11';
      $this->requires = array
      (
         'MantisCore' => '1.2.0, <= 1.3.99',
      );

      $this->author = 'Stefan Schwarz';
      $this->contact = '';
      $this->url = '';
   }

   function hooks()
   {
      $hooks = array
      (
         'EVENT_LAYOUT_PAGE_FOOTER' => 'footer',

         'EVENT_REPORT_BUG_FORM' => 'bugViewFields',
         'EVENT_REPORT_BUG' => 'bugUpdateData',

         'EVENT_UPDATE_BUG_FORM' => 'bugViewFields',
         'EVENT_UPDATE_BUG' => 'bugUpdateData',

         'EVENT_VIEW_BUG_DETAILS' => 'bugViewFields',

         'EVENT_MENU_MAIN' => 'menu'
      );
      return $hooks;
   }

   function init()
   {
      $t_core_path = config_get_global( 'plugin_path' )
         . plugin_get_current()
         . DIRECTORY_SEPARATOR
         . 'core'
         . DIRECTORY_SEPARATOR;
      require_once( $t_core_path . 'constant_api.php' );
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
         'DeinstallFull' => OFF,
         'AccessLevel' => ADMINISTRATOR
      );
   }

   function schema()
   {
      return array
      (
         array
         (
            'CreateTableSQL', array( plugin_table( 'req' ), "
            id          I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id      I       NOTNULL UNSIGNED,
            type_id     I       NOTNULL UNSIGNED
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'src' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id          I       NOTNULL UNSIGNED,
            requirement_id  I       NOTNULL UNSIGNED,
            version         C(250)  DEFAULT '',
            work_package    C(250)  DEFAULT '',
            type_id         I       NOTNULL UNSIGNED
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'type' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            type            C(250)  NOTNULL DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'ptime' ), "
            id          I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id      I       NOTNULL UNSIGNED,
            time        I       NOTNULL UNSIGNED
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
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'database_api.php';
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'print_api.php';
      $database_api = new database_api();
      $print_api = new print_api();

      $bug_id = null;
      $version = null;
      $work_package = null;
      $type = null;
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
         $requirement_obj = $database_api->getReqRow( $bug_id );
         $source_obj = $database_api->getSourceRow( $bug_id );
         $ptime_obj = $database_api->getPtimeRow( $bug_id );

         $type = $database_api->getTypeString( $requirement_obj[2] );
         $version = $source_obj[3];
         $work_package = $source_obj[4];
         $ptime = $ptime_obj[2];
      }

      $types = $database_api->getTypes();

      if ( plugin_config_get( 'ShowFields' ) )
      {
         switch ( $event )
         {
            case 'EVENT_VIEW_BUG_DETAILS':
               if ( $this->getReadLevel() || $this->getWriteLevel() )
               {
                  $print_api->printBugViewFields( $type, $version, $work_package, $ptime );
               }
               break;
            case 'EVENT_REPORT_BUG_FORM':
               if ( $this->getWriteLevel() )
               {
                  $version = gpc_get_string( 'version', '' );
                  $print_api->printBugReportFields( $types, $version, $work_package, $ptime );
               }
               break;
            case 'EVENT_UPDATE_BUG_FORM':
               if ( $this->getWriteLevel() )
               {
                  $print_api->printBugUpdateFields( $type, $types, $version, $work_package, $ptime );
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
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'database_api.php';
      $database_api = new database_api();

      $bug_id = $bug->id;

      $requirement_obj = $database_api->getReqRow( $bug_id );
      $source_obj = $database_api->getSourceRow( $bug_id );
      $ptime_obj = $database_api->getPtimeRow( $bug_id );

      $type = gpc_get_string( 'types', $database_api->getTypeString( $requirement_obj[2] ) );
      $type_id = $database_api->getTypeId( $type );
      $version = gpc_get_string( 'doc_version', $source_obj[3] );
      $work_package = gpc_get_string( 'work_package', $source_obj[4] );
      $ptime = gpc_get_string( 'ptime', $ptime_obj[2] );

      switch ( $event )
      {
         case 'EVENT_REPORT_BUG':
            $database_api->insertReqRow( $bug_id, $type_id );
            $requirement_id = $database_api->getReqId( $bug_id );
            $database_api->insertSourceRow( $bug_id, $requirement_id, $version, $work_package, $type_id );
            $database_api->insertPtimeRow( $bug_id, $ptime );
            break;
         case 'EVENT_UPDATE_BUG':
            $database_api->updateReqRow( $bug_id, $type_id );
            $database_api->updateSourceRow( $bug_id, $version, $work_package, $type_id );
            $database_api->updatePtimeRow( $bug_id, $ptime );
            break;
      }
   }

   function menu()
   {
      if ( !plugin_is_installed('WhiteboardMenu') && plugin_config_get( 'ShowMenu' ) && $this->getUserHasLevel() )
      {
         return '<a href="' . plugin_page( 'choose_document' ) . '">' . plugin_lang_get( 'menu_title' ) . '</a>';
      }
      return null;
   }
}