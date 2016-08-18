<?php
require_once ( __DIR__ . '/../../VersionManagement/core/vmVersion.php' );

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 18.08.2016
 * Time: 10:25
 */
class document
{
   /**
    * @var string
    */
   private $name;
   /**
    * @var integer
    */
   private $versionId;
   /**
    * @var integer
    */
   private $date;
   /**
    * @var array
    */
   private $bugIds;

   /**
    * @return string
    */
   public function getName ()
   {
      return $this->name;
   }

   /**
    * @param string $name
    */
   public function setName ( $name )
   {
      $this->name = $name;
   }

   /**
    * @return int
    */
   public function getVersionId ()
   {
      return $this->versionId;
   }

   /**
    * @return int
    */
   public function getDate ()
   {
      return $this->date;
   }

   /**
    * @param int $date
    */
   public function setDate ( $date )
   {
      $this->date = $date;
   }

   /**
    * @return array
    */
   public function getBugIds ()
   {
      return $this->bugIds;
   }

   /**
    * @param array $bugIds
    */
   public function setBugIds ( $bugIds )
   {
      $this->bugIds = $bugIds;
   }

   /**
    * document constructor.
    * @param $versionId
    */
   public function __construct ( $versionId )
   {
      $this->versionId = $versionId;
   }

   /**
    * document destructor.
    */
   public function __destruct ()
   {
      // TODO: Implement __destruct() method.
   }

   /**
    * print the document
    */
   public function printDocument ()
   {
      $this->printDocumentHead ();
   }

   /**
    * print the head of the document
    */
   private function printDocumentHead ()
   {
      # table
      echo '<div class="table">';
      # row - name
      echo '<div class="tr"><div class="td w10">' . plugin_lang_get ( 'head_title' ) . '</div>' .
         '<div class="td w90">' . $this->name . '</div></div>';
      # row - version
      echo '<div class="tr"><div class="td w10">' . lang_get ( 'version' ) . '</div>' .
         '<div class="td w90">' . version_get_field ( $this->versionId, 'version' ) . '</div></div>';
      # row - project
      echo '<div class="tr"><div class="td w10">' . lang_get ( 'email_project' ) . '</div>' .
         '<div class="td w90">' . project_get_name ( version_get_field ( $this->versionId, 'project_id' ) ) . '</div></div>';
      # row - date
      echo '<div class="tr"><div class="td w10">' . plugin_lang_get ( 'head_date' ) . '</div>' .
         '<div class="td w90">' . string_attribute ( date ( config_get ( 'calendar_date_format' ), time () ) ) . '</div></div>';
      # row - person responsible
      echo '<div class="tr"><div class="td w10">' . plugin_lang_get ( 'head_person_in_charge' ) . '</div>' .
         '<div class="td w90">' . $this->generatePersonResponsibleString ( $this->getPersonResponsibleUserIds () ) . '</div></div>';
      # row - document progress
      echo '<div class="tr"><div class="td w100">' . $this->printDocumentProgress ( $this->getDocumentProgress () ) . '</div></div>';
      # row - version history
      echo '<div class="tr"><div class="td">' . $this->printVersionHistory ( $this->generateVersionHistory () ) . '</div></div>';
      # end table
      echo '</div>';
   }

   /**
    * iterate through responsible user ids an generate a name string
    * @param $responsibleUserNames
    * @return string
    */
   private function generatePersonResponsibleString ( $responsibleUserNames )
   {
      $string = '';
      $userIdCount = count ( $responsibleUserNames );
      for ( $index = 0; $index < $userIdCount; $index++ )
      {
         $string .= $responsibleUserNames[ $index ];

         if ( $index < ( $userIdCount - 1 ) )
         {
            $string .= ',&nbsp;';
         }
      }

      return $string;
   }

   /**
    * get names of responsible users and returns
    * @return array
    */
   private function getPersonResponsibleUserIds ()
   {
      $responsibleUserNames = array ();

      $projectId = version_get_field ( $this->versionId, 'project_id' );
      $accessUsers = project_get_all_user_rows ( $projectId, MANAGER );

      foreach ( $accessUsers as $accessUser )
      {
         $userName = $accessUser[ 'realname' ];
         array_push ( $responsibleUserNames, $userName );
      }

      return $responsibleUserNames;
   }

