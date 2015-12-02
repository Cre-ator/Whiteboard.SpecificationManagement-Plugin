<?php

class specmanagementplugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'specmanagement';
      $this->description = 'Adds fields for management specs to bug reports.';
      $this->page = 'config_page';

      $this->version = '1.0.9';
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
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecPrint_api.php';
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecDatabase_api.php';
      $sm_api = new SpecPrint_api();
      $db_api = new SpecDatabase_api();

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
         $requirement_obj = $db_api->getReqRow( $bug_id );
         $source_obj = $db_api->getSourceRow( $bug_id );
         $ptime_obj = $db_api->getPtimeRow( $bug_id );

         $type = $db_api->getTypeString( $requirement_obj[2] );
         $version = $source_obj[3];
         $work_package = $source_obj[4];
         $ptime = $ptime_obj[2];
      }

      $types = $db_api->getTypes();

      if ( plugin_config_get( 'ShowFields' ) )
      {
         switch ( $event )
         {
            case 'EVENT_VIEW_BUG_DETAILS':
               if ( $this->getReadLevel() || $this->getWriteLevel() )
               {
                  $sm_api->printBugViewFields( $type, $version, $work_package, $ptime );
               }
               break;
            case 'EVENT_REPORT_BUG_FORM':
               if ( $this->getWriteLevel() )
               {
                  $version = gpc_get_string( 'version', '' );
                  $sm_api->printBugReportFields( $types, $version, $work_package, $ptime );
               }
               break;
            case 'EVENT_UPDATE_BUG_FORM':
               if ( $this->getWriteLevel() )
               {
                  $sm_api->printBugUpdateFields( $type, $types, $version, $work_package, $ptime );
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
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecDatabase_api.php';
      $db_api = new SpecDatabase_api();

      $bug_id = $bug->id;

      $requirement_obj = $db_api->getReqRow( $bug_id );
      $source_obj = $db_api->getSourceRow( $bug_id );
      $ptime_obj = $db_api->getPtimeRow( $bug_id );

      $type = gpc_get_string( 'types', $db_api->getTypeString( $requirement_obj[2] ) );
      $type_id = $db_api->getTypeId( $type );
      $version = gpc_get_string( 'doc_version', $source_obj[3] );
      $work_package = gpc_get_string( 'work_package', $source_obj[4] );
      $ptime = gpc_get_string( 'ptime', $ptime_obj[2] );

      switch ( $event )
      {
         case 'EVENT_REPORT_BUG':
            $db_api->insertReqRow( $bug_id, $type_id );
            $requirement_id = $db_api->getReqId( $bug_id );
            $db_api->insertSourceRow( $bug_id, $requirement_id, $version, $work_package, $type_id );
            $db_api->insertPtimeRow( $bug_id, $ptime );
            break;
         case 'EVENT_UPDATE_BUG':
            $db_api->updateReqRow( $bug_id, $type_id );
            $db_api->updateSourceRow( $bug_id, $version, $work_package, $type_id );
            $db_api->updatePtimeRow( $bug_id, $ptime );
            break;
      }
   }

   function menu()
   {
      if ( plugin_config_get( 'ShowMenu' ) && $this->getUserHasLevel() )
      {
         return '<a href="' . plugin_page( 'ChooseDocument' ) . '">' . plugin_lang_get( 'menu_title' ) . '</a>';
      }
      return null;
   }
}