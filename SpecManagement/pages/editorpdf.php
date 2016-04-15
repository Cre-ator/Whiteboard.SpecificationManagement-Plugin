<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_editor_api.php';
require_once SPECMANAGEMENT_FILES_URI . 'fpdf181/fpdf.php';

class PDF extends FPDF
{
   /**
    * Page Header
    */
   function Header()
   {
      $this->Image( SPECMANAGEMENT_FILES_URI . 'logo.png', 160, 6, 30 );
      $this->Ln( 10 );
      $this->Cell( 0, 0, '', 'T' );
      $this->Ln( 20 );
   }

   /**
    * Page Footer
    */
   function Footer()
   {
      $this->SetY( -15 );
      $this->SetFont( 'Arial', '', 9 );
      $this->Cell( 95, 10, date( 'd\.m\.Y' ), 0, 0, 'L' );
      $this->Cell( 95, 10, plugin_lang_get( 'editor_page' ) . ' ' . $this->PageNo() . '/{nb}', 0, 0, 'R' );
   }

   /**
    * Chapter Title Element
    * @param $num
    * @param $label
    * @param $option_show_duration
    * @param $chapter_duration
    */
   function ChapterTitle( $num, $label, $option_show_duration, $chapter_duration )
   {
      $this->SetFont( 'Arial', 'B', 12 );
      $this->SetFillColor( 192, 192, 192 );
      if ( $option_show_duration == '1' && $chapter_duration > 0 )
      {
         $this->Cell( 95, 6, "$num $label", 0, 0, 'L', 1 );
         $this->Cell( 95, 6, utf8_decode( plugin_lang_get( 'editor_work_package_duration' ) ) . ': ' . $chapter_duration . ' ' . plugin_lang_get( 'editor_duration_unit' ), 0, 0, 'R', 1 );
      }
      else
      {
         $this->Cell( 0, 6, "$num $label", 0, 0, 'L', 1 );
      }
      $this->SetFont( 'Arial', '', 12 );
      $this->Ln( 8 );
   }

