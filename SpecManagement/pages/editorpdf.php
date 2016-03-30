<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_editor_api.php';
require_once SPECMANAGEMENT_FILES_URI . 'fpdf181/fpdf.php';

class PDF extends FPDF
{
   // Page header
   function Header()
   {
      $specmanagement_database_api = new specmanagement_database_api();
      $version_id = $_POST['version_id'];
      $type_string = $specmanagement_database_api->get_type_string( $specmanagement_database_api->get_type_by_version( $version_id ) );
      // Logo
//      $this->Image( 'logo.png', 10, 6, 30 );
      // Arial bold 15
      $this->SetFont( 'Arial', 'B', 15 );
      // Move to the right
      $this->Cell( 45 );
      // Title
      $this->Cell( 100, 10, $type_string . ' - ' . version_get_field( $version_id, 'version' ), 1, 0, 'C' );
      // Line break
      $this->Ln( 20 );
   }

   // Page footer
   function Footer()
   {
      // Position at 1.5 cm from bottom
      $this->SetY( -15 );
      // Arial italic 8
      $this->SetFont( 'Arial', 'I', 8 );
      // Page number
      $this->Cell( 0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C' );
   }
}

$specmanagement_database_api = new specmanagement_database_api();
$specmanagement_print_api = new specmanagement_print_api();
$version_id = $_POST['version_id'];
$version_spec_bug_ids = $specmanagement_database_api->get_version_spec_bugs( version_get_field( $version_id, 'version' ) );
if ( !is_null( $version_spec_bug_ids ) )
{
   /** generate and print page content */

   $pdf = new PDF();
   $pdf->AliasNbPages();
   $pdf->AddPage();
   $pdf->SetFont( 'Arial', '', 12 );
   /** Page Content ************************************************************************************************* */

   /** get bug and work package data */
   $plugin_version_obj = $specmanagement_database_api->get_plugin_version_row_by_version_id( $version_id );
   $p_version_id = $plugin_version_obj[0];
   foreach ( $version_spec_bug_ids as $version_spec_bug_id )
   {
      $p_source_row = $specmanagement_database_api->get_source_row( $version_spec_bug_id );
      if ( is_null( $p_source_row[2] ) )
      {
         $specmanagement_database_api->update_source_row( $version_spec_bug_id, $p_version_id, '' );
      }
   }
   $work_packages = $specmanagement_database_api->get_document_spec_workpackages( $p_version_id );
   asort( $work_packages );
   $no_work_package_bug_ids = $specmanagement_database_api->get_workpackage_spec_bugs( $p_version_id, '' );

   /** get type options */
   $type_string = $specmanagement_database_api->get_type_string( $specmanagement_database_api->get_type_by_version( $version_id ) );
   $type_id = $specmanagement_database_api->get_type_id( $type_string );
   $type_row = $specmanagement_database_api->get_type_row( $type_id );
   $type_options = explode( ';', $type_row[2] );

   /** generate and print directory */
   if ( $type_options[2] == '1' )
   {
      $pdf->Cell( 0, 10, plugin_lang_get( 'editor_directory' ), 1, 1 );
      /** @var detail_flag = false :: show detailed bug-information */
      $pdf = generate_content( $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids, $type_options[0], false );
   }

   $pdf->Cell( 0, 10, 'content', 1, 1 );
   $pdf = generate_content( $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids, $type_options[0], true );


   /** ************************************************************************************************************** */
   $pdf->Output();
}

/**
 * Print table body from directory
 *
 * @param $pdf
 * @param $p_version_id
 * @param $work_packages
 * @param $no_work_package_bug_ids
 * @param $option_show_duration
 * @param $detail_flag
 * @return PDF
 */
function generate_content( PDF $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids, $option_show_duration, $detail_flag )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $specmanagement_editor_api = new specmanagement_editor_api();
   $directory_depth = $specmanagement_editor_api->calculate_directory_depth( $work_packages );
   $chapter_counter_array = $specmanagement_editor_api->prepare_chapter_counter( $directory_depth );
   $last_chapter_depth = 0;
   $version_id = $_POST['version_id'];
   $version = version_get( $version_id );
   $version_date = $version->date_order;

