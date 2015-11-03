<?php

class SpecificationManagementPlugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'SpecificationManagement';
      $this->description = 'Adds fields for management specifications to bug reports.';
      $this->page = 'config_page';

      $this->version = '1.0.2';
      $this->requires = array
      (
         'MantisCore' => '1.2.0, <= 1.3.1',
         'UserProjectView' => '>= 1.2.7'
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

         'EVENT_REPORT_BUG_FORM' => 'bugviewFields',
         'EVENT_REPORT_BUG' => 'bugupdateData',

         'EVENT_UPDATE_BUG_FORM' => 'bugviewFields',
         'EVENT_UPDATE_BUG' => 'bugupdateData',

         'EVENT_VIEW_BUG_DETAILS' => 'bugviewFields',
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
         'AccessLevel' => ADMINISTRATOR
      );
   }

   function schema()
   {
      return array
      (
         array
         (
            'CreateTableSQL', array( plugin_table( 'requirement' ), "
            id          I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id      I       NOTNULL UNSIGNED,
            type        I       NOTNULL UNSIGNED
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'source' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id          I       NOTNULL UNSIGNED,
            requirement_id  I       NOTNULL UNSIGNED,
            version         C(250)  DEFAULT '',
            type            I       NOTNULL UNSIGNED
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'type' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            type            C(250)  NOTNULL DEFAULT ''
            " )
         )
      );
   }

   function uninstall()
   {
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecDatabase_api.php';
      $db_api = new SpecDatabase_api();

      $db_api->config_resetPlugin();
   }

   /**
    * Check if user has level greater or equal then plugin access level
    *
    * @return bool - Userlevel is greater or equal then plugin access level
    */
   function getUserHasLevel()
   {
      $projectId = helper_get_current_project();
      $userId = auth_get_current_user_id();

      return user_get_access_level( $userId, $projectId ) >= plugin_config_get( 'AccessLevel', PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT );
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
   function bugviewFields( $event )
   {
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecPrint_api.php';
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecDatabase_api.php';
      $sm_api = new SpecPrint_api();
      $db_api = new SpecDatabase_api();

      $bug_id = null;
      $source = null;
      $requirement = null;

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
         $requirement = $db_api->getContentString( $requirement_obj[2] );
         $source = $source_obj[3];
      }

      $types = $db_api->getTypes();

      if ( plugin_config_get( 'ShowFields' ) && $this->getUserHasLevel() )
      {
         switch ( $event )
         {
            case 'EVENT_UPDATE_BUG_FORM':
               $sm_api->printBugUpdateFields( $types, $source );
               break;
            case 'EVENT_VIEW_BUG_DETAILS':
               $sm_api->printBugViewFields( $requirement, $source );
               break;
            case 'EVENT_REPORT_BUG_FORM':
               $source = gpc_get_string( 'source', '' );
               if ( plugin_config_get( 'ShowFields' ) && $this->getUserHasLevel() )
               {
                  $sm_api->printBugReportFields( $types, $source );
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
   function bugupdateData( $event, BugData $bug )
   {
      include config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'SpecDatabase_api.php';
      $db_api = new SpecDatabase_api();

      $bug_id = $bug->id;
      $requirement = gpc_get_string( 'types', $db_api->getContentString( $db_api->getReqRow( $bug_id )[2] ) );
      $version = gpc_get_string( 'source', $db_api->getSourceRow( $bug_id )[3] );
      $requirement_type = $db_api->getContentType( $requirement );

      switch ( $event )
      {
         case 'EVENT_REPORT_BUG':
            $db_api->insertReqRow( $bug_id, $requirement_type );
            $requirement_id = $db_api->getReqId( $bug_id );
            $db_api->insertSourceRow( $bug_id, $requirement_id, $requirement_type, $version );
            break;
         case 'EVENT_UPDATE_BUG':
            $db_api->updateReqRow( $bug_id, $requirement_type );
            $db_api->updateSourceRow( $bug_id, $requirement_type, $version );
            break;
      }
   }
}