   /**
    * get all version ids for the selected version-assigned project
    *
    * @return array
    */
   private function generateVersionHistory ()
   {
      $versionHistoryVersionIds = array ();

      $projectId = version_get_field ( $this->versionId, 'project_id' );
      $tmpVersions = version_get_all_rows_with_subs ( $projectId );
      foreach ( $tmpVersions as $tmpVersion )
      {
         $tmpVersionId = $tmpVersion[ 'id' ];
         array_push ( $versionHistoryVersionIds, $tmpVersionId );
      }

      return $versionHistoryVersionIds;
   }

   /**
    * print the version history with date and name
    *
    * @param $versionHistoryVersionIds
    */
   private function printVersionHistory ( $versionHistoryVersionIds )
   {
      echo '<div class="table">';
      foreach ( $versionHistoryVersionIds as $historyVersionId )
      {
         $version = new vmVersion( $historyVersionId );
         # row
         echo '<div class="tr">';
         # column - date
         echo '<div class="td w20">' . string_attribute ( date ( config_get ( 'calendar_date_format' ), $version->getDateOrder () ) ) . '</div>';
         # column - name
         echo '<div class="td w60">';
         if ( version_get_field ( $this->versionId, 'project_id' ) != $version->getProjectId () )
         {
            echo '[' . project_get_name ( $version->getProjectId () ) . ']&nbsp;';
         }
         echo $version->getVersionName ();
         echo '</div>';
         # column - button show changes
         echo '<div class="td w10">folgt</div>';
         # column - button show document
         echo '<div class="td w10">folgt</div>';
         # end row
         echo '</div>';
      }
      echo '</div>';
   }

   /**
    * print the progressbar for the document
    *
    * @param $documentProgress
    */
   private function printDocumentProgress ( $documentProgress )
   {
      echo '<div class="progress9002">';
      echo '<span class="bar single" style="width: ' . ( $documentProgress * 10 ) . '%">' . ( $documentProgress * 10 ) . '%</span>';
      echo '</div>';
   }

   /**
    * get the progress of a document as decimal [0..1].
    *
    * @return float|int
    */
   private function getDocumentProgress ()
   {
      $documentProgress = 0;
      if ( $this->checkEtaIsUsable () )
      {
         $documentProgress = round ( ( $this->getDoneEta () / $this->getMaxEta () ), 2 );
      }
      else
      {
         $documentProgress = round ( ( $this->getDoneBugs () / $this->getMaxBugs () ), 2 );
      }

      return $documentProgress;
   }

   /**
    * check if eta value is usable for calculation (all issues must have an eta value)
    *
    * @return bool
    */
   private function checkEtaIsUsable ()
   {
      $etaIsUsable = true;
      if ( !config_get ( 'enable_eta' ) )
      {
         $etaIsUsable = false;
      }
      else
      {
         foreach ( $this->bugIds as $bugId )
         {
            $eta = bug_get_field ( $bugId, 'eta' );
            if ( ( $eta == null ) )
            {
               $etaIsUsable = false;
            }
         }
      }

      return $etaIsUsable;
   }

   /**
    * get the sum of eta from all bugs
    *
    * @return int
    */
   private function getMaxEta ()
   {
      $etaMax = 0;
      foreach ( $this->bugIds as $bugId )
      {
         $eta = (int)bug_get_field ( $bugId, 'eta' );
         $etaMax += $eta;
      }

      return $etaMax;
   }

   /**
    * get the sum of eta from done bugs
    *
    * @return int
    */
   private function getDoneEta ()
   {
      $etaDone = 0;
      foreach ( $this->bugIds as $bugId )
      {
         $status = bug_get_field ( $bugId, 'status' );
         if ( $status == RESOLVED || $status == CLOSED )
         {
            $eta = (int)bug_get_field ( $bugId, 'eta' );
            $etaDone += $eta;
         }
      }

      return $etaDone;
   }

   /**
    * get the amount of bug which can be done
    *
    * @return int
    */
   private function getMaxBugs ()
   {
      return count ( $this->bugIds );
   }

   /**
    * get the amount of bug which are done
    *
    * @return int
    */
   private function getDoneBugs ()
   {
      $bugDone = 0;
      foreach ( $this->bugIds as $bugId )
      {
         $status = bug_get_field ( $bugId, 'status' );
         if ( $status == RESOLVED || $status == CLOSED )
         {
            $bugDone++;
         }
      }

      return $bugDone;
   }
}