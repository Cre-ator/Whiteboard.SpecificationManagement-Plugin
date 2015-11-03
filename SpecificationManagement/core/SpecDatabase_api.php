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
    * Get string-related type id
    *
    * @param $type_string
    * @return mixed
    */
   public function getContentType( $type_string )
   {
      $plugin_type_table = db_get_table( 'mantis_plugin_specificationmanagement_type_table' );

      $query = 'SELECT id FROM ' . $plugin_type_table . '
         WHERE type = \'' . $type_string . '\'';

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $type = $result[0];

      return $type;
   }

   /**
    * Get id-related type string
    *
    * @param $type_id
    * @return string
    */
   public function getContentString( $type_id )
   {
      $string = '';

      if ( $type_id != null )
      {
         $plugin_type_table = db_get_table( 'mantis_plugin_specificationmanagement_type_table' );

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
      $plugin_requirement_table = db_get_table( 'mantis_plugin_specificationmanagement_requirement_table' );

      $query = 'SELECT id FROM ' . $plugin_requirement_table . '
         WHERE bug_id = ' . $bug_id;

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $id = $result[0];

      return $id;
   }

   /**
    * Get bug-related requirement entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getReqRow( $bug_id )
   {
      $plugin_requirement_table = db_get_table( 'mantis_plugin_specificationmanagement_requirement_table' );

      $query = 'SELECT * FROM ' . $plugin_requirement_table . '
         WHERE bug_id = ' . $bug_id;

      $reqRow = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $reqRow;
   }

   /**
    * Get bug-related source entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getSourceRow( $bug_id )
   {
      $plugin_source_table = db_get_table( 'mantis_plugin_specificationmanagement_source_table' );

      $query = 'SELECT * FROM ' . $plugin_source_table . '
         WHERE bug_id = ' . $bug_id;

      $sourceRow = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $sourceRow;
   }

   /**
    * Create new bug-related requirement entry
    *
    * @param $bug_id
    * @param $requirement_type
    */
   public function insertReqRow( $bug_id, $requirement_type )
   {
      $plugin_requirement_table = db_get_table( 'mantis_plugin_specificationmanagement_requirement_table' );

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
      $plugin_source_table = db_get_table( 'mantis_plugin_specificationmanagement_source_table' );

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
      $plugin_requirement_table = db_get_table( 'mantis_plugin_specificationmanagement_requirement_table' );

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
      $plugin_source_table = db_get_table( 'mantis_plugin_specificationmanagement_source_table' );

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
    * Add a specific type
    *
    * @param $string
    */
   public function addType( $string )
   {
      $plugin_type_table = db_get_table( 'mantis_plugin_specificationmanagement_type_table' );

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
      $plugin_type_table = db_get_table( 'mantis_plugin_specificationmanagement_type_table' );
      $primaryKey = $this->getTypePrimareyKey( $string );

      $query = "DELETE FROM $plugin_type_table
         WHERE id = " . $primaryKey;

      $this->mysqli->query( $query );
   }

   /**
    * Get the primary key for a specific type string
    *
    * @param $string
    * @return mixed
    */
   private function getTypePrimareyKey( $string )
   {
      $plugin_type_table = db_get_table( 'mantis_plugin_specificationmanagement_type_table' );

      $query = 'SELECT id
         FROM ' . $plugin_type_table . '
         WHERE type = \'' . $string . '\'';

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );
      $primaryKey = $result[0];

      return $primaryKey;
   }

   /**
    * Get all types
    *
    * @return array
    */
   public function getTypes()
   {
      $plugin_type_table = db_get_table( 'mantis_plugin_specificationmanagement_type_table' );

      $query = "SELECT type
         FROM $plugin_type_table";

      $result = $this->mysqli->query( $query );

      $types = array();
      while ( $row = $result->fetch_row() )
      {
         $types[] = $row[0];
      }

      return $types;
   }
}