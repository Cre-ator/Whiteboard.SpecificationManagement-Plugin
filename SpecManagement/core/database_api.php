<?php

class database_api
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
   public function resetPlugin()
   {
      $query = 'DROP TABLE mantis_plugin_SpecManagement_req_table';

      $this->mysqli->query( $query );

      $query = 'DROP TABLE mantis_plugin_SpecManagement_src_table';

      $this->mysqli->query( $query );

      $query = 'DROP TABLE mantis_plugin_SpecManagement_type_table';

      $this->mysqli->query( $query );

      $query = 'DROP TABLE mantis_plugin_SpecManagement_ptime_table';

      $this->mysqli->query( $query );

      $query = "DELETE FROM mantis_config_table
          WHERE config_id LIKE 'plugin_SpecManagement%'";

      $this->mysqli->query( $query );

      print_successful_redirect( 'manage_plugin_page.php' );
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
            $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
         }
         else
         {
            $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
         }

         $query = 'SELECT type FROM ' . $plugin_type_table . '
            WHERE id = ' . $type_id;

         $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

         $string = $result[0];
      }

      return $string;
   }

   public function getTypeByVersion( $version )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
      }

      $query = "SELECT DISTINCT s.type_id FROM $plugin_src_table s
          WHERE s.version = '" . $version . "'";

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
         $plugin_req_table = plugin_table( 'req', 'SpecManagement' );
      }
      else
      {
         $plugin_req_table = db_get_table( 'plugin_SpecManagement_req' );
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
         $plugin_req_table = plugin_table( 'req', 'SpecManagement' );
      }
      else
      {
         $plugin_req_table = db_get_table( 'plugin_SpecManagement_req' );
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
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
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
         $plugin_ptime_table = plugin_table( 'ptime', 'SpecManagement' );
      }
      else
      {
         $plugin_ptime_table = db_get_table( 'plugin_SpecManagement_ptime' );
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
    * @param $type_id
    */
   public function insertReqRow( $bug_id, $type_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_req_table = plugin_table( 'req', 'SpecManagement' );
      }
      else
      {
         $plugin_req_table = db_get_table( 'plugin_SpecManagement_req' );
      }

      $query = 'INSERT INTO ' . $plugin_req_table . '( id, bug_id, type_id )
         SELECT null, ' . $bug_id . ',' . $type_id . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_req_table . '
         WHERE bug_id = ' . $bug_id . ')';

      $this->mysqli->query( $query );
   }

   /**
    * Create new bug-related src entry
    *
    * @param $bug_id
    * @param $requirement_id
    * @param $version
    * @param $work_package
    * @param $type_id
    */
   public function insertSourceRow( $bug_id, $requirement_id, $version, $work_package, $type_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
      }

      $query = 'INSERT INTO ' . $plugin_src_table . '( id, bug_id, requirement_id, version, work_package, type_id )
         SELECT null, ' . $bug_id . ',' . $requirement_id . ',\'' . $version . '\',\'' . $work_package . '\',' . $type_id . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM ' . $plugin_src_table . '
         WHERE bug_id = ' . $bug_id . ' AND version = \'' . $version . '\' AND work_package = \'' . $work_package . '\' AND type_id = ' . $type_id . ')';


      var_dump( $query );

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
         $plugin_ptime_table = plugin_table( 'ptime', 'SpecManagement' );
      }
      else
      {
         $plugin_ptime_table = db_get_table( 'plugin_SpecManagement_ptime' );
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
    * @param $type_id
    */
   public function updateReqRow( $bug_id, $type_id )
   {
      if ( $this->getReqRow( $bug_id ) == null )
      {
         $this->insertReqRow( $bug_id, $type_id );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_req_table = plugin_table( 'req', 'SpecManagement' );
         }
         else
         {
            $plugin_req_table = db_get_table( 'plugin_SpecManagement_req' );
         }

         $query = 'SET SQL_SAFE_UPDATES = 0';
         $this->mysqli->query( $query );

         $query = 'UPDATE ' . $plugin_req_table . '
         SET type_id = ' . $type_id . '
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
    * @param $version
    * @param $work_package
    * @param $type_id
    */
   public function updateSourceRow( $bug_id, $version, $work_package, $type_id )
   {
      if ( $this->getSourceRow( $bug_id ) == null )
      {
         $requirement_id = $this->getReqId( $bug_id );
         $this->insertSourceRow( $bug_id, $requirement_id, $version, $work_package, $type_id );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
         }
         else
         {
            $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
         }

         $query = 'SET SQL_SAFE_UPDATES = 0';
         $this->mysqli->query( $query );

         $query = 'UPDATE ' . $plugin_src_table . '
         SET version = \'' . $version . '\', work_package = \'' . $work_package . '\', type_id = ' . $type_id . '
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
            $plugin_ptime_table = plugin_table( 'ptime', 'SpecManagement' );
         }
         else
         {
            $plugin_ptime_table = db_get_table( 'plugin_SpecManagement_ptime' );
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
         $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
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
         $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
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
    * @param $type_id
    * @param $project_id
    * @return array
    */
   public function getSources( $type_id, $project_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
         $bug_table = db_get_table( 'mantis_bug_table' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
         $bug_table = db_get_table( 'bug' );
      }

      $query = "SELECT DISTINCT s.version FROM $plugin_src_table s, $bug_table b
          WHERE s.type_id = " . $type_id;
      if ( $project_id != 0 )
      {
         $query .= " AND b.id = s.bug_id
            AND b.project_id = " . $project_id;
      }

      $result = $this->mysqli->query( $query );

      $tmp_row = null;
      $srcs = array();
      while ( $row = $result->fetch_row() )
      {
         if ( $row[0] != $tmp_row )
         {
            $srcs[] = $row[0];
            $tmp_row = $row[0];
         }
      }

      return $srcs;
   }

   /**
    * Get all work packages assigned to a version of a document
    *
    * @param $version
    * @return array
    */
   public function getDocumentSpecWorkPackages( $version )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
      }

      if ( $version != null )
      {
         $query = "SELECT s.work_package FROM $plugin_src_table s
          WHERE s.version = '" . $version . "'";

         $result = $this->mysqli->query( $query );

         $tmp_row = null;
         $work_packages = array();
         while ( $row = $result->fetch_row() )
         {
            if ( $row[0] != $tmp_row )
            {
               $work_packages[] = $row[0];
               $tmp_row = $row[0];
            }
         }

         return $work_packages;
      }

      return null;
   }

   /**
    * Get all bugs of a specific work package and the version
    *
    * @param $version
    * @param $work_package
    * @return array
    */
   public function getWorkPackageSpecBugs( $version, $work_package )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
         $bug_table = db_get_table( 'mantis_bug_table' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
         $bug_table = db_get_table( 'bug' );
      }

      $query = "SELECT DISTINCT s.bug_id FROM $plugin_src_table s, $bug_table b
         WHERE s.version = '" . $version . "'
         AND s.work_package = '" . $work_package . "'
         AND s.bug_id = b.id
         AND NOT b.resolution = 90";

      $result = $this->mysqli->query( $query );

      $bugs = array();
      while ( $row = $result->fetch_row() )
      {
         $bugs[] = $row[0];
      }

      return $bugs;
   }

   /**
    * Get the duration of all bugs in a specific work package and the version
    *
    * @param $version
    * @param $work_package
    * @return mixed
    */
   public function getWorkpackageDuration( $version, $work_package )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
         $plugin_ptime_table = plugin_table( 'ptime', 'SpecManagement' );
         $bug_table = db_get_table( 'mantis_bug_table' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
         $plugin_ptime_table = db_get_table( 'plugin_SpecManagement_ptime' );
         $bug_table = db_get_table( 'bug' );
      }

      $query = "SELECT SUM( p.time ) FROM $plugin_ptime_table p, $plugin_src_table s, $bug_table b
         WHERE p.bug_id = s.bug_id
         AND s.version = '" . $version . "'
         AND s.work_package = '" . $work_package . "'
         AND s.bug_id = b.id
         AND NOT b.resolution = 90";

      $result = mysqli_fetch_row( $this->mysqli->query( $query ) );

      $duration = $result[0];

      return $duration;
   }


   /**
    * Get all bug ids from an array of work packages
    *
    * @param $work_packages
    * @param $version
    * @return array
    */
   public function getAllBugsFromWorkpackages( $work_packages, $version )
   {
      $allBugs = array();

      foreach ( $work_packages as $work_package )
      {
         $work_package_bug_ids = $this->getWorkPackageSpecBugs( $version, $work_package );

         foreach ( $work_package_bug_ids as $bug_id )
         {
            $allBugs[] = $bug_id;
         }
      }

      return $allBugs;
   }

   /**
    * Get the overall parent project by a given project/subproject
    *
    * @param $project_id
    * @return int
    */
   public function getMainProjectByHierarchy( $project_id )
   {
      if ( $project_id != 0 )
      {
         $parent_project = project_hierarchy_get_parent( $project_id, false );
         if ( project_hierarchy_is_toplevel( $project_id ) )
         {
            $parent_project_id = $project_id;
         }
         else
         {
            // selected project is subproject
            while ( project_hierarchy_is_toplevel( $parent_project, false ) == false )
            {
               $parent_project = project_hierarchy_get_parent( $parent_project, false );

               if ( project_hierarchy_is_toplevel( $parent_project ) )
               {
                  break;
               }
            }
            $parent_project_id = $parent_project;
         }

         return $parent_project_id;
      }
      else
      {
         return 0;
      }
   }
}