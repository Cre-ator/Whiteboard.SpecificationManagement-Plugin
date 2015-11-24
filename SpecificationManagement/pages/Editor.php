<?php
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecMenu_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecPrint_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecEditor_api.php';

$sm_api = new SpecMenu_api();
$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();
$se_api = new SpecEditor_api();

$document_type = null;
/* initialize source */
$source = null;
/* initialize work packages */
$work_packages = array();
/* initialize bug ids assigned to work package */
$work_package_bug_ids = array();

/* get source if not empty */
if ( !empty( $_POST['version'] ) )
{
   $source = $_POST['version'];
   $document_type = $sd_api->getTypeString( $sd_api->getTypeBySource( $source ) );
}

/* get work packages from source */
$work_packages = $se_api->getDocumentSpecWorkPackages( $source );

html_page_top1( plugin_lang_get( 'page_title' ) );
echo '<link rel="stylesheet" href="' . SPECIFICATIONMANAGEMENT_PLUGIN_URL . 'files/SpecificationManagement.css">';
html_page_top2();

$sm_api->printWhiteboardMenu();
$sm_api->printEditorMenu();

$sp_api->print_document_head( $document_type, $source );

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

if ( !empty( $_SESSION ) )
{
   /* get all keys from session */
   $allSessionKeys = array_keys( $_SESSION );

   /* initialize additional bugs */
   $additional_bugs = array();
   /* for each session key */
   foreach ( $allSessionKeys as $key )
   {
      /* ensure that key is bug id */
      if ( strpos( $key, 'bug_id' ) !== false )
      {
         $additional_bugs[] = substr( $key, 6 );
      }
   }
}

if ( !empty( $additional_bugs ) )
{
   /* print work package */
   $sp_api->print_chapter_title( $chapter_index, plugin_lang_get( 'editor_additionalbugs' ) );

   $sub_chapter_index = 10;
   /* for each additional bug */
   foreach ( $additional_bugs as $add_bug_id )
   {
      /* TODO extract method    */
      /* ensure that bug exists */
      if ( bug_exists( $add_bug_id ) )
      {
         /* planned duration for each bug */
         $ptime = $sd_api->getPtimeRow( $add_bug_id )[2];
         /* print bugs */
         $sp_api->print_bugs( $chapter_index, $sub_chapter_index, $add_bug_id, $ptime );
         /* increment index */
         $sub_chapter_index += 10;
      }
   }
}

echo '</table>';

echo '<form method="post" name="form_set_requirement" action="' . plugin_page( 'Editor_update' ) . '">';
echo '<input ' . helper_get_tab_index() . ' type="text" id="bug_id" name="bug_id" size="8" minlength="1" maxlength="8" />';
echo '<input type="submit" name="newbugid" class="button-small" value="' . plugin_lang_get( 'editor_addbug' ) . '" />';
echo '</form>';

html_page_bottom1();