   /**
    * Title Element
    * @param $label
    */
   function Title( $label )
   {
      $this->SetFont( 'Arial', 'B', 12 );
      $this->SetFillColor( 192, 192, 192 );
      $this->Cell( 0, 6, "$label", 0, 1, 'L', 1 );
      $this->SetFont( 'Arial', '', 12 );
      $this->Ln( 3 );
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

   /** print document head */
   $pdf = generate_document_head( $pdf, $type_string, $version_id, $version_spec_bug_ids );
   $pdf->Ln( 10 );

   /** generate and print directory */
   if ( $type_options[2] == '1' )
   {
      $pdf->Title( plugin_lang_get( 'editor_directory' ) );
      $pdf->Ln( 2 );
      $pdf->Cell( 0, 0, '', 'T' );
      $pdf->Ln();
      /** @var detail_flag = false :: show detailed bug-information */
      $content = generate_content( $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids, false, false );
      $pdf = $content[0];
      $chapter_counter = $content[1];
      if ( $type_options[1] == '1' )
      {
         $pdf->SetFont( 'Arial', 'B', 12 );
         $pdf->SetFillColor( 255, 255, 255 );
         $pdf->Cell( 95, 6, $chapter_counter . ' ' . utf8_decode( plugin_lang_get( 'editor_expenses_overview' ) ), 0, 0, 'L', 1 );
         $pdf->Cell( 95, 6, $pdf->PageNo(), 0, 0, 'R', 1 );
         $pdf->SetFont( 'Arial', '', 12 );
         $pdf->Ln();
      }
      $pdf->Cell( 0, 0, '', 'T' );
      $pdf->Ln( 20 );
   }

   /** print core */
   $content = generate_content( $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids, $type_options[0], true );
   $pdf = $content[0];
   $chapter_counter = $content[1];
   $pdf->Ln( 20 );

   /** print expeneses overview */
   if ( $type_options[1] == '1' )
   {
      $pdf->Title( $chapter_counter . ' ' . utf8_decode( plugin_lang_get( 'editor_expenses_overview' ) ) );
      $pdf = generate_expenses_overview( $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids );
   }

   /** generate pdf */
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
 * @return Array
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
            $chapter_duration = $specmanagement_database_api->get_workpackage_duration( $p_version_id, $work_package );

            if ( $detail_flag )
            {
               $pdf->ChapterTitle( $chapter_prefix, utf8_decode( $chapter_suffix ), $option_show_duration, $chapter_duration );
            }
            else
            {
               $pdf->SetFont( 'Arial', 'B', 12 );
               $pdf->SetFillColor( 255, 255, 255 );
               $pdf->Cell( 95, 6, $chapter_prefix . ' ' . utf8_decode( $chapter_suffix ), 0, 0, 'L', 1 );
               $pdf->Cell( 95, 6, $pdf->PageNo(), 0, 0, 'R', 1 );
               $pdf->SetFont( 'Arial', '', 12 );
               $pdf->Ln();
            }
            process_content( $pdf, $work_package_spec_bug_ids, $version_date, $chapter_prefix, $option_show_duration, $detail_flag );
            $last_chapter_depth = $chapter_depth;
         }
         if ( $detail_flag )
         {
            $pdf->Cell( 0, 0, '', 'T' );
         }
         $pdf->Ln( 7 );
      }
   }

   /** Iterate through issues without defined work package */
   $chapter_prefix = $chapter_counter_array[0] + 1;
   if ( count( $no_work_package_bug_ids ) > 0 )
   {
      $chapter_suffix = plugin_lang_get( 'editor_no_workpackage' );
      $chapter_duration = $specmanagement_database_api->get_workpackage_duration( $p_version_id, '' );
      if ( $detail_flag )
      {
         $pdf->ChapterTitle( $chapter_prefix, utf8_decode( $chapter_suffix ), $option_show_duration, $chapter_duration );
      }
      else
      {
         $pdf->SetFont( 'Arial', 'B', 12 );
         $pdf->SetFillColor( 255, 255, 255 );
         $pdf->Cell( 0, 6, $chapter_prefix . ' ' . utf8_decode( $chapter_suffix ), 0, 0, 'L', 1 );
         $pdf->SetFont( 'Arial', '', 12 );
         $pdf->Ln();
      }
      process_content( $pdf, $no_work_package_bug_ids, $version_date, $chapter_prefix, $option_show_duration, $detail_flag );
      if ( $detail_flag )
      {
         $pdf->Cell( 0, 0, '', 'T' );
      }
      $pdf->Ln( 7 );
      $chapter_prefix++;
   }

   $content_package = array();
   $content_package[0] = $pdf;
   $content_package[1] = ( $chapter_prefix );
   return $content_package;
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
            $pdf->SetFont( 'Arial', 'B', 12 );
            $pdf->Cell( 95, 10, $chapter_prefix . '.' . $bug_counter . ' ' . utf8_decode( string_display( $bug_data[1] ) . ' (' . bug_format_id( $bug_data[0] ) ) . ')' );
            $pdf->SetFont( 'Arial', '', 12 );
            if ( $option_show_duration == '1' && !( $bug_data[7] == 0 || is_null( $bug_data[7] ) ) )
            {
               $pdf->SetFont( 'Arial', 'B', 12 );
               $pdf->Cell( 95, 10, plugin_lang_get( 'editor_bug_duration' ) . ': ' . $bug_data[7] . ' ' . plugin_lang_get( 'editor_duration_unit' ), '', 0, 0 );
               $pdf->SetFont( 'Arial', '', 12 );
            }
            $pdf->Ln();
            $pdf->MultiCell( 0, 10, utf8_decode( trim( $bug_data[2] ) ), 0, 1 );
            $pdf->MultiCell( 0, 10, utf8_decode( trim( $bug_data[3] ) ), 0, 1 );
            $pdf->MultiCell( 0, 10, utf8_decode( trim( $bug_data[4] ) ), 0, 1 );
            if ( !empty( $bug_data[5] ) )
            {
               $bug_attachements = file_get_visible_attachments( $bug_id );
               $bug_attachements_count = count( $bug_attachements );
               $pdf->MultiCell( 0, 10, utf8_decode( plugin_lang_get( 'editor_bug_attachments' ) ) . ' (' . $bug_attachements_count . ')', 0, 1 );
               foreach ( $bug_attachements as $bug_attachement )
               {
//                  var_dump( $bug_attachement );
                  /** TODO: Bilder anzeigen */
                  if ( $bug_attachement['type'] == 'image' )
                  {
                     $file_download_url = config_get_global( 'path' ) . $bug_attachement['download_url'];
//                  $file_download_url = 'https://upload.wikimedia.org/wikipedia/commons/c/c6/Bayerischer_Wald_-_Aufichtenwald_001.jpg';
                     fopen( $file_download_url, 'r' );
                     $contents = file_get_contents( $file_download_url );
                     $savename = '_' . $bug_attachement['id'] . $bug_attachement['display_name'];
                     $savefile = fopen( $savename, 'w' );
                     fwrite( $savefile, $contents );
                     fclose( $savefile );
//                     $pdf->Image( $savename );
                  }
               }
            }
            if ( !is_null( $bug_data[6] ) && $bug_data[6] != 0 )
            {
               $pdf->MultiCell( 0, 10, utf8_decode( plugin_lang_get( 'editor_bug_notes_note' ) ) . ' (' . $bug_data[6] . ')', 0, 1 );
            }
         }
         else
         {
            $pdf->Cell( 95, 10, $chapter_prefix . '.' . $bug_counter . ' ' . utf8_decode( string_display( $bug_data[1] ) . ' (' . bug_format_id( $bug_data[0] ) ) . ')', 0, 0 );
            $pdf->Cell( 95, 10, $pdf->PageNo(), 0, 1, 'R' );
         }
         $bug_counter += 10;
      }
   }
}

