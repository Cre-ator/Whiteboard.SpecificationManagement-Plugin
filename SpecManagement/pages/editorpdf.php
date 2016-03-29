<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';
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

   /** generate and print page content */

   $pdf = new PDF();
   $pdf->AliasNbPages();
   $pdf->AddPage();
   $pdf->SetFont( 'Arial', '', 12 );
   $pdf->Output();
}
