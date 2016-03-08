<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

$print_flag = false;
if ( isset( $_POST['print_flag'] ) )
{
   $print_flag = true;
}

/**
 * Page content
 */
calculate_page_content( $print_flag );

/**
 * @param $print_flag
 */
function calculate_page_content( $print_flag )
{
   $specmanagement_print_api = new specmanagement_print_api();

   html_page_top1( plugin_lang_get( 'select_doc_title' ) );
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'specmanagement.css">';
   if ( !$print_flag )
   {
      html_page_top2();
      if ( plugin_is_installed( 'WhiteboardMenu' ) )
      {
         require_once WHITEBOARDMENU_CORE_URI . 'whiteboard_print_api.php';
         $whiteboard_print_api = new whiteboard_print_api();
         $whiteboard_print_api->printWhiteboardMenu();
      }
      $specmanagement_print_api->print_plugin_menu();
      echo '<div align="center">';
      echo '<hr size="1" width="100%" />';
   }

   print_table( $print_flag );

   if ( !$print_flag )
   {
      html_page_bottom1();
   }
}

/**
 * @param $print_flag
 */
function print_table( $print_flag )
{
   $specmanagement_print_api = new specmanagement_print_api();


}