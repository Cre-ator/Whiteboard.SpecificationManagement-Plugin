<?php
include SPECMANAGEMENT_CORE_URI . 'SpecDatabase_api.php';
include SPECMANAGEMENT_CORE_URI . 'SpecPrint_api.php';

$sd_api = new SpecDatabase_api();
$sp_api = new SpecPrint_api();

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