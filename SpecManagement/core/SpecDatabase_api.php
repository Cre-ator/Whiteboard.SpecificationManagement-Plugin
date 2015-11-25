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

      $query = ' DROP TABLE mantis_plugin_specmanagement_req_table';

      $this->mysqli->query( $query );

      $query = ' DROP TABLE mantis_plugin_specmanagement_src_table';

      $this->mysqli->query( $query );

      $query = ' DROP TABLE mantis_plugin_specmanagement_type_table';

      $this->mysqli->query( $query );

      $query = ' DROP TABLE mantis_plugin_specmanagement_ptime_table';

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
            $plugin_type_table = plugin_table( 'type', 'specmanagement' );
         }
         else
         {
            $plugin_type_table = db_get_table( 'plugin_specmanagement_type' );
         }

         $query = 'SELECT type FROM ' . $plugin_type_table . '
            WHERE id = ' . $type_id;

         $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

         $string = $result[0];
      }

      return $string;
   }

   public function getTypeBySource( $src )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
      }

      $query = "SELECT DISTINCT s.type FROM $plugin_src_table s
          WHERE s.version LIKE '" . $src . "%'";

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $type_id = $result[0];

      return $type_id;
   }

   /**
    * Get bug-related req id
    *
    * @param $bug_id
    * @return mixed
    */
   public function getReqId( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_req_table = plugin_table( 'req', 'specmanagement' );
      }
      else
      {
         $plugin_req_table = db_get_table( 'plugin_specmanagement_req' );
      }

      $query = 'SELECT id FROM ' . $plugin_req_table . '
         WHERE bug_id = ' . $bug_id;

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $req_id = $result[0];

      return $req_id;
   }

   /**
    * Get bug-related req entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getReqRow( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_req_table = plugin_table( 'req', 'specmanagement' );
      }
      else
      {
         $plugin_req_table = db_get_table( 'plugin_specmanagement_req' );
      }

      $query = 'SELECT * FROM ' . $plugin_req_table . '
         WHERE bug_id = ' . $bug_id;

      $req_row = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $req_row;
   }

   /**
    * Get bug-related src entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getSourceRow( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
      }

      $query = 'SELECT * FROM ' . $plugin_src_table . '
         WHERE bug_id = ' . $bug_id;

      $src_row = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $src_row;
   }

   /**
    * Get bug-related planned time entry
    *
    * @param $bug_id
    * @return array|null
    */
   public function getPtimeRow( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_ptime_table = plugin_table( 'ptime', 'specmanagement' );
      }
      else
      {
         $plugin_ptime_table = db_get_table( 'plugin_specmanagement_ptime' );
      }

      $query = 'SELECT * FROM ' . $plugin_ptime_table . '
         WHERE bug_id = ' . $bug_id;

      $ptime_row = mysqli_fetch_row( $this->mysqli->query( $query ) );

      return $ptime_row;
   }

   /**
    * Create new bug-related req entry
    *
    * @param $bug_id
    * @param $req_type
    */
   public function insertReqRow( $bug_id, $req_type )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_req_table = plugin_table( 'req', 'specmanagement' );
      }
      else
      {
         $plugin_req_table = db_get_table( 'plugin_specmanagement_req' );
      }

      $query = 'INSERT INTO ' . $plugin_req_table . '( id, bug_id, type )
         SELECT null, ' . $bug_id . ',' . $req_type . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_req_table . '
         WHERE bug_id = ' . $bug_id . ')';

      $this->mysqli->query( $query );
   }

   /**
    * Create new bug-related src entry
    *
    * @param $bug_id
    * @param $req_id
    * @param $req_type
    * @param $version
    */
   public function insertSourceRow( $bug_id, $req_id, $req_type, $version )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
      }

      $query = 'INSERT INTO ' . $plugin_src_table . '( id, bug_id, req_id, version, type )
         SELECT null, ' . $bug_id . ',' . $req_id . ',\'' . $version . '\',' . $req_type . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_src_table . '
         WHERE bug_id = ' . $bug_id . ' AND version = \'' . $version . '\' AND type = ' . $req_type . ')';

      var_dump($query);

      $this->mysqli->query( $query );
   }

   /**
    * Create new bug-related time entry
    *
    * @param $bug_id
    * @param $ptime
    */
   public function insertPtimeRow( $bug_id, $ptime )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_ptime_table = plugin_table( 'ptime', 'specmanagement' );
      }
      else
      {
         $plugin_ptime_table = db_get_table( 'plugin_specmanagement_ptime' );
      }

      $query = 'INSERT INTO ' . $plugin_ptime_table . '( id, bug_id, time)
         SELECT null, ' . $bug_id . ',' . $ptime . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_ptime_table . '
         WHERE bug_id = ' . $bug_id . ' AND time = ' . $ptime . ')';

      $this->mysqli->query( $query );
   }

   /**
    * Update existing req
    *
    * @param $bug_id
    * @param $req_type
    */
   public function updateReqRow( $bug_id, $req_type )
   {
      if ( $this->getReqRow( $bug_id ) == null )
      {
         $this->insertReqRow( $bug_id, $req_type );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_req_table = plugin_table( 'req', 'specmanagement' );
         }
         else
         {
            $plugin_req_table = db_get_table( 'plugin_specmanagement_req' );
         }

         $query = 'SET SQL_SAFE_UPDATES = 0';
         $this->mysqli->query( $query );

         $query = 'UPDATE ' . $plugin_req_table . '
         SET type = ' . $req_type . '
         WHERE bug_id = ' . $bug_id;

         $this->mysqli->query( $query );

         $query = 'SET SQL_SAFE_UPDATES = 1';
         $this->mysqli->query( $query );
      }
   }

   /**
    * Update existing src
    *
    * @param $bug_id
    * @param $req_type
    * @param $version
    */
   public function updateSourceRow( $bug_id, $req_type_id, $req_type, $version )
   {
      if ( $this->getSourceRow( $bug_id ) == null )
      {
         $req_id = $this->getTypeId( $req_type );
         $this->insertSourceRow( $bug_id, $req_id, $req_type_id, $version );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_src_table = plugin_table( 'src', 'specmanagement' );
         }
         else
         {
            $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
         }

         $query = 'SET SQL_SAFE_UPDATES = 0';
         $this->mysqli->query( $query );

         $query = 'UPDATE ' . $plugin_src_table . '
         SET version = \'' . $version . '\', type = ' . $req_type . '
         WHERE bug_id = ' . $bug_id;

         $this->mysqli->query( $query );

         $query = 'SET SQL_SAFE_UPDATES = 1';
         $this->mysqli->query( $query );
      }
   }

   /**
    * Update existing planned time
    *
    * @param $bug_id
    * @param $ptime
    */
   public function updatePtimeRow( $bug_id, $ptime )
   {
      if ( $this->getPtimeRow( $bug_id ) == null )
      {
         $this->insertPtimeRow( $bug_id, $ptime );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_ptime_table = plugin_table( 'ptime', 'specmanagement' );
         }
         else
         {
            $plugin_ptime_table = db_get_table( 'plugin_specmanagement_ptime' );
         }

         $query = 'SET SQL_SAFE_UPDATES = 0';
         $this->mysqli->query( $query );

         $query = 'UPDATE ' . $plugin_ptime_table . '
         SET time = ' . $ptime . '
         WHERE bug_id = ' . $bug_id;

         $this->mysqli->query( $query );

         $query = 'SET SQL_SAFE_UPDATES = 1';
         $this->mysqli->query( $query );
      }
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
         $plugin_type_table = plugin_table( 'type', 'specmanagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specmanagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'specmanagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specmanagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'specmanagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specmanagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'specmanagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specmanagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'specmanagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_specmanagement_type' );
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
    * Get available srcs (versions) for a specific req (type)
    *
    * @param $type
    * @param $project_id
    * @return array
    */
   public function getSources( $type, $project_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
         $bug_table = db_get_table( 'mantis_bug_table' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
         $bug_table = db_get_table( 'bug' );
      }

      $query = "SELECT DISTINCT s.version FROM $plugin_src_table s, $bug_table b
          WHERE s.type = " . $type;
      if ( $project_id != 0 )
      {
         $query .= " AND b.id = s.bug_id
             AND b.project_id = " . $project_id;
      }

      $result = $this->mysqli->query( $query );

      $oldTmp = null;
      $srcs = array();
      while ( $row = $result->fetch_row() )
      {
         $tmp = explode( ';', $row[0] );

         if ( $tmp[0] != $oldTmp )
         {
            $srcs[] = $tmp[0];
         }
         $oldTmp = $tmp[0];
      }

      return $srcs;
   }

   public function getWorkpackageDuration( $work_package )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
         $plugin_ptime_table = plugin_table( 'ptime', 'specmanagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
         $plugin_ptime_table = db_get_table( 'plugin_specmanagement_ptime' );
      }

      $query = "SELECT SUM( p.time ) FROM $plugin_ptime_table p, $plugin_src_table s
         WHERE p.bug_id = s.bug_id
         AND s.version LIKE '%" . $work_package . "'";

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $duration = $result[0];

      return $duration;
   }

   public function getProbablyAssignedBugs( $work_package )
   {

   }
}