   /** Iterate through defined work packages */
   if ( !is_null( $work_packages ) )
   {
      foreach ( $work_packages as $work_package )
      {
         if ( strlen( $work_package ) > 0 )
         {
            $work_package_spec_bug_ids = $specmanagement_database_api->get_workpackage_spec_bugs( $p_version_id, $work_package );
            $chapters = explode( '/', $work_package );
            $chapter_depth = count( $chapters );
            if ( $chapter_depth == 1 )
            {
               $specmanagement_editor_api->reset_chapter_counter( $chapter_counter_array );
            }

            $chapter_prefix_data = $specmanagement_editor_api->generate_chapter_prefix( $chapter_counter_array, $chapter_depth, $last_chapter_depth );
            $chapter_counter_array = $chapter_prefix_data[0];
            $chapter_prefix = $chapter_prefix_data[1];
            $chapter_suffix = $specmanagement_editor_api->generate_chapter_suffix( $chapters, $chapter_depth );

//            $pdf->Cell( 200, 10, $chapter_prefix . ' ' . $chapter_suffix, 0, 1 );
            process_content( $pdf, $work_package_spec_bug_ids, $version_date, $chapter_prefix, $option_show_duration, $detail_flag );
            $last_chapter_depth = $chapter_depth;
         }
      }
   }

   /** Iterate through issues without defined work package */
   $chapter_prefix = $chapter_counter_array[0] + 1;
   $chapter_suffix = plugin_lang_get( 'editor_no_workpackage' );
   if ( count( $no_work_package_bug_ids ) > 0 )
   {
//      $pdf->Cell( 200, 10, $chapter_prefix . ' ' . $chapter_suffix, 0, 1 );
      process_content( $pdf, $no_work_package_bug_ids, $version_date, $chapter_prefix, $option_show_duration, $detail_flag );
   }

   return $pdf;
}

/**
 * @param PDF $pdf
 * @param $bug_ids
 * @param $version_date
 * @param $chapter_prefix
 * @param $option_show_duration
 * @param $detail_flag
 */
function process_content( PDF $pdf, $bug_ids, $version_date, $chapter_prefix, $option_show_duration, $detail_flag )
{
   $specmanagement_editor_api = new specmanagement_editor_api();
   $bug_counter = 10;
   foreach ( $bug_ids as $bug_id )
   {
      if ( bug_exists( $bug_id ) )
      {
         $bug_data = $specmanagement_editor_api->calculate_bug_data( $bug_id, $version_date );
         if ( $detail_flag )
         {
            $pdf->Cell( 0, 10, $chapter_prefix . '.' . $bug_counter . ' ' . bug_format_id( $bug_data[0] ), 0, 1 );
            $pdf->Cell( 0, 10, string_display_line( trim( $bug_data[2] ) ), 0, 1 );
            $pdf->Cell( 0, 10, string_display_links( trim( $bug_data[3] ) ), 0, 1 );
            $pdf->Cell( 0, 10, string_display_links( trim( $bug_data[4] ) ), 0, 1 );
            if ( !empty( $bug_data[5] ) )
            {
               $attachment_count = file_bug_attachment_count( $bug_id );
               $pdf->Cell( 0, 10, plugin_lang_get( 'editor_bug_attachments' ) . ' (' . $attachment_count . ')', 0, 1 );
            }
            if ( !is_null( $bug_data[6] ) && $bug_data[6] != 0 )
            {
               $pdf->Cell( 0, 10, plugin_lang_get( 'editor_bug_notes_note' ) . ' (' . $bug_data[6] . ')', 0, 1 );
            }
         }
         else
         {
            $pdf->Cell( 200, 10, $chapter_prefix . '.' . $bug_counter . ' ' . string_display( $bug_data[1] ) . ' (' . bug_format_id( $bug_data[0] ) . ')', 0, 1 );
         }
         $bug_counter += 10;
      }
   }
}