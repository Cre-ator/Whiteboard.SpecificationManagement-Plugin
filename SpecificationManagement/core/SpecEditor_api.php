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

   public function getDocumentSpecWorkPackages( $source )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      if ( $source != null )
      {
         $query = "SELECT s.version FROM $plugin_source_table s
          WHERE s.version LIKE '" . $source . "%'";

         $result = $this->mysqli->query( $query );

         $oldTmp = null;
         $work_packages = array();
         while ( $row = $result->fetch_row() )
         {
            $tmp = explode( ';', $row[0] );

            if ( $tmp[1] != $oldTmp )
            {
               $work_packages[] = $tmp[1];
            }
            $oldTmp = $tmp[1];
         }

         return $work_packages;
      }
   }

   public function getWorkPackageSpecBugs( $work_package )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_source_table = plugin_table( 'source' );
      }
      else
      {
         $plugin_source_table = db_get_table( 'plugin_specificationmanagement_source' );
      }

      $query = "SELECT DISTINCT s.bug_id FROM $plugin_source_table s
        WHERE s.version LIKE '%" . $work_package . "%'";

      $result = $this->mysqli->query( $query );

      $bugs = array();
      while ( $row = $result->fetch_row() )
      {
         $bugs[] = $row[0];
      }

      return $bugs;
   }
}