<?php

class specmanagement_database_api
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
   public function get_mantis_version()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   /**
    * Reset all plugin-related data
    *
    * - config entries
    * - database entities
    */
   public function reset_plugin()
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
    * @param $table
    * @return string
    */
   private function get_mantis_table( $table )
   {
      if ( $this->get_mantis_version() == '1.2.' )
      {
         $mantis_table = db_get_table( 'mantis_' . $table . '_table' );
      }
      else
      {
         $mantis_table = db_get_table( $table );
      }
      return $mantis_table;
   }

   /**
    * @param $table
    * @return string
    */
   private function get_mantis_plugin_table( $table )
   {
      if ( $this->get_mantis_version() == '1.2.' )
      {
         $mantis_plugin_table = plugin_table( $table, 'SpecManagement' );
      }
      else
      {
         $mantis_plugin_table = db_get_table( 'plugin_SpecManagement_' . $table );
      }
      return $mantis_plugin_table;
   }

   /**
    * Returns true if incoming type id is in use
    *
    * @param $type_id
    * @return bool
    */
   public function check_type_is_used( $type_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );
      $version_table = $this->get_mantis_table( 'project_version' );

      $query = "SELECT COUNT(*) FROM $plugin_vers_table v, $version_table w
          WHERE v.type_id = " . $type_id . " AND v.version_id = w.id";

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
    * Get id-related type string
    *
    * @param $type_id
    * @return string
    */
   public function get_type_string( $type_id )
   {
      if ( !is_null( $type_id ) )
      {
         $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

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
   public function get_type_by_version( $version_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );
      $version_table = $this->get_mantis_table( 'project_version' );

      $query = "SELECT DISTINCT v.type_id FROM $plugin_vers_table v, $version_table w
          WHERE v.version_id = " . $version_id . " AND v.version_id = w.id";

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
    * Get id related type row
    *
    * @param $type_id
    * @return array|null
    */
   public function get_type_row( $type_id )
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

      $query = "SELECT * FROM $plugin_type_table t
         WHERE t.id = " . $type_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $type_row = mysqli_fetch_row( $result );
         return $type_row;
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
   public function get_source_row( $bug_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

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
   public function get_ptime_row( $bug_id )
   {
      $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );

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
   public function get_plugin_version_row_by_version_id( $version_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );
      $version_table = $this->get_mantis_table( 'project_version' );


      if ( $version_id == false )
      {
         return null;
      }
      else
      {
         $query = "SELECT * FROM $plugin_vers_table v, $version_table w
            WHERE version_id = " . $version_id . " AND v.version_id = w.id";

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
   }

   /**
    * Get primary key related version entry
    *
    * @param $primary_id
    * @return array|null
    */
   public function get_version_row_by_primary( $primary_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );
      $version_table = $this->get_mantis_table( 'project_version' );

      $query = "SELECT * FROM $plugin_vers_table v, $version_table w
        WHERE v.id = " . $primary_id . " AND v.version_id = w.id";

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
    * Get project id related version entry
    *
    * @param $project_id
    * @return array|null
    */
   public function get_version_rows_by_project_id( $project_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );
      $version_table = $this->get_mantis_table( 'project_version' );

      $query = "SELECT * FROM $plugin_vers_table v, $version_table w
        WHERE v.project_id = " . $project_id . " AND v.version_id = w.id";

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $tmp_row = null;
         $version_rows = array();
         while ( $row = $result->fetch_row() )
         {
            if ( $row[0] != $tmp_row )
            {
               $version_rows[] = $row[0];
               $tmp_row = $row[0];
            }
         }
         return $version_rows;
      }
      else
      {
         return null;
      }
   }

   /**
    * Get the first element of type
    *
    * @return mixed
    */
   public function get_first_type()
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

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
    * Get the primary key for a specific type string
    *
    * @param $string
    * @return mixed
    */
   public function get_type_id( $string )
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

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
   public function get_full_types()
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

      $query = "SELECT * FROM $plugin_type_table ORDER BY type ASC";

      $result = $this->mysqli->query( $query );
      $types = array();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            $types[] = $row;
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
   public function get_version_ids( $type_id, $project_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );
      $version_table = $this->get_mantis_table( 'project_version' );

      $query = "SELECT DISTINCT v.version_id
         FROM $plugin_vers_table v, $version_table w
         WHERE v.type_id = " . $type_id . " AND v.version_id = w.id";
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
   public function get_document_spec_workpackages( $p_version_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

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
    * Get all work packages assigned to a project
    *
    * @return array
    */
   public function get_project_spec_workpackages()
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      $p_version_ids = $this->get_version_rows_by_project_id( helper_get_current_project() );
      $work_packages = array();
      $tmp_row = null;

      if ( !is_null( $p_version_ids ) )
      {
         foreach ( $p_version_ids as $p_version_id )
         {
            $query = "SELECT DISTINCT s.work_package FROM $plugin_src_table s
               WHERE s.p_version_id = '" . $p_version_id . "'";

            $result = $this->mysqli->query( $query );
            $work_packages_tmps = array();

            if ( 0 != $result->num_rows )
            {
               while ( $row = $result->fetch_row() )
               {
                  if ( $row[0] != $tmp_row )
                  {
                     $work_packages_tmps[] = $row[0];
                     $tmp_row = $row[0];
                  }
               }
            }
            foreach ( $work_packages_tmps as $work_packages_tmp )
            {
               $work_packages[] = $work_packages_tmp;
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
   public function get_workpackage_spec_bugs( $p_version_id, $work_package )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );
      $bug_table = $this->get_mantis_table( 'bug' );

      $query = "SELECT DISTINCT s.bug_id FROM $plugin_src_table s, $bug_table b
         WHERE s.p_version_id = '" . $p_version_id . "'
         AND s.work_package = '" . $work_package . "'
         AND s.bug_id = b.id
         AND NOT b.resolution = 90
         ORDER BY s.bug_id";

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
   public function get_workpackage_duration( $p_version_id, $work_package )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );
      $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );
      $bug_table = $this->get_mantis_table( 'bug' );

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
    * Get all bug ids from an array of work packages
    *
    * @param $work_packages
    * @param $p_version_id
    * @return array
    */
   public function get_all_bugs_from_workpackages( $work_packages, $p_version_id )
   {
      $allBugs = array();

      if ( $work_packages != null )
      {
         foreach ( $work_packages as $work_package )
         {
            $work_package_bug_ids = $this->get_workpackage_spec_bugs( $p_version_id, $work_package );

            foreach ( $work_package_bug_ids as $bug_id )
            {
               $allBugs[] = $bug_id;
            }
         }
      }

      return $allBugs;
   }

   /**
    * @param $bug_id
    */
   public function get_workpackage_by_bug_id( $bug_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      if ( bug_exists( $bug_id ) )
      {
         $query = "SELECT work_package FROM $plugin_src_table
            WHERE bug_id = " . $bug_id;

         var_dump( $query );

         $result = $this->mysqli->query( $query );
         if ( 0 != $result->num_rows )
         {
            $row = mysqli_fetch_row( $result );
            $work_package = $row[0];
            return $work_package;
         }
      }
   }

   /**
    * Get the overall parent project by a given project/subproject
    *
    * @param $project_id
    * @return int
    */
   public function get_main_project_by_hierarchy( $project_id )
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

   /**
    * @param $string
    */
   public function insert_type_row( $string )
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

      $query = "INSERT INTO $plugin_type_table ( id, type, opt )
         SELECT null,'" . $string . "', ';;'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_type_table
         WHERE type = '" . $string . "')";

      $this->mysqli->query( $query );
   }

   /**
    * Create new bug-related src entry
    *
    * @param $bug_id
    * @param $p_version_id
    * @param $work_package
    */
   public function insert_source_row( $bug_id, $p_version_id, $work_package )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      $query = "INSERT INTO $plugin_src_table ( id, bug_id, p_version_id, work_package )
         SELECT null," . $bug_id . ",";
      if ( is_null( $p_version_id ) )
      {
         $query .= "null,";
      }
      else
      {
         $query .= $p_version_id . ",";
      }

      $query .= "'" . $work_package . "'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_src_table
         WHERE bug_id = " . $bug_id . " AND p_version_id = ";
      if ( is_null( $p_version_id ) )
      {
         $query .= " null";
      }
      else
      {
         $query .= $p_version_id;
      }
      $query .= " AND work_package = '" . $work_package . "')";

      $this->mysqli->query( $query );
   }

   /**
    * Create new bug-related time entry
    *
    * @param $bug_id
    * @param $ptime
    */
   public function insert_ptime_row( $bug_id, $ptime )
   {
      $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );

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
   public function insert_version_row( $project_id, $version_id, $type_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );

      $query = "INSERT INTO $plugin_vers_table ( id, project_id, version_id, type_id )
         SELECT null," . $project_id . "," . $version_id . "," . $type_id . "
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_vers_table
         WHERE project_id = " . $project_id . " AND version_id = " . $version_id . " AND type_id = " . $type_id . ")";

      $this->mysqli->query( $query );
   }

   /**
    * Update an existing type string
    *
    * @param $type_id
    * @param $new_type_string
    */
   public function update_type_row( $type_id, $new_type_string )
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

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
    * Updade options from a type
    *
    * @param $type_id
    * @param $type_options
    */
   public function update_type_options( $type_id, $type_options )
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_type_table
            SET opt = '" . $type_options . "'
            WHERE id = " . $type_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Update existing src
    *
    * @param $bug_id
    * @param $p_version_id
    * @param $work_package
    */
   public function update_source_row( $bug_id, $p_version_id, $work_package )
   {
      if ( $this->get_source_row( $bug_id ) == null )
      {
         $this->insert_source_row( $bug_id, $p_version_id, $work_package );
      }
      else
      {
         $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_src_table
            SET work_package = '" . $work_package . "'";

         if ( is_null( $p_version_id ) )
         {
            $query .= ",p_version_id=null";
         }
         else
         {
            $query .= ",p_version_id=" . $p_version_id;
         }
         $query .= " WHERE bug_id = " . $bug_id;

         $this->mysqli->query( $query );

         $query = "SET SQL_SAFE_UPDATES = 1";
         $this->mysqli->query( $query );
      }
   }

   /**
    * Reset p_version_id when a version has been deleted
    *
    * @param $p_version_id
    */
   public function update_source_version_set_null( $p_version_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_src_table
            SET p_version_id = null
            WHERE p_version_id = " . $p_version_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Reset p_version_id when a version has been deleted
    *
    * @param $bug_id
    * @param $p_version_id
    */
   public function update_source_version( $bug_id, $p_version_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_src_table
            SET p_version_id = ";
      if ( is_null( $p_version_id ) )
      {
         $query .= "null";
      }
      else
      {
         $query .= $p_version_id;
      }
      $query .= " WHERE bug_id = " . $bug_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Update existing planned time
    *
    * @param $bug_id
    * @param $ptime
    */
   public function update_ptime_row( $bug_id, $ptime )
   {
      if ( $this->get_ptime_row( $bug_id ) == null )
      {
         $this->insert_ptime_row( $bug_id, $ptime );
      }
      else
      {
         $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );

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
   public function update_version_row( $project_id, $version_id, $type_id )
   {
      if ( $this->get_plugin_version_row_by_version_id( $version_id ) == null )
      {
         $this->insert_version_row( $project_id, $version_id, $type_id );
      }
      else
      {
         $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );

         $version_row = $this->get_plugin_version_row_by_version_id( $version_id );
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
    * Update an existing association if it exists or, of not, create a new one
    *
    * @param $project_id
    * @param $version_id
    * @param $type_id
    */
   public function update_version_associated_type( $project_id, $version_id, $type_id )
   {
      if ( $this->get_plugin_version_row_by_version_id( $version_id ) == null )
      {
         $this->insert_version_row( $project_id, $version_id, $type_id );
      }
      else
      {
         $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );

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
    * Delete a specific type
    *
    * @param $string
    */
   public function delete_type_row( $string )
   {
      $plugin_type_table = $this->get_mantis_plugin_table( 'type' );

      $primary_key = $this->get_type_id( $string );

      $query = "DELETE FROM $plugin_type_table
         WHERE id = " . $primary_key;

      $this->mysqli->query( $query );
   }

   /**
    * Deletes a source row
    *
    * @param $p_version_id
    */
   public function delete_source_row( $p_version_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "DELETE FROM $plugin_src_table
         WHERE p_version_id = " . $p_version_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Deletes a source row by specific bug id
    *
    * @param $bug_id
    */
   public function delete_source_row_by_bug( $bug_id )
   {
      $plugin_src_table = $this->get_mantis_plugin_table( 'src' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "DELETE FROM $plugin_src_table
         WHERE bug_id = " . $bug_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Deletes a ptime row
    *
    * @param $bug_id
    */
   public function delete_ptime_row( $bug_id )
   {
      $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "DELETE FROM $plugin_ptime_table
         WHERE bug_id = " . $bug_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Deletes a version row
    *
    * @param $version_id
    */
   public function delete_version_row( $version_id )
   {
      $plugin_vers_table = $this->get_mantis_plugin_table( 'vers' );

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "DELETE FROM $plugin_vers_table
         WHERE version_id = " . $version_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Gets the related issues for a specific version
    *
    * @param $version_string
    * @return array
    */
   public function get_version_spec_bugs( $version_string )
   {
      $bug_table = $this->get_mantis_table( 'bug' );

      $query = "SELECT id FROM $bug_table
          WHERE target_version = '" . string_display( $version_string ) . "'";

      $result = $this->mysqli->query( $query );

      $bugs = array();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            $bugs[] = $row[0];
         }
         return $bugs;
      }

      return null;
   }

   /**
    * Get the duration of a bug array
    *
    * @param $bug_array
    * @return int
    */
   public function get_bug_array_duration( $bug_array )
   {
      $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );

      $duration = 0;
      foreach ( $bug_array as $bug )
      {
         $query = "SELECT time FROM $plugin_ptime_table
            WHERE bug_id = " . $bug;

         $result = $this->mysqli->query( $query );
         $bug_duration = 0;
         if ( 0 != $result->num_rows )
         {
            $row = $result->fetch_row();
            $bug_duration = $row[0];
         }

         $duration += $bug_duration;
      }

      return $duration;
   }

   /**
    * Get the duration of a bug array
    *
    * @param $bug_id
    * @return int
    */
   public function get_bug_duration( $bug_id )
   {
      $plugin_ptime_table = $this->get_mantis_plugin_table( 'ptime' );

      $bug_duration = 0;
      $query = "SELECT time FROM $plugin_ptime_table
            WHERE bug_id = " . $bug_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $row = $result->fetch_row();
         $bug_duration = $row[0];
      }

      return $bug_duration;
   }

   /**
    * Get the relationship row (type 2) for a pair of bugs
    *
    * @param $src_bug_id
    * @param $dest_bug_id
    * @return mixed|null
    */
   public function get_bug_relationship_type_two( $src_bug_id, $dest_bug_id )
   {
      $bug_relationship_table = $this->get_mantis_table( 'bug_relationship' );

      $query = "SELECT * FROM $bug_relationship_table
         WHERE source_bug_id = " . $src_bug_id . "
         AND destination_bug_id = " . $dest_bug_id . "
         AND relationship_type = 2";

      $result = $this->mysqli->query( $query );

      $relationship = null;
      if ( 0 != $result->num_rows )
      {
         $row = $result->fetch_row();
         $relationship = $row;
      }

      return $relationship;
   }

   /**
    * Get bugs related to given project id
    *
    * @param $project_id
    * @return array|null
    */
   public function get_bugs_by_project( $project_id )
   {
      $bug_table = $this->get_mantis_table( 'bug' );

      $query = "SELECT id FROM $bug_table
          WHERE project_id = " . $project_id;

      $result = $this->mysqli->query( $query );

      $bugs = array();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            $bugs[] = $row[0];
         }
         return $bugs;
      }

      return null;
   }

   /**
    * Get last change values for:
    * - Summary
    * - Priorität
    * - Produktversion
    * - Zielversion
    * - Behoben in Version
    * - Status
    * - Lösung
    * - Reproduzierbarkeit
    * - Sichtbarkeit
    * - Auswirkung
    * - Bearbeiter
    * - Plattform
    * - OS
    * - OS Version
    *
    * @param $bug_id
    * @param $version_date
    * @param $int_filter_string
    * @return array
    */
   public function calculate_last_change( $bug_id, $version_date, $int_filter_string )
   {
      $output_value = null;
      $spec_filter_string = lang_get( $int_filter_string );
      $min_time_difference = 0;
      $min_time_difference_event_id = 0;
      $bug_history_events = history_get_events_array( $bug_id );

      for ( $event_index = 0; $event_index < count( $bug_history_events ); $event_index++ )
      {
         $bug_history_event = $bug_history_events[$event_index];

         if ( $bug_history_event['note'] == $spec_filter_string )
         {
            $bug_history_event_date = strtotime( $bug_history_event['date'] );
            $local_time_difference = ( $version_date - $bug_history_event_date );

            /* initial value */
            if ( $min_time_difference == 0 )
            {
               $min_time_difference = $local_time_difference;
               $min_time_difference_event_id = $event_index;
            }

            /* overwrite existing if it is closer to event date */
            if ( $min_time_difference > $local_time_difference )
            {
               $min_time_difference = $local_time_difference;
               $min_time_difference_event_id = $event_index;
            }
         }
      }

      $output_change = null;
      $output_values = null;
      if ( !empty( $bug_history_events ) )
      {
         $output_change = $bug_history_events[$min_time_difference_event_id]['change'];
         $output_values = explode( ' => ', $output_change );
      }

      if ( $min_time_difference <= 0 )
      {
         $output_value = $output_values[0];
      }
      else
      {
         $output_value = $output_values[1];
      }
      /**
       * TODO kann wegen der Feldbezeichnung eventuell noch zu Problemen führen.
       */
      if ( strlen( $output_change ) == 0 )
      {
         $output_value = bug_get_field( $bug_id, $int_filter_string );
      }

      return $output_value;
   }

   /**
    * Get last change values for:
    * - Description
    * - Steps to reproduce
    * - Additional information
    *
    * @param $bug_id
    * @param $version_date
    * @param $type_id
    * @return null
    */
   public function calculate_last_text_fields( $bug_id, $version_date, $type_id )
   {
      $output_value = null;
      $min_pos_time_difference = 0;
      $min_pos_time_difference_description = null;
      $min_neg_time_difference = 0;
      $min_neg_time_difference_description = null;

      $revision_events = bug_revision_list( $bug_id );

      foreach ( $revision_events as $revision_event )
      {
         if ( $revision_event['type'] == $type_id )
         {
            $revision_event_timestamp = $revision_event['timestamp'];
            $local_time_difference = ( $version_date - $revision_event_timestamp );

            if ( $local_time_difference > 0 )
            {
               /* initial value */
               if ( $min_pos_time_difference == 0 )
               {
                  $min_pos_time_difference = $local_time_difference;
                  $min_pos_time_difference_description = $revision_event['value'];
               }

               /* overwrite existing if it is closer to event date */
               if ( $min_pos_time_difference > $local_time_difference )
               {
                  $min_pos_time_difference = $local_time_difference;
                  $min_pos_time_difference_description = $revision_event['value'];
               }
            }
            else
            {
               /* initial value */
               if ( $min_neg_time_difference == 0 )
               {
                  $min_neg_time_difference = $local_time_difference;
                  $min_neg_time_difference_description = $revision_event['value'];
               }

               /* overwrite existing if it is closer to event date */
               if ( $min_neg_time_difference < $local_time_difference )
               {
                  $min_neg_time_difference = $local_time_difference;
                  $min_neg_time_difference_description = $revision_event['value'];
               }
            }
         }
      }

      if ( !is_null( $min_pos_time_difference_description ) )
      {
         $output_value = $min_pos_time_difference_description;
      }
      else
      {
         $output_value = $min_neg_time_difference_description;
      }
      return $output_value;
   }

   /**
    * Get last change values for:
    * - amount of bugotes
    *
    * @param $bug_id
    * @param $version_date
    * @return int
    */
   public function calculate_last_bugnotes( $bug_id, $version_date )
   {
      $bugnote_count = 0;

      $bugnotes = bugnote_get_all_bugnotes( $bug_id );
      foreach ( $bugnotes as $bugnote )
      {
         if ( $bugnote->date_submitted <= $version_date )
         {
            $bugnote_count++;
         }
      }
      return $bugnote_count;
   }
}