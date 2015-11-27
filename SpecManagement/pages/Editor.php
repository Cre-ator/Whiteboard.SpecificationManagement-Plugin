<?php
include SPECMANAGEMENT_CORE_URI . 'SpecMenu_api.php';
include SPECMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECMANAGEMENT_CORE_URI . 'SpecPrint_api.php';
include SPECMANAGEMENT_CORE_URI . 'SpecEditor_api.php';

$sm_api = new SpecMenu_api();
$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();
$se_api = new SpecEditor_api();

$document_type = null;
/* initialize source */
$version = null;
/* initialize work packages */
$work_packages = array();
/* initialize bug ids assigned to work package */
$work_package_bug_ids = array();
/* initialize parent project */
$parent_project_id = $sd_api->getMainProjectByHierarchy( helper_get_current_project() );

/* get source if not empty */
if ( !empty( $_POST['version'] ) )
{
   $version = $_POST['version'];
   $document_type = $sd_api->getTypeString( $sd_api->getTypeByVersion( $version ) );
}

/* get work packages from source */
$work_packages = $se_api->getDocumentSpecWorkPackages( $version );

/* if there is no work package specified, the default work package named with "version" */
/* will be used */
if ( empty( $work_packages ) && !is_null( $version ) )
{
   array_push( $work_packages, $version );
}

html_page_top1( plugin_lang_get( 'page_title' ) );
echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/SpecManagement.css">';
html_page_top2();

$sm_api->printEditorMenu();

$sp_api->print_document_head( $document_type, $version, $parent_project_id );

echo '<table class="width100">';

$chapter_index = 1;

if ( $work_packages != null )
{
   /* for each work package */
   foreach ( $work_packages as $work_package )
   {
      $duration = $sd_api->getWorkpackageDuration( $work_package );
      /* print work package */
      $sp_api->print_chapter_title( $chapter_index, $work_package, $duration );
      /* get work package assigned bugs */
      $work_package_bug_ids = $se_api->getWorkPackageSpecBugs( $work_package );

      $sub_chapter_index = 10;
      /* for each bug in selected work package */
      foreach ( $work_package_bug_ids as $bug_id )
      {
         /* TODO extract method    */
         /* ensure that bug exists */
         if ( bug_exists( $bug_id ) )
         {
            /* planned duration for each bug */
            $ptime = $sd_api->getPtimeRow( $bug_id )[2];
            /* print bugs */
            $sp_api->print_bugs( $chapter_index, $sub_chapter_index, $bug_id, $ptime );
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