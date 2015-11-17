<?php
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecMenu_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecPrint_api.php';
include SPECIFICATIONMANAGEMENT_CORE_URI . 'SpecEditor_api.php';

$sm_api = new SpecMenu_api();
$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();
$se_api = new SpecEditor_api();

$option_new_bug_id = gpc_get_bool( 'newbugid', false );

if ( $option_new_bug_id )
{
   if ( !empty( $_POST['bug_id'] ) )
   {
      $bug_id = $_POST['bug_id'];
      if ( bug_exists( $bug_id ) )
      {
         $_SESSION["bug_id" . $bug_id] = $bug_id;
      }
   }
}


print_successful_redirect( plugin_page( 'Editor', true ) );