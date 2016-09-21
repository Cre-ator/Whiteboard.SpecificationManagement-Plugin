<?php

/**
 * Class dmApi
 *
 * Contains functions for the plugin specific content
 */
class dmApi
{
   /**
    * get database connection infos and connect to the database
    *
    * @return mysqli
    */
   public static function initializeDbConnection ()
   {
      $dbPath = config_get ( 'hostname' );
      $dbUser = config_get ( 'db_username' );
      $dbPass = config_get ( 'db_password' );
      $dbName = config_get ( 'database_name' );

      $mysqli = new mysqli( $dbPath, $dbUser, $dbPass, $dbName );
      $mysqli->connect ( $dbPath, $dbUser, $dbPass, $dbName );

      return $mysqli;
   }

   /**
    * returns array with 1/0 values when plugin comprehensive table is installed
    *
    * @return array
    */
   public static function checkWhiteboardTablesExist ()
   {
      $boolArray = array ();

      $boolArray[ 0 ] = self::checkTable ( 'menu' );
      $boolArray[ 1 ] = self::checkTable ( 'eta' );
      $boolArray[ 2 ] = self::checkTable ( 'etathreshold' );
      $boolArray[ 3 ] = self::checkTable ( 'workday' );

      return $boolArray;
   }

   /**
    * checks if given table exists
    *
    * @param $tableName
    * @return bool
    */
   private static function checkTable ( $tableName )
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT COUNT(id) FROM mantis_plugin_whiteboard_' . $tableName . '_table';
      $result = $mysqli->query ( $query );
      $mysqli->close ();
      if ( $result->num_rows != 0 )
      {
         return true;
      }
      else
      {
         return false;
      }
   }

   public static function checkPluginIsRegisteredInWhiteboardMenu ()
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT COUNT(id) FROM mantis_plugin_whiteboard_menu_table
         WHERE plugin_name=\'' . plugin_get_current () . '\'';

      $result = $mysqli->query ( $query );
      $mysqli->close ();
      if ( $result->num_rows != 0 )
      {
         $resultCount = mysqli_fetch_row ( $result )[ 0 ];
         if ( $resultCount > 0 )
         {
            return true;
         }
         else
         {
            return false;
         }
      }

      return null;
   }

   /**
    * register plugin in whiteboard menu
    */
   public static function addPluginToWhiteboardMenu ()
   {
      $pluginName = plugin_get_current ();
      $pluginAccessLevel = ADMINISTRATOR;
      $pluginShowMenu = ON;
      $pluginPath = '<a href="' . plugin_page ( 'choose_document' ) . '">';

      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'INSERT INTO mantis_plugin_whiteboard_menu_table (id, plugin_name, plugin_access_level, plugin_show_menu, plugin_menu_path)
         SELECT null,\'' . $pluginName . '\',' . $pluginAccessLevel . ',' . $pluginShowMenu . ',\'' . $pluginPath . '\'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM mantis_plugin_whiteboard_menu_table
         WHERE plugin_name=\'' . $pluginName . '\')';

      $mysqli->query ( $query );
      $mysqli->close ();
   }

   /**
    * edit plugin data in whiteboard menu
    *
    * @param $field
    * @param $value
    */
   public static function editPluginInWhiteboardMenu ( $field, $value )
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_whiteboard_menu_table
         SET ' . $field . '=\'' . $value . '\'
         WHERE plugin_name=\'' . plugin_get_current () . '\'';

      $mysqli->query ( $query );
      $mysqli->close ();
   }


   /**
    * remove plugin from whiteboard menu
    */
   public static function removePluginFromWhiteboardMenu ()
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'DELETE FROM mantis_plugin_whiteboard_menu_table
         WHERE plugin_name=\'' . plugin_get_current () . '\'';

      $mysqli->query ( $query );
      $mysqli->close ();
   }
}