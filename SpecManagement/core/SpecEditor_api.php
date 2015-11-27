<?php

class SpecEditor_api
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

   public function getDocumentSpecWorkPackages( $version )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
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
   }

   public function getWorkPackageSpecBugs( $work_package )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_src_table = plugin_table( 'src', 'specmanagement' );
      }
      else
      {
         $plugin_src_table = db_get_table( 'plugin_specmanagement_src' );
      }

      $query = "SELECT DISTINCT s.bug_id FROM $plugin_src_table s
        WHERE s.work_package = '" . $work_package . "'";

      $result = $this->mysqli->query( $query );

      $bugs = array();
      while ( $row = $result->fetch_row() )
      {
         $bugs[] = $row[0];
      }

      return $bugs;
   }
}