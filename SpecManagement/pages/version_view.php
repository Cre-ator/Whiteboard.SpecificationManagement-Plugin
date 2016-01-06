<?php
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$print_api = new print_api();

/**
 * Page content
 */
html_page_top1( plugin_lang_get( 'select_doc_title' ) );
echo '<link rel="stylesheet" href="plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR . 'files/specmanagement.css">';
html_page_top2();
if ( plugin_is_installed( 'WhiteboardMenu' ) )
{
   $print_api->print_whiteboardplugin_menu();
}
$print_api->print_plugin_menu();
echo '<div align="center">';
echo '<hr size="1" width="100%" />';
print_table();
html_page_bottom1();
/* **************************** */

function print_table()
{
   $database_api = new database_api();
   $print_api = new print_api();

   $cols = 5;
   $col_width = 100 / $cols;

   $project_id = helper_get_current_project();
   $versions = version_get_all_rows_with_subs( $project_id, null, null );

   if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
   {
      echo '<table class="width90">';
   }
   else
   {
      echo '<div class="table-container">';
      echo '<table>';
   }

   echo '<thead>';
   $print_api->printFormTitle( $cols, 'versview_thead' );
   echo '<tr class="row-category2">';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . lang_get( 'version' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_deadline' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_amount' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_duration' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_information' ) . '</th>';
   echo '</tr>';
   echo '</thead>';

   echo '<tbody>';
   for ( $version_index = 0; $version_index < count( $versions ); $version_index++ )
   {
      $version = $versions[$version_index];
      $version_deadline = date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) );
      $version_spec_bugs = $database_api->getVersionSpecBugs( $version['version'] );
      $version_spec_bug_count = count( $version_spec_bugs );
      $version_spec_bug_duration = $database_api->getBugDuration( $version_spec_bugs );
      $version_spec_bugs_finished_date = time() + ( $version_spec_bug_duration * 3600 );

      $print_api->printRow();

      /* Name */
      echo '<td>';
      echo string_display( version_full_name( $version['id'] ) );
      echo '</td>';

      /* Date */
      echo '<td>';
      echo $version_deadline;
      echo '</td>';

      /* amount of issues */
      echo '<td>';
      echo string_display( $version_spec_bug_count );
      echo '</td>';

      /* duration */
      echo '<td>';
      echo string_display( $version_spec_bug_duration );
      echo '</td>';

      /* information */
      echo '<td>';
      if ( $version['date_order'] < $version_spec_bugs_finished_date && $version_spec_bug_duration != 0 )
      {
         echo plugin_lang_get( 'versview_deadline' );
      }
      echo '</td>';

      echo '</tr>';
   }
   echo '</tbody>';
   echo '</table>';
   if ( substr( MANTIS_VERSION, 0, 4 ) != '1.2.' )
   {
      echo '</div>';
   }
}