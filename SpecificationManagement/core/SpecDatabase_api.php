<?php

class SpecDatabase_api
{
   private $mysqli;
   private $dbPath;
   private $dbUser;
   private $dbPass;
   private $dbName;

   public function __construct()
   {
      $this->dbPath = config_get( 'hostname' );
      $this->dbUser = config_get( 'db_username' );
      $this->dbPass = config_get( 'db_password' );
      $this->dbName = config_get( 'database_name' );

      $this->mysqli = new mysqli( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
   }

   public function getMantisVersion()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   /**
    * Reset all plugin-related data
    *
    * - config entries
    * - database entities
    */
   public function config_resetPlugin()
   {
      $query = ' DELETE FROM mantis_config_table' .
         ' WHERE config_id' .
         ' LIKE \'plugin_SpecificationManagement_%\' ';

      $this->mysqli->query( $query );

      $query = ' DROP TABLE mantis_plugin_specificationmanagement_requirement_table';

      $this->mysqli->query( $query );

      $query = ' DROP TABLE mantis_plugin_specificationmanagement_source_table';

      $this->mysqli->query( $query );

      $query = ' DROP TABLE mantis_plugin_specificationmanagement_type_table';

      $this->mysqli->query( $query );
   }

   /**
    * Get id-related type string
    *
    * @param $type_id
    * @return string
    */
   public function getTypeString( $type_id )
   {
      $string = '';

      if ( $type_id != null )
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_type_table = plugin_table( 'type' );
         }
         else
         {
            $plugin_type_table = db_get_table( 'plugin_specificationmanagement_type' );
         }

         $query = 'SELECT type FROM ' . $plugin_type_table . '
            WHERE id = ' . $type_id;

         $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

         $string = $result[0];
      }

