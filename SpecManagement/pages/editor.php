<?php
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$database_api = new database_api();
$print_api = new print_api();

$document_type = null;

/* initialize print_duration */
$print_duration = null;
/* initialize version */
$version = null;
/* initialize work packages */
$work_packages = array();
/* initialize bug ids assigned to work package */
$work_package_bug_ids = array();
/* initialize parent project */
$parent_project_id = $database_api->getMainProjectByHierarchy( helper_get_current_project() );

/* get print_duration option if not empty */
if ( !empty( $_POST['print_duration'] ) )
{
   $print_duration = $_POST['print_duration'];
}

/* get version if not empty */
if ( !empty( $_POST['version'] ) )
{
   $version = $_POST['version'];
   $document_type = $database_api->getTypeString( $database_api->getTypeByVersion( $version ) );
}

/* get work packages from source */
$work_packages = $database_api->getDocumentSpecWorkPackages( $version );

/* get all bug ids from an array of work packages */
$allRelevantBugs = $database_api->getAllBugsFromWorkpackages( $work_packages, $version );

/* if there is no work package specified, the default work package named with "version" */
/* will be used */
if ( empty( $work_packages ) && !is_null( $version ) )
{
   array_push( $work_packages, $version );
}

html_page_top1( plugin_lang_get( 'editor_title' ) . ': ' . $document_type . ' - ' . $version );
echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/SpecManagement.css">';
html_page_top2();

$print_api->print_editor_menu();
$print_api->print_document_head( $document_type, $version, $parent_project_id, $allRelevantBugs );

echo '<table class="width100">';

$chapter_index = 1;

if ( $work_packages != null )
{
   /* for each work package */
   foreach ( $work_packages as $work_package )
   {
      $duration = $database_api->getWorkpackageDuration( $version, $work_package );
      /* print work package */
      $print_api->print_chapter_title( $chapter_index, $work_package, $print_duration, $duration );
      /* get work package assigned bugs */
      $work_package_bug_ids = $database_api->getWorkPackageSpecBugs( $version, $work_package );

      $sub_chapter_index = 10;
      /* for each bug in selected work package */
      foreach ( $work_package_bug_ids as $bug_id )
      {
         /* ensure that bug exists */
         if ( bug_exists( $bug_id ) )
         {
            /* planned duration for each bug */
            $ptime = $database_api->getPtimeRow( $bug_id )[2];
            /* print bugs */
            $print_api->print_bugs( $chapter_index, $sub_chapter_index, $bug_id, $print_duration, $ptime );
            /* increment index */
            $sub_chapter_index += 10;
         }
      }
      /* increment index */
      $chapter_index++;
   }
}

echo '</table>';

html_page_bottom1();