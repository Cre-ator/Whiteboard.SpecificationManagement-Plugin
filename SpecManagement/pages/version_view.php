<?php
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$print_api = new print_api();

$obsolete_flag = false;
if ( isset( $_POST['obsolete_flag'] ) )
{
   $obsolete_flag = null;
}

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
print_table( $obsolete_flag );
if ( helper_get_current_project() != 0 )
{
   print_graph( $obsolete_flag );
}
html_page_bottom1();
/* **************************** */

function print_table( $obsolete_flag )
{
   $database_api = new database_api();
   $print_api = new print_api();

   $cols = 6;
   $col_width = 100 / $cols;

   $project_id = helper_get_current_project();
   $versions = version_get_all_rows_with_subs( $project_id, null, $obsolete_flag );

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
   echo '<tr>';
   echo '<td class="form-title" colspan="' . ( $cols - 1 ) . '">' . plugin_lang_get( 'versview_thead' ) . '</td>';
   echo '<td colspan="1"><form action="' . plugin_page( 'version_view' ) . '" method="post">';
   echo '<input type="submit" name="obsolete_flag" class="button" value="' . plugin_lang_get( 'versview_obsolete_flag' ) . '"/>';
   echo '</form></td>';
   echo '</tr>';
   echo '<tr class="row-category2">';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . lang_get( 'version' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_deadline' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_amount' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_duration' ) . '</th>';
   echo '<th class="form-title" colspan="1" width="' . $col_width . '">' . plugin_lang_get( 'versview_progress' ) . '</th>';
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
      $status_process = null;

      if ( !is_null( $version_spec_bugs ) )
      {
         $status_process = $print_api->calculate_status_doc_progress( $version_spec_bugs );
      }

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
      if ( $version_spec_bug_count > 0 && !$version['obsolete'] )
      {
         echo '<a href="search.php?project_id=' . $project_id . '&target_version=' . $version['version'] .
            '&sortby=last_updated&dir=DESC&hide_status_id=-2&match_type=0">';
      }
      echo string_display( $version_spec_bug_count );
      if ( $version_spec_bug_count > 0 && !$version['obsolete'] )
      {
         echo '</a>';
      }
      echo '</td>';

      /* duration */
      echo '<td>';
      echo string_display( $version_spec_bug_duration );
      echo '</td>';

      /* progress */
      echo '<td>';
      if ( $version_spec_bug_duration > 0 )
      {
         echo $status_process . '%';
      }
      echo '</td>';

      /* information */
      echo '<td>';
      if ( $version['date_order'] < $version_spec_bugs_finished_date && $version_spec_bug_duration != 0 )
      {
         echo plugin_lang_get( 'versview_deadline_n' ) . '<br/>';
      }
      if ( $version['obsolete'] )
      {
         echo plugin_lang_get( 'versview_obsolete' ) . '<br/>';
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

function print_graph( $obsolete_flag )
{
   $print_api = new print_api();

   $project_id = helper_get_current_project();
   $versions = version_get_all_rows_with_subs( $project_id, null, $obsolete_flag );

   $version_hash = array();

   for ( $version_index = count( $versions ) - 1; $version_index >= 0; $version_index-- )
   {
      $version = $versions[$version_index];

      $version_record = array();
      array_push( $version_record, $version['id'] );
      array_push( $version_record, $version['date_order'] );

      array_push( $version_hash, $version_record );
   }

   if ( !empty( $version_hash ) )
   {
      echo '<br/>';
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
      $print_api->printFormTitle( null, 'versview_theadgraph' );
      echo '</thead>';

      echo '<tbody>';
      echo '<tr>';
      echo '<td class="center">';

      foreach ( $version_hash as $version_value )
      {
         $version_id = $version_value[0];
         $version_name = version_get_field( $version_id, 'version' );
         $version_date = date_is_null( $version_value[1] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version_value[1] ) );
         echo ' <img border="0" src="' . SPECMANAGEMENT_PLUGIN_URL . 'files/rel_next_version.png"/> ' . $version_name . ' [' . $version_date . ']';
      }

      echo '</td>';
      echo '</tr>';
      echo '</tbody>';

      echo '</table>';
      if ( substr( MANTIS_VERSION, 0, 4 ) != '1.2.' )
      {
         echo '</div>';
      }
   }
}