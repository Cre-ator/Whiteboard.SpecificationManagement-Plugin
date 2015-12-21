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

   /**
    * Get suffix of mantis version
    *
    * @return string
    */
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
      $query = "DROP TABLE mantis_plugin_SpecManagement_src_table";

      $this->mysqli->query( $query );

      $query = "DROP TABLE mantis_plugin_SpecManagement_type_table";

      $this->mysqli->query( $query );

      $query = "DROP TABLE mantis_plugin_SpecManagement_ptime_table";

      $this->mysqli->query( $query );

      $query = "DROP TABLE mantis_plugin_SpecManagement_vers_table";

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
      if ( !is_null( $type_id ) )
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
         }
         else
         {
            $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
         }

         $query = "SELECT t.type FROM $plugin_type_table t
            WHERE t.id = " . $type_id;

         $result = $this->mysqli->query( $query );
         if ( 0 != $result->num_rows )
         {
            $row = mysqli_fetch_row( $result );
            $type_string = $row[0];
            return $type_string;
         }
         else
         {
            return null;
         }
      }
      return '';
   }

   /**
    * Get version-related type id
    *
    * @param $version_id
    * @return mixed
    */
   public function getTypeByVersion( $version_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "SELECT DISTINCT v.type_id FROM $plugin_vers_table v
          WHERE v.version_id = " . $version_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $row = mysqli_fetch_row( $result );
         $type_id = $row[0];
         return $type_id;
      }
      else
      {
         return null;
      }
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

      $query = "SELECT * FROM $plugin_src_table s
         WHERE s.bug_id = " . $bug_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $source_row = mysqli_fetch_row( $result );
         return $source_row;
      }
      else
      {
         return null;
      }
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

      $query = "SELECT * FROM $plugin_ptime_table p
         WHERE p.bug_id = " . $bug_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $ptime_row = mysqli_fetch_row( $result );
         return $ptime_row;
      }
      else
      {
         return null;
      }
   }

   /**
    * Get version-related version entry
    *
    * @param $version_id
    * @return array|null
    */
   public function getVersionRowByVersionId( $version_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "SELECT * FROM $plugin_vers_table v
        WHERE v.version_id = " . $version_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $version_row = mysqli_fetch_row( $result );
         return $version_row;
      }
      else
      {
         return null;
      }
   }

   /**
    * Get primary key related version entry
    *
    * @param $primary_id
    * @return array|null
    */
   public function getVersionRowByPrimary( $primary_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "SELECT * FROM $plugin_vers_table v
        WHERE v.id = " . $primary_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $version_row = mysqli_fetch_row( $result );
         return $version_row;
      }
      else
      {
         return null;
      }
   }

   /**
    * Create new bug-related src entry
    *
    * @param $bug_id
    * @param $version_id
    * @param $work_package
    */
   public function insertSourceRow( $bug_id, $version_id, $work_package )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
      }

      $version_row = $this->getVersionRowByVersionId( $version_id );
      $p_version_id = $version_row[0];

      $query = "INSERT INTO $plugin_src_table ( id, bug_id, p_version_id, work_package )
         SELECT null," . $bug_id . "," . $p_version_id . ",'" . $work_package . "'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_src_table
         WHERE bug_id = " . $bug_id . " AND p_version_id = " . $p_version_id . " AND work_package = '" . $work_package . "')";

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

      $query = "INSERT INTO $plugin_ptime_table ( id, bug_id, time )
         SELECT null," . $bug_id . "," . $ptime . "
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_ptime_table
         WHERE bug_id = " . $bug_id . " AND time = " . $ptime . ")";

      $this->mysqli->query( $query );
   }

   /**
    * Create new version entry
    *
    * @param $project_id
    * @param $version_id
    * @param $type_id
    */
   public function insertVersionRow( $project_id, $version_id, $type_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "INSERT INTO $plugin_vers_table ( id, project_id, version_id, type_id )
         SELECT null," . $project_id . "," . $version_id . "," . $type_id . "
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_vers_table
         WHERE project_id = " . $project_id . " AND version_id = " . $version_id . " AND type_id = " . $type_id . ")";

      $this->mysqli->query( $query );
   }

   /**
    * Update existing src
    *
    * @param $bug_id
    * @param $p_version_id
    * @param $work_package
    */
   public function updateSourceRow( $bug_id, $p_version_id, $work_package )
   {
      if ( $this->getSourceRow( $bug_id ) == null )
      {
         $this->insertSourceRow( $bug_id, $p_version_id, $work_package );
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

         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_src_table
            SET p_version_id = " . $p_version_id . ", work_package = '" . $work_package . "'
            WHERE bug_id = " . $bug_id;

         $this->mysqli->query( $query );

         $query = "SET SQL_SAFE_UPDATES = 1";
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

         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_ptime_table
            SET time = '" . $ptime . "'
            WHERE bug_id = " . $bug_id;

         $this->mysqli->query( $query );

         $query = "SET SQL_SAFE_UPDATES = 1";
         $this->mysqli->query( $query );
      }
   }

   /**
    * Update existing version
    *
    * @param $project_id
    * @param $version_id
    * @param $type_id
    */
   public function updateVersionRow( $project_id, $version_id, $type_id )
   {
      if ( $this->getVersionRowByVersionId( $version_id ) == null )
      {
         $this->insertVersionRow( $project_id, $version_id, $type_id );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
         }
         else
         {
            $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
         }

         $version_row = $this->getVersionRowByVersionId( $version_id );
         $p_version_id = $version_row[0];

         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_vers_table
         SET project_id = " . $project_id . ", version_id = " . $version_id . ", type_id = " . $type_id . "
         WHERE id = " . $p_version_id;

         $this->mysqli->query( $query );

         $query = "SET SQL_SAFE_UPDATES = 1";
         $this->mysqli->query( $query );
      }
   }

   /**
    * Deletes a version row
    *
    * @param $version_id
    */
   public function deleteVersionRow( $version_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "DELETE FROM $plugin_vers_table
         WHERE version_id = " . $version_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Deletes a source row
    *
    * @param $p_version_id
    */
   public function deleteSourceRow( $p_version_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
      }

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "DELETE FROM $plugin_src_table
         WHERE p_version_id = " . $p_version_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Update an existing association if it exists or, of not, create a new one
    *
    * @param $project_id
    * @param $version_id
    * @param $type_id
    */
   public function updateVersionAssociatedType( $project_id, $version_id, $type_id )
   {
      if ( $this->getVersionRowByVersionId( $version_id ) == null )
      {
         $this->insertVersionRow( $project_id, $version_id, $type_id );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
         }
         else
         {
            $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
         }

         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_vers_table
         SET type_id = " . $type_id . "
         WHERE version_id = " . $version_id;

         $this->mysqli->query( $query );

         $query = "SET SQL_SAFE_UPDATES = 1";
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

      $result = $this->mysqli->query( $query );

      if ( 0 != $result->num_rows )
      {
         $row = mysqli_fetch_row( $result );
         $first_type = $row[0];
         return $first_type;
      }
      else
      {
         return null;
      }
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

      $query = "INSERT INTO $plugin_type_table ( id, type )
         SELECT null,'" . $string . "'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_type_table
         WHERE type = '" . $string . "')";

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

      $query = "SELECT t.id FROM $plugin_type_table t
         WHERE t.type = '" . $string . "'";

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $row = mysqli_fetch_row( $result );
         $primary_key = $row[0];
         return $primary_key;
      }
      else
      {
         return null;
      }
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

      $query = "SELECT t.type FROM $plugin_type_table t ORDER BY t.type ASC";

      $result = $this->mysqli->query( $query );
      $types = array();
      if ( 0 != $result->num_rows )
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
   public function getVersionIDs( $type_id, $project_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "SELECT DISTINCT v.version_id
         FROM $plugin_vers_table v
         WHERE v.type_id = " . $type_id;
      if ( $project_id != 0 )
      {
         $query .= " AND v.project_id = " . $project_id;
      }

      $result = $this->mysqli->query( $query );

      $tmp_row = null;
      $version_ids = array();

      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            if ( $row[0] != $tmp_row )
            {
               $version_ids[] = $row[0];
               $tmp_row = $row[0];
            }
         }
      }

      return $version_ids;
   }

   /**
    * Get all work packages assigned to a version of a document
    *
    * @param $p_version_id
    * @return array
    */
   public function getDocumentSpecWorkPackages( $p_version_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'SpecManagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_SpecManagement_src' );
      }

      if ( $p_version_id != null )
      {
         $query = "SELECT DISTINCT s.work_package FROM $plugin_src_table s
            WHERE s.p_version_id = '" . $p_version_id . "'";

         $result = $this->mysqli->query( $query );

         $tmp_row = null;
         $work_packages = array();

         if ( 0 != $result->num_rows )
         {
            while ( $row = $result->fetch_row() )
            {
               if ( $row[0] != $tmp_row )
               {
                  $work_packages[] = $row[0];
                  $tmp_row = $row[0];
               }
            }
         }

         return $work_packages;
      }

      return null;
   }

   /**
    * Get all bugs of a specific work package and the version
    *
    * @param $p_version_id
    * @param $work_package
    * @return array
    */
   public function getWorkPackageSpecBugs( $p_version_id, $work_package )
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
         WHERE s.p_version_id = '" . $p_version_id . "'
         AND s.work_package = '" . $work_package . "'
         AND s.bug_id = b.id
         AND NOT b.resolution = 90";

      $result = $this->mysqli->query( $query );

      $bugs = array();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            $bugs[] = $row[0];
         }
      }

      return $bugs;
   }

   /**
    * Get the duration of all bugs in a specific work package and the version
    *
    * @param $p_version_id
    * @param $work_package
    * @return mixed
    */
   public function getWorkpackageDuration( $p_version_id, $work_package )
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
         AND s.p_version_id = '" . $p_version_id . "'
         AND s.work_package = '" . $work_package . "'
         AND s.bug_id = b.id
         AND NOT b.resolution = 90";

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $row = mysqli_fetch_row( $result );
         $duration = $row[0];
         return $duration;
      }
      else
      {
         return null;
      }
   }

   /**
    * Returns true if incoming type id is in use
    *
    * @param $type_id
    * @return bool
    */
   public function checkTypeIsUsed( $type_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_vers_table = plugin_table( 'vers', 'SpecManagement' );
      }
      else
      {
         $plugin_vers_table = db_get_table( 'plugin_SpecManagement_vers' );
      }

      $query = "SELECT COUNT(*) FROM $plugin_vers_table v
          WHERE v.type_id = " . $type_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $row = mysqli_fetch_row( $result );
         return $row[0] > 0;
      }
      else
      {
         return null;
      }
   }

   /**
    * Update an existing type string
    *
    * @param $type_id
    * @param $new_type_string
    */
   public function updateTypeString( $type_id, $new_type_string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'SpecManagement' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_SpecManagement_type' );
      }

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_type_table
         SET type = '" . $new_type_string . "'
         WHERE id = " . $type_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Get all bug ids from an array of work packages
    *
    * @param $work_packages
    * @param $p_version_id
    * @return array
    */
   public function getAllBugsFromWorkpackages( $work_packages, $p_version_id )
   {
      $allBugs = array();

      if ( $work_packages != null )
      {
         foreach ( $work_packages as $work_package )
         {
            $work_package_bug_ids = $this->getWorkPackageSpecBugs( $p_version_id, $work_package );

            foreach ( $work_package_bug_ids as $bug_id )
            {
               $allBugs[] = $bug_id;
            }
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