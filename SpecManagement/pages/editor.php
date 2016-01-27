<?php
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';

$database_api = new database_api();
$print_api = new print_api();

$type_string = null;
/* initialize version */
$version_id = null;
/* initialize plugin primary key for version */
$p_version_id = null;
/* initialize work packages */
$work_packages = array();
/* initialize bug ids assigned to work package */
$work_package_bug_ids = array();
/* initialize parent project */
$parent_project_id = $database_api->getMainProjectByHierarchy( helper_get_current_project() );

if ( isset( $_POST['version_id'] ) )
{
   $print_flag = false;
   if ( isset( $_POST['print'] ) )
   {
      $print_flag = true;
   }
   $version_id = $_POST['version_id'];
   $version = version_get( $version_id );
   $version_date = $version->date_order;
   $plugin_version_obj = $database_api->getPluginVersionRowByVersionId( $version_id );
   $p_version_id = $plugin_version_obj[0];
   $type_string = $database_api->getTypeString( $database_api->getTypeByVersion( $version_id ) );
   $type_id = $database_api->getTypeId( $type_string );
   $type_row = $database_api->getTypeRow( $type_id );

   $type_options_set = $type_row[2];
   $type_options = explode( ';', $type_options_set );

   $option_show_duration = $type_options[0];
   $option_show_expenses_overview = $type_options[1];

   $work_packages = $database_api->getDocumentSpecWorkPackages( $p_version_id );
   $versionSpecBugIds = $database_api->getVersionSpecBugs( version_full_name( $version_id ) );

   echo '<link rel="stylesheet" href="plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR . 'files/specmanagement.css">';
   html_page_top1( plugin_lang_get( 'editor_title' ) . ': ' . $type_string . ' - ' . version_full_name( $version_id ) );
   if ( !$print_flag )
   {
      html_page_top2();

      if ( plugin_is_installed( 'WhiteboardMenu' ) )
      {
         $print_api->print_whiteboardplugin_menu();
      }
      $print_api->print_plugin_menu();
   }
   print_document_head( $type_string, $version_id, $parent_project_id, $versionSpecBugIds, $print_flag );

   if ( !$print_flag )
   {
      echo '<table class="editor">';
   }
   else
   {
      echo '<table class="editorprint">';
   }

   $chapter_index = 1;

   /**
    * Generate work packages first
    */
   if ( $work_packages != null )
   {
      /* for each work package */
      foreach ( $work_packages as $work_package )
      {
         /* go to next record, if workpackage is empty */
         if ( $work_package == '' )
         {
            continue;
         }

         $duration = $database_api->getWorkpackageDuration( $p_version_id, $work_package );
         /* print work package */
         print_chapter_title( $chapter_index, $work_package, $option_show_duration, $duration );
         /* get work package assigned bugs */
         $work_package_bug_ids = $database_api->getWorkPackageSpecBugs( $p_version_id, $work_package );

         $sub_chapter_index = 10;
         /* for each bug in selected work package */
         foreach ( $work_package_bug_ids as $bug_id )
         {
            /* ensure that bug exists */
            if ( bug_exists( $bug_id ) )
            {
               /* bug data */
               $bug_data = calculate_bug_data( $bug_id, $version_date );
               /* print bugs */
               print_bugs( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
               /* increment index */
               $sub_chapter_index += 10;
               /* remove bug from version spec bugs */
               if ( ( $key = array_search( $bug_id, $versionSpecBugIds ) ) !== false )
               {
                  unset( $versionSpecBugIds[$key] );
               }
            }
         }
         /* increment index */
         $chapter_index++;
      }
   }

   /*
    * If there are bugs left without work packages, print them too, if it is set in the config
    */
   if ( true && !is_null( $versionSpecBugIds ) )
   {
      $duration = $database_api->getBugDuration( $versionSpecBugIds );
      /* print work package */
      print_simple_chapter_title( $chapter_index, $option_show_duration, $duration );

      $sub_chapter_index = 10;
      foreach ( $versionSpecBugIds as $versionSpecBugId )
      {
         /* ensure that bug exists */
         if ( bug_exists( $versionSpecBugId ) )
         {
            /* bug data */
            $bug_data = calculate_bug_data( $versionSpecBugId, $version_date );
            /* print bugs */
            print_bugs( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
            /* increment index */
            $sub_chapter_index += 10;
            /* remove bug from version spec bugs */
            if ( ( $key = array_search( $versionSpecBugId, $versionSpecBugIds ) ) !== false )
            {
               unset( $versionSpecBugIds[$key] );
            }
         }
      }
   }

   echo '</table>';

   if ( $option_show_expenses_overview == '1' )
   {
      print_expenses_overview( $work_packages, $p_version_id, $print_flag );
   }

}
if ( !$print_flag )
{
   html_page_bottom1();
}

/**
 * Gets and returns relevant data for a given bug and date
 *
 * @param $bug_id
 * @param $version_date
 * @return array
 */
function calculate_bug_data( $bug_id, $version_date )
{
   $database_api = new database_api();
   /* Initialize bug data array */
   $bug_data = array();
   /* bug object */
   $bug = bug_get( $bug_id );

   /* ID */
   $bug_data[0] = $bug_id;

   /* Summary */
   $int_filter_string = 'summary';
   $summary_value = calculate_lastChange( $bug_id, $version_date, $int_filter_string );
   if ( strlen( $summary_value ) == 0 )
   {
      $summary_value = bug_get_field( $bug_id, 'summary' );
   }
   $bug_data[1] = $summary_value;

   /* Description */
   $description_value = null;
   $value_type = 1;
   $description_value = calculateLastTextfields( $bug_id, $version_date, $value_type );
   if ( strlen( $description_value ) == 0 )
   {
      $description_value = $bug->description;
   }
   $bug_data[2] = $description_value;

   /* Steps to reproduce */
   $steps_to_reproduce_value = null;
   $value_type = 2;
   $steps_to_reproduce_value = calculateLastTextfields( $bug_id, $version_date, $value_type );
   $bug_data[3] = $steps_to_reproduce_value;

   /* Additional information */
   $additional_information_value = null;
   $value_type = 3;
   $additional_information_value = calculateLastTextfields( $bug_id, $version_date, $value_type );
   $bug_data[4] = $additional_information_value;

   /* Attached files */
   $bug_attachments = bug_get_attachments( $bug_id );
   $bug_data[5] = $bug_attachments;

   /* Notes */
   $bugnote_count_value = null;
   $bugnote_count_value = calculateLastBugnotes( $bug_id, $version_date );
   $bug_data[6] = $bugnote_count_value;

   /* planned duration for each bug */
   $ptime = $database_api->getPtimeRow( $bug_id )[2];
   $bug_data[7] = $ptime;

   return $bug_data;
}

/**
 * Get last change values for:
 * - Summary
 * - Priorität
 * - Produktversion
 * - Zielversion
 * - Behoben in Version
 * - Status
 * - Lösung
 * - Reproduzierbarkeit
 * - Sichtbarkeit
 * - Auswirkung
 * - Bearbeiter
 * - Plattform
 * - OS
 * - OS Version
 *
 * @param $bug_id
 * @param $version_date
 * @param $int_filter_string
 * @return array
 */
function calculate_lastChange( $bug_id, $version_date, $int_filter_string )
{
   $output_value = null;
   $spec_filter_string = lang_get( $int_filter_string );
   $min_time_difference = 0;
   $min_time_difference_event_id = 0;
   $bug_history_events = history_get_events_array( $bug_id );

   for ( $event_index = 0; $event_index < count( $bug_history_events ); $event_index++ )
   {
      $bug_history_event = $bug_history_events[$event_index];

      if ( $bug_history_event['note'] == $spec_filter_string )
      {
         $bug_history_event_date = strtotime( $bug_history_event['date'] );
         $local_time_difference = ( $version_date - $bug_history_event_date );

         /* initial value */
         if ( $min_time_difference == 0 )
         {
            $min_time_difference = $local_time_difference;
            $min_time_difference_event_id = $event_index;
         }

         /* overwrite existing if it is closer to event date */
         if ( $min_time_difference > $local_time_difference )
         {
            $min_time_difference = $local_time_difference;
            $min_time_difference_event_id = $event_index;
         }
      }
   }

   $output_change = $bug_history_events[$min_time_difference_event_id]['change'];
   $output_values = explode( ' => ', $output_change );

   if ( $min_time_difference <= 0 )
   {
      $output_value = $output_values[0];
   }
   else
   {
      $output_value = $output_values[1];
   }

   return $output_value;
}

/**
 * Get last change values for:
 * - Description
 * - Steps to reproduce
 * - Additional information
 *
 * @param $bug_id
 * @param $version_date
 * @param $type_id
 * @return null
 */
function calculateLastTextfields( $bug_id, $version_date, $type_id )
{
   $output_value = null;
   $min_pos_time_difference = 0;
   $min_pos_time_difference_description = null;
   $min_neg_time_difference = 0;
   $min_neg_time_difference_description = null;

   $revision_events = bug_revision_list( $bug_id );

   foreach ( $revision_events as $revision_event )
   {
      if ( $revision_event['type'] == $type_id )
      {
         $revision_event_timestamp = $revision_event['timestamp'];
         $local_time_difference = ( $version_date - $revision_event_timestamp );

         if ( $local_time_difference > 0 )
         {
            /* initial value */
            if ( $min_pos_time_difference == 0 )
            {
               $min_pos_time_difference = $local_time_difference;
               $min_pos_time_difference_description = $revision_event['value'];
            }

            /* overwrite existing if it is closer to event date */
            if ( $min_pos_time_difference > $local_time_difference )
            {
               $min_pos_time_difference = $local_time_difference;
               $min_pos_time_difference_description = $revision_event['value'];
            }
         }
         else
         {
            /* initial value */
            if ( $min_neg_time_difference == 0 )
            {
               $min_neg_time_difference = $local_time_difference;
               $min_neg_time_difference_description = $revision_event['value'];
            }

            /* overwrite existing if it is closer to event date */
            if ( $min_neg_time_difference < $local_time_difference )
            {
               $min_neg_time_difference = $local_time_difference;
               $min_neg_time_difference_description = $revision_event['value'];
            }
         }
      }
   }

   if ( !is_null( $min_pos_time_difference_description ) )
   {
      $output_value = $min_pos_time_difference_description;
   }
   else
   {
      $output_value = $min_neg_time_difference_description;
   }
   return $output_value;
}

/**
 * Get last change values for:
 * - amount of bugotes
 *
 * @param $bug_id
 * @param $version_date
 * @return int
 */
function calculateLastBugnotes( $bug_id, $version_date )
{
   $bugnote_count = 0;

   $bugnotes = bugnote_get_all_bugnotes( $bug_id );
   foreach ( $bugnotes as $bugnote )
   {
      if ( $bugnote->date_submitted <= $version_date )
      {
         $bugnote_count++;
      }
   }
   return $bugnote_count;
}

/**
 * Gets the managers of the current selected project
 *
 * @return string
 */
function calculate_person_in_charge()
{
   $person_in_charge = '';
   $project_related_users = project_get_local_user_rows( helper_get_current_project() );
   $count = 0;
   foreach ( $project_related_users as $project_related_user )
   {
      if ( $project_related_user['project_id'] == helper_get_current_project()
         && $project_related_user['access_level'] == 70
      )
      {
         if ( $count > 0 )
         {
            $person_in_charge .= ', ';
         }
         $person_in_charge .= user_get_realname( $project_related_user['user_id'] );
         $count++;
      }
   }
   return $person_in_charge;
}

/**
 * Gets the sum of each planned time for a bunch of issues
 *
 * @param $allRelevantBugs
 * @return array
 */
function calculate_pt_doc_progress( $allRelevantBugs )
{
   $database_api = new database_api();
   $sum_pt = array();
   $sum_pt_all = 0;
   $sum_pt_bug = 0;
   foreach ( $allRelevantBugs as $bug_id )
   {
      $ptime_row = $database_api->getPtimeRow( $bug_id );
      if ( !is_null( $ptime_row[2] ) || 0 != $ptime_row[2] )
      {
         $sum_pt_all += $ptime_row[2];
         if ( bug_get_field( $bug_id, 'status' ) == PLUGINS_SPECMANAGEMENT_STAT_RESOLVED
            || bug_get_field( $bug_id, 'status' ) == PLUGINS_SPECMANAGEMENT_STAT_CLOSED
         )
         {
            $sum_pt_bug += $ptime_row[2];
         }
      }
   }
   array_push( $sum_pt, $sum_pt_all );
   array_push( $sum_pt, $sum_pt_bug );

   return $sum_pt;
}

/**
 * Prints a specific information of a bug
 *
 * @param $string
 */
function print_bug_infos( $string )
{
   if ( !is_null( $string ) )
   {
      echo '<tr>';
      echo '<td colspan="1" />';
      echo '<td colspan="2">' . $string . '</td>';
      echo '</tr>';
   }
}

/**
 * Prints the information that there are notes
 *
 * @param $bugnote_count_value
 */
function print_bugnote_note( $bugnote_count_value )
{
   echo '<tr>';
   echo '<td colspan="1" />';
   echo '<td class="infohead" colspan="2">' . plugin_lang_get( 'editor_bug_notes_note' ) . ' (' . $bugnote_count_value . ')</td>';
   echo '</tr>';
}

/**
 * Prints bug-specific attachments
 *
 * @param $bug_id
 */
function print_bug_attachments( $bug_id )
{
   $print_api = new print_api();
   $attachment_count = file_bug_attachment_count( $bug_id );
   echo '<tr>';
   echo '<td colspan="1" />';
   echo '<td class="infohead" colspan="2">' . plugin_lang_get( 'editor_bug_attachments' ) . ' (' . $attachment_count . ')</td>';
   echo '</tr>';

   echo '<tr id="attachments">';
   echo '<td colspan="1" />';
   echo '<td class="bug-attachments" colspan="2">';
   $print_api->print_bug_attachments_list( $bug_id );
   echo '</td>';
   echo '</tr>';
}

/**
 * Prints a bug into the document
 *
 * @param $chapter_index
 * @param $sub_chapter_index
 * @param $bug_data
 * @param $option_show_duration
 * @param $print_flag
 */
function print_bugs( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag )
{
   echo '<tr>';
   echo '<td class="form-title" colspan="1">' . $chapter_index . '.' . $sub_chapter_index . '</td>';
   echo '<td class="form-title" colspan="2">' . string_display( $bug_data[1] ) . ' (';
   if ( !$print_flag )
   {
      print_bug_link( $bug_data[0], true );
   }
   else
   {
      echo bug_format_id( $bug_data[0] );
   }
   echo ')';
   if ( $option_show_duration == '1' )
   {
      echo ', ' . plugin_lang_get( 'editor_bug_duration' ) . ': ' . $bug_data[7] . ' ' . plugin_lang_get( 'editor_duration_unit' );
   }
   echo '</td>';
   echo '</tr>';

   print_bug_infos( string_display_links( $bug_data[2] ) );
   print_bug_infos( string_display_links( $bug_data[3] ) );
   print_bug_infos( string_display_links( $bug_data[4] ) );
   if ( !empty( $bug_data[5] ) )
   {
      print_bug_attachments( $bug_data[0] );
   }
   if ( !is_null( $bug_data[6] ) && $bug_data[6] != 0 )
   {
      print_bugnote_note( $bug_data[6] );
   }
}

/**
 * Prints the header element of a document
 *
 * @param $type_string
 * @param $version_id
 * @param $parent_project_id
 * @param $allRelevantBugs
 * @param $print_flag
 */
function print_document_head( $type_string, $version_id, $parent_project_id, $allRelevantBugs, $print_flag )
{
   $versions = version_get_all_rows( helper_get_current_project() );
   $act_version = version_get( $version_id );

   if ( !$print_flag )
   {
      echo '<table class="editor">';
   }
   else
   {
      echo '<table class="editorprint">';
   }
   echo '<tr>';
   echo '<td class="field-container">' . plugin_lang_get( 'head_title' ) . '</td>';
   echo '<td class="form-title" colspan="2">' . $type_string . ' - ' . version_full_name( $version_id ) . '</td>';
   if ( !$print_flag )
   {
      echo '<td class="form-title" colspan="1">';
      echo '<form action="' . plugin_page( 'editor' ) . '" method="post">';
      echo '<span class="input">';
      echo '<input type="hidden" name="version_id" value="' . $version_id . '" />';
      echo '<input type="submit" name="print" class="button" value="' . lang_get( 'print' ) . '"/>';
      echo '</span>';
      echo '</form>';
      echo '</td>';
   }
   echo '</tr>';

   print_doc_head_row( 'head_version', version_full_name( $version_id ) );
   print_doc_head_row( 'head_customer', project_get_name( $parent_project_id ) );
   print_doc_head_row( 'head_project', project_get_name( helper_get_current_project() ) );
   print_doc_head_row( 'head_date', date( 'j\.m\.Y' ) );
   print_doc_head_row( 'head_person_in_charge', calculate_person_in_charge() );
   if ( !is_null( $allRelevantBugs ) )
   {
      print_doc_head_row( 'head_process', print_document_progress( $allRelevantBugs ) );
   }
   if ( !$print_flag )
   {
      print_doc_head_versions( $versions, $act_version );
   }
   echo '</table>';
   echo '<br />';
}

/**
 * Prints a new chapter title element in a document
 *
 * @param $chapter_index
 * @param $option_show_duration
 * @param $duration
 */
function print_simple_chapter_title( $chapter_index, $option_show_duration, $duration )
{
   if ( is_null( $duration ) )
   {
      $duration = plugin_lang_get( 'editor_work_package_duration_null' );
   }

   echo '<tr>';
   echo '<td class="form-title" colspan="1">' . $chapter_index . '</td>';
   echo '<td class="form-title" colspan="2">' . plugin_lang_get( 'editor_no_workpackage' );
   if ( $option_show_duration == '1' )
   {
      echo ' [' . plugin_lang_get( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ']';
   }
   echo '</td>';
   echo '</tr>';
}

/**
 * Prints a new chapter title element in a document
 *
 * @param $chapter_index
 * @param $work_package
 * @param $option_show_duration
 * @param $duration
 */
function print_chapter_title( $chapter_index, $work_package, $option_show_duration, $duration )
{
   if ( is_null( $duration ) )
   {
      $duration = plugin_lang_get( 'editor_work_package_duration_null' );
   }

   echo '<tr>';
   echo '<td class="form-title" colspan="1">' . $chapter_index . '</td>';
   echo '<td class="form-title" colspan="2">' . $work_package;
   if ( $option_show_duration == '1' )
   {
      echo ' [' . plugin_lang_get( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ']';
   }
   echo '</td>';
   echo '</tr>';
}

/**
 * Prints the expenses overview area
 *
 * @param $work_packages
 * @param $p_version_id
 * @param $print_flag
 */
function print_expenses_overview( $work_packages, $p_version_id, $print_flag )
{
   $database_api = new database_api();

   echo '<br />';
   if ( !$print_flag )
   {
      echo '<table class="editor">';
   }
   else
   {
      echo '<table class="editorprint">';
   }
   print_expenses_overview_head();

   echo '<tbody>';
   if ( $work_packages != null )
   {
      $document_duration = 0;
      foreach ( $work_packages as $work_package )
      {
         /* go to next record, if workpackage is empty */
         if ( $work_package == '' )
         {
            continue;
         }

         $duration = $database_api->getWorkpackageDuration( $p_version_id, $work_package );
         $document_duration += $duration;
         echo '<tr>';
         echo '<td colspan="1">' . $work_package . '</td>';
         echo '<td colspan="1">' . $duration . '</td>';
         echo '</tr>';
      }
      echo '<tr>';
      echo '<td colspan="2"><hr width="100%" align="center" /></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td />';
      echo '<td>' . plugin_lang_get( 'editor_expenses_overview_sum' ) . ': ' . $document_duration . '</td>';
      echo '</tr>';
   }
   echo '</tbody>';
   echo '</table>';
}

/**
 * Prints the head of the expenses overview area
 */
function print_expenses_overview_head()
{
   echo '<thead>';
   echo '<tr>';
   echo '<td class="form-title" colspan="2">' . plugin_lang_get( 'editor_expenses_overview' ) . '</td>';
   echo '</tr>';

   echo '<tr class="row-category">';
   echo '<th colspan="1">' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</th>';
   echo '<th colspan="1">' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')</th>';
   echo '</tr>';
   echo '</thead>';
}

/**
 * Prints a row into the document head
 *
 * @param $lang_string
 * @param $col_data
 */
function print_doc_head_row( $lang_string, $col_data )
{
   echo '<tr>';
   echo '<td class="field-container">' . plugin_lang_get( $lang_string ) . '</td>';
   echo '<td class="form-title" colspan="3">' . $col_data . '</td>';
   echo '</tr>';
}

/**
 * Prints all available versions into the document head
 *
 * @param $versions
 * @param $act_version
 */
function print_doc_head_versions( $versions, $act_version )
{
   foreach ( $versions as $version )
   {
      $same_version = $act_version->id == $version['id'];
      echo '<tr>';
      print_doc_head_version_col( $same_version, date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) ) );
      print_doc_head_version_col( $same_version, version_full_name( $version['id'] ) );
      $change_button_string = '<form method="post" name="form_set_source" action="' . plugin_page( 'changes' ) . '">'
         . '<input type="hidden" name="version_old" value="' . $version['id'] . '" />'
         . '<input type="hidden" name="version_act" value="' . $act_version->id . '" />'
         . '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get( 'head_changes' ) . '"/>'
         . '</form>';
      if ( $same_version )
      {
         print_doc_head_version_col( $same_version, '' );
      }
      else
      {
         print_doc_head_version_col( $same_version, $change_button_string );
      }
      $show_button_string = '<form method="post" name="form_set_source" action="' . plugin_page( 'editor' ) . '">'
         . '<input type="hidden" name="version_id" value="' . $version['id'] . '" />'
         . '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get( 'head_view' ) . '"/>'
         . '</form>';
      print_doc_head_version_col( $same_version, $show_button_string );
      echo '</tr>';
   }
}

/**
 * Prints a column for a version in the document head area
 *
 * @param $same_version
 * @param $data
 */
function print_doc_head_version_col( $same_version, $data )
{
   if ( $same_version )
   {
      echo '<td class="selected">';
   }
   else
   {
      echo '<td>';
   }
   echo $data;
   echo '</td>';
}

/**
 * Prints the process of a document
 *
 * @param $allRelevantBugs
 * @return string
 */
function print_document_progress( $allRelevantBugs )
{
   $database_api = new database_api();
   $print_api = new print_api();
   $process_string = '';
   $status_flag = false;

   foreach ( $allRelevantBugs as $bug_id )
   {
      $ptime_row = $database_api->getPtimeRow( $bug_id );
      if ( is_null( $ptime_row[2] ) || 0 == $ptime_row[2] )
      {
         $status_flag = true;
         break;
      }
   }

   if ( $status_flag )
   {
      $status_process = 0;
      if ( !empty( $allRelevantBugs ) )
      {
         $status_process = $print_api->calculate_status_doc_progress( $allRelevantBugs );
      }

      $process_string .= '<div class="progress400">';
      $process_string .= '<span class="bar" style="width: ' . $status_process . '%;">' . round( $status_process, 2 ) . '%</span>';
      $process_string .= '</div>';
   }
   else
   {
      $sum_pt = calculate_pt_doc_progress( $allRelevantBugs );
      $sum_pt_all = $sum_pt[0];
      $sum_pt_bug = $sum_pt[1];
      $pt_process = 0;

      if ( $sum_pt_all != 0 )
      {
         $pt_process = $sum_pt_bug * 100 / $sum_pt_all;
      }

      $process_string .= '<div class="progress400">';
      $process_string .= '<span class="bar" style="width: ' . $pt_process . '%;">' . $sum_pt_bug . '/' . $sum_pt_all . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ' (' . $pt_process . '%)</span>';
      $process_string .= '</div>';
   }
   return $process_string;
}