/**
 * @param $pdf
 * @param $p_version_id
 * @param $work_packages
 * @param $no_work_package_bug_ids
 * @return mixed
 */
function generate_expenses_overview( PDF $pdf, $p_version_id, $work_packages, $no_work_package_bug_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $document_duration = 0;

   $table_column_widths = Array( 95, 95 );
   $header = Array( plugin_lang_get( 'bug_view_specification_wpg' ), plugin_lang_get( 'bug_view_planned_time' ) . ' ( ' . plugin_lang_get( 'editor_duration_unit' ) . ')' );

   /** Head */
   $pdf->SetFont( 'Arial', 'B', 12 );
   for ( $head_column_index = 0; $head_column_index < count( $header ); $head_column_index++ )
   {
      $pdf->Cell( $table_column_widths[$head_column_index], 7, $header[$head_column_index], 1, 0, 'C' );
   }
   $pdf->SetFont( 'Arial', '', 12 );
   $pdf->Ln();

   /** Body */
   if ( $work_packages != null )
   {
      $document_duration = 0;
      foreach ( $work_packages as $work_package )
      {
         /** go to next record, if work package is empty */
         if ( strlen( $work_package ) == 0 )
         {
            continue;
         }
         $duration = $specmanagement_database_api->get_workpackage_duration( $p_version_id, $work_package );
         if ( is_null( $duration ) )
         {
            $duration = 0;
         }
         $document_duration += $duration;

         $pdf->Cell( $table_column_widths[0], 6, $work_package, 'LR' );
         $pdf->Cell( $table_column_widths[1], 6, $duration, 'LR', 0, 0 );
         $pdf->Ln();
         $pdf->Cell( array_sum( $table_column_widths ), 0, '', 'T' );
         $pdf->Ln();
      }
   }

   if ( count( $no_work_package_bug_ids ) > 0 )
   {
      $sum_no_work_package_bug_duration = 0;

      foreach ( $no_work_package_bug_ids as $no_work_package_bug_id )
      {
         $no_work_package_bug_duration = $specmanagement_database_api->get_bug_duration( $no_work_package_bug_id );
         if ( !is_null( $no_work_package_bug_duration ) )
         {
            $sum_no_work_package_bug_duration += $no_work_package_bug_duration;
         }
      }

      $document_duration += $sum_no_work_package_bug_duration;

      $pdf->Cell( $table_column_widths[0], 6, utf8_decode( plugin_lang_get( 'editor_no_workpackage' ) ), 'LR' );
      $pdf->Cell( $table_column_widths[1], 6, $sum_no_work_package_bug_duration, 'LR', 0, 0 );
      $pdf->Ln();
      $pdf->Cell( array_sum( $table_column_widths ), 0, '', 'T' );
      $pdf->Ln();
   }

   $pdf->SetFont( 'Arial', 'B', 12 );
   $pdf->Cell( $table_column_widths[0], 6, plugin_lang_get( 'editor_expenses_overview_sum' ) . ':', 'LR' );
   $pdf->Cell( $table_column_widths[1], 6, $document_duration, 'LR', 0, 0 );
   $pdf->Ln();
   $pdf->SetFont( 'Arial', '', 12 );
   $pdf->Cell( array_sum( $table_column_widths ), 0, '', 'T' );

   return $pdf;
}