      return $string;
   }

   /**
    * Get bug-related requirement id
    *
    * @param $bug_id
    * @return mixed
    */
   public function getReqId( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_requirement_table = plugin_table( 'requirement' );
      }
      else
      {
         $plugin_requirement_table = db_get_table( 'plugin_specificationmanagement_requirement' );
      }

      $query = 'SELECT id FROM ' . $plugin_requirement_table . '
         WHERE bug_id = ' . $bug_id;

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $requirement_id = $result[0];

      return $requirement_id;
   }

   /**
    * Get bug-related requirement entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getReqRow( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_requirement_table = plugin_table( 'requirement' );
      }
      else
      {
         $plugin_requirement_table = db_get_table( 'plugin_specificationmanagement_requirement' );
      }

      $query = 'SELECT * FROM ' . $plugin_requirement_table . '
         WHERE bug_id = ' . $bug_id;

      $requirement_row = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $requirement_row;
   }

   /**
    * Get bug-related source entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getSourceRow( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      $query = 'SELECT * FROM ' . $plugin_source_table . '
         WHERE bug_id = ' . $bug_id;

      $source_row = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $source_row;
   }

   /**
    * Get version of source entry
    *
    * @param $source_vesion
    * @return array|null
    */
   public function getSourceVersion( $source_vesion )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      $query = "SELECT DISTINCT s.version FROM $plugin_source_table s
         WHERE s.version = '" . $source_vesion . "'";

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $source_version = $result[0];

      return $source_version;
   }

   /**
    * Create new bug-related requirement entry
    *
    * @param $bug_id
    * @param $requirement_type
    */
   public function insertReqRow( $bug_id, $requirement_type )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_requirement_table = plugin_table( 'requirement' );
      }
      else
      {
         $plugin_requirement_table = db_get_table( 'plugin_specificationmanagement_requirement' );
      }

      $query = 'INSERT INTO ' . $plugin_requirement_table . '( id, bug_id, type )
         SELECT null, ' . $bug_id . ',' . $requirement_type . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_requirement_table . '
         WHERE bug_id = ' . $bug_id . ')';

      $this->mysqli->query( $query );
   }

   /**
    * Create new bug-related source entry
    *
    * @param $bug_id
    * @param $requirement_id
    * @param $requirement_type
    * @param $version
    */
   public function insertSourceRow( $bug_id, $requirement_id, $requirement_type, $version )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      $query = 'INSERT INTO ' . $plugin_source_table . '( id, bug_id, requirement_id, version, type )
         SELECT null, ' . $bug_id . ',' . $requirement_id . ',\'' . $version . '\',' . $requirement_type . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_source_table . '
         WHERE bug_id = ' . $bug_id . ' AND version = \'' . $version . '\' AND type = ' . $requirement_type . ')';

      $this->mysqli->query( $query );
   }

   /**
    * Update existing requirement
    *
    * @param $bug_id
    * @param $requirement_type
    */
   public function updateReqRow( $bug_id, $requirement_type )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_requirement_table = plugin_table( 'requirement' );
      }
      else
      {
         $plugin_requirement_table = db_get_table( 'plugin_specificationmanagement_requirement' );
      }

      $query = 'SET SQL_SAFE_UPDATES = 0';
      $this->mysqli->query( $query );

      $query = 'UPDATE ' . $plugin_requirement_table . '
         SET type = ' . $requirement_type . '
         WHERE bug_id = ' . $bug_id;

      $this->mysqli->query( $query );

      $query = 'SET SQL_SAFE_UPDATES = 1';
      $this->mysqli->query( $query );
   }

   /**
    * Update existing source
    *
    * @param $bug_id
    * @param $requirement_type
    * @param $version
    */
   public function updateSourceRow( $bug_id, $requirement_type, $version )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      $query = 'SET SQL_SAFE_UPDATES = 0';
      $this->mysqli->query( $query );

      $query = 'UPDATE ' . $plugin_source_table . '
         SET version = \'' . $version . '\', type = ' . $requirement_type . '
         WHERE bug_id = ' . $bug_id;

      $this->mysqli->query( $query );

      $query = 'SET SQL_SAFE_UPDATES = 1';
      $this->mysqli->query( $query );
   }

   /**
    * Get the first element of type
    *
    * @return mixed
    */
   public function getFirstType()
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specificationmanagement_type' );
      }

      $query = "SELECT t.id FROM $plugin_type_table t
          ORDER BY t.id ASC LIMIT 1";

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $first_type = $result[0];

      return $first_type;
   }

   /**
    * Add a specific type
    *
    * @param $string
    */
   public function addType( $string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specificationmanagement_type' );
      }

      $query = 'INSERT INTO ' . $plugin_type_table . '( id, type )
         SELECT null, \'' . $string . '\'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_type_table . '
         WHERE type = \'' . $string . '\')';

      $this->mysqli->query( $query );
   }

   /**
    * Delete a specific type
    *
    * @param $string
    */
   public function deleteType( $string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specificationmanagement_type' );
      }

      $primary_key = $this->getTypeId( $string );

      $query = "DELETE FROM $plugin_type_table
         WHERE id = " . $primary_key;

      $this->mysqli->query( $query );
   }

   /**
    * Get the primary key for a specific type string
    *
    * @param $string
    * @return mixed
    */
   public function getTypeId( $string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specificationmanagement_type' );
      }

      $query = 'SELECT id
         FROM ' . $plugin_type_table . '
         WHERE type = \'' . $string . '\'';

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );
      $primary_key = $result[0];

      return $primary_key;
   }

   /**
    * Get all types
    *
    * @return array
    */
   public function getTypes()
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specificationmanagement_type' );
      }

      $query = "SELECT type
         FROM $plugin_type_table";

      $result = $this->mysqli->query( $query );
      $types = array();

      if ( $result != null )
      {
         while ( $row = $result->fetch_row() )
         {
            $types[] = $row[0];
         }
      }

      return $types;
   }

   /**
    * Get available sources (versions) for a specific requirement (type)
    *
    * @param $type
    * @param $project_id
    * @return array
    */
   public function getSources( $type, $project_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      $bug_table = db_get_table( 'mantis_bug_table' );

      $query = "SELECT DISTINCT s.version FROM $plugin_source_table s, $bug_table b
          WHERE s.type = " . $type;
      if ( $project_id != 0 )
      {
         $query .= " AND b.id = s.bug_id
             AND b.project_id = " . $project_id;
      }

      $result = $this->mysqli->query( $query );

      $oldTmp = null;
      $sources = array();
      while ( $row = $result->fetch_row() )
      {
         $tmp = explode( ';', $row[0] );

         if ( $tmp[0] != $oldTmp )
         {
            $sources[] = $tmp[0];
         }
         $oldTmp = $tmp[0];
      }

      return $sources;
   }
}