/**
 * @param PDF $pdf
 * @param $type_string
 * @param $version_id
 * @param $version_spec_bug_ids
 * @return PDF
 */
function generate_document_head( PDF $pdf, $type_string, $version_id, $version_spec_bug_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $specmanagement_editor_api = new specmanagement_editor_api();

   $project_id = helper_get_current_project();
   $parent_project_id = $specmanagement_database_api->get_main_project_by_hierarchy( $project_id );
   $head_project_id = $project_id;
   if ( $parent_project_id == 0 )
   {
      $parent_project_id = version_get_field( $version_id, 'project_id' );
      $head_project_id = version_get_field( $version_id, 'project_id' );
   }

   $table_column_widths = Array( 95, 95 );
   $pdf->Cell( array_sum( $table_column_widths ), 0, '', 'T' );
   $pdf->Ln();

   generate_document_head_row( $pdf, 'head_version', version_get_field( $version_id, 'version' ) );
   generate_document_head_row( $pdf, 'manversions_thdoctype', $type_string );
   generate_document_head_row( $pdf, 'head_customer', project_get_name( $parent_project_id ) );
   generate_document_head_row( $pdf, 'head_project', project_get_name( $head_project_id ) );
   generate_document_head_row( $pdf, 'head_date', date( 'd\.m\.Y' ) );
   generate_document_head_row( $pdf, 'head_person_in_charge', $specmanagement_editor_api->calculate_person_in_charge( $version_id ) );
   if ( !is_null( $version_spec_bug_ids ) )
   {
      $process = $specmanagement_editor_api->get_process( $version_spec_bug_ids );
      if ( is_array( $process ) )
      {
         $sum_pt_all = $process[0];
         $sum_pt_bug = $process[1];
         $pt_process = 0;
         if ( $sum_pt_all != 0 )
         {
            $pt_process = round( ( $sum_pt_bug * 100 / $sum_pt_all ), 2 );
         }
         $process_string = $sum_pt_bug . '/' . $sum_pt_all . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ' (' . $pt_process . ' %)';
      }
      else
      {
         $process_string = $process . ' %';
      }
      generate_document_head_row( $pdf, 'head_process', $process_string );
   }

   return $pdf;
}

/**
 * @param PDF $pdf
 * @param $lang_string
 * @param $data
 */
function generate_document_head_row( PDF $pdf, $lang_string, $data )
{
   $table_column_widths = Array( 95, 95 );
   $pdf->Cell( $table_column_widths[0], 6, plugin_lang_get( $lang_string ), 'LR' );
   $pdf->Cell( $table_column_widths[1], 6, $data, 'LR' );
   $pdf->Ln();
   $pdf->Cell( array_sum( $table_column_widths ), 0, '', 'T' );
   $pdf->Ln();
}