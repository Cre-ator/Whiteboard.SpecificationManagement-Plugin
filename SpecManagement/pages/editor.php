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
   $version_id = $_POST['version_id'];
   $version_obj = $database_api->getPluginVersionRowByVersionId( $version_id );
   $p_version_id = $version_obj[0];
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
   html_page_top2();

   if ( plugin_is_installed( 'WhiteboardMenu' ) )
   {
      $print_api->print_whiteboardplugin_menu();
   }

   $print_api->print_plugin_menu();
//   $print_api->print_editor_menu();
   $print_api->print_document_head( $type_string, $version_id, $parent_project_id, $versionSpecBugIds );

   echo '<table class="width60">';

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
         $print_api->print_chapter_title( $chapter_index, $work_package, $option_show_duration, $duration );
         /* get work package assigned bugs */
         $work_package_bug_ids = $database_api->getWorkPackageSpecBugs( $p_version_id, $work_package );

         $sub_chapter_index = 10;
         /* for each bug in selected work package */
         foreach ( $work_package_bug_ids as $bug_id )
         {
            /* ensure that bug exists */
            if ( bug_exists( $bug_id ) )
            {
               /* planned duration for each bug */
               $ptime = $database_api->getPtimeRow( $bug_id )[2];
               /* print bugs */
               $print_api->print_bugs( $chapter_index, $sub_chapter_index, $bug_id, $option_show_duration, $ptime );
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
    * TODO: set config
    */
   if ( true && !is_null( $versionSpecBugIds ) )
   {
      $duration = $database_api->getBugDuration( $versionSpecBugIds );
      /* print work package */
      $print_api->print_simple_chapter_title( $chapter_index, $option_show_duration, $duration );

      $sub_chapter_index = 10;
      foreach ( $versionSpecBugIds as $versionSpecBugId )
      {
         /* ensure that bug exists */
         if ( bug_exists( $versionSpecBugId ) )
         {
            /* planned duration for each bug */
            $ptime = $database_api->getPtimeRow( $versionSpecBugId )[2];
            /* print bugs */
            $print_api->print_bugs( $chapter_index, $sub_chapter_index, $versionSpecBugId, $option_show_duration, $ptime );
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
      echo '<br /><table class="width60">';
      $print_api->print_expenses_overview_head();

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
   echo '<br /><table class="width60">';
   echo '<thead><tr><th>Testbereich Bug Historie zu gegebener Version und deren Termin</th></tr></thead>';
   echo '<tbody>';

   $ex_bug_id = 1;

   $version = version_get( $version_id );
   $version_date = $version->date_order;

   /**
    * Eintrag #1 Summary geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Zusammenfassung: ';

   $summary_value = null;
   $int_filter_string = 'summary';
   $summary_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $summary_value ) == 0 )
   {
      $summary_value = bug_get_field( $ex_bug_id, 'summary' );
   }
   echo string_display( $summary_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Priorität geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Priorität: ';

   $priority_value = null;
   $int_filter_string = 'priority';
   $priority_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $priority_value ) == 0 )
   {
      $priority_value = bug_get_field( $ex_bug_id, 'priority' );
   }
   echo string_display( $priority_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Produktversion geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Produktversion: ';

   $product_version_value = null;
   $int_filter_string = 'product_version';
   $product_version_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display( $product_version_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Status geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Status: ';

   $status_value = null;
   $int_filter_string = 'status';
   $status_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $status_value ) == 0 )
   {
      $status_value = bug_get_field( $ex_bug_id, 'status' );
   }
   echo string_display( $status_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Lösung geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Lösung: ';

   $resolution_value = null;
   $int_filter_string = 'resolution';
   $resolution_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $resolution_value ) == 0 )
   {
      $resolution_value = get_enum_element( 'resolution', bug_get_field( $ex_bug_id, 'resolution' ) );
   }
   echo string_display_line( $resolution_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Bearbeiter geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Bearbeiter: ';

   $handler_value = null;
   $int_filter_string = 'assigned_to';
   $handler_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display_line( $handler_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Zielversion geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Zielversion: ';

   $target_version_value = null;
   $int_filter_string = 'target_version';
   $target_version_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display_line( $target_version_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Behoben in Version geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Behoben in Version: ';

   $fixed_in_version_value = null;
   $int_filter_string = 'fixed_in_version';
   $fixed_in_version_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display_line( $fixed_in_version_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Reproduzierbarkeit geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Reproduzierbarkeit: ';

   $reproducibility_value = null;
   $int_filter_string = 'reproducibility';
   $reproducibility_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $reproducibility_value ) == 0 )
   {
      $reproducibility_value = get_enum_element( 'reproducibility', bug_get_field( $ex_bug_id, 'reproducibility' ) );
   }
   echo string_display_line( $reproducibility_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Sichtbarkeit geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Sichtbarkeit: ';

   $view_state_value = null;
   $int_filter_string = 'view_status';
   $view_state_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $view_state_value ) == 0 )
   {
      $view_state_value = get_enum_element( 'view_status', bug_get_field( $ex_bug_id, 'view_state' ) );
   }
   echo string_display_line( $view_state_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Auswirkung geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Auswirkung: ';

   $severity_value = null;
   $int_filter_string = 'severity';
   $severity_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $severity_value ) == 0 )
   {
      $severity_value = get_enum_element( 'severity', bug_get_field( $ex_bug_id, 'severity' ) );
   }
   echo string_display_line( $severity_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Plattform geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Plattform: ';

   $platform_value = null;
   $int_filter_string = 'platform';
   $platform_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display_line( $platform_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 OS geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - OS: ';

   $os_value = null;
   $int_filter_string = 'os';
   $os_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display_line( $os_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 OS Version geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - OS Version: ';

   $os_build_value = null;
   $int_filter_string = 'os_version';
   $os_build_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   echo string_display_line( $os_build_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Beschreibung geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Beschreibung: ';

   $description_value = null;
   $value_type = 1;
   $description_value = calculateLastTextfields( $ex_bug_id, $version_date, $value_type );
   echo string_display_line( $description_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Schritte zur Reproduktion geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Schritte zur Reproduktion: ';

   $steps_to_reproduce_value = null;
   $value_type = 2;
   $steps_to_reproduce_value = calculateLastTextfields( $ex_bug_id, $version_date, $value_type );
   echo string_display_line( $steps_to_reproduce_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Zusätzliche Informationen geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Zusätzliche Informationen: ';

   $additional_information_value = null;
   $value_type = 3;
   $additional_information_value = calculateLastTextfields( $ex_bug_id, $version_date, $value_type );
   echo string_display_line( $additional_information_value );

   echo '</td>';
   echo '</tr>';

   /**
    * Eintrag #1 Anzahl Notizen geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Anzahl Notizen: ';

   $bugnote_count_value = null;
   $bugnote_count_value = calculateLastBugnotes( $ex_bug_id, $version_date );
   echo string_display_line( $bugnote_count_value );

   echo '</td>';
   echo '</tr>';


   echo '</tbody>';
   echo '</table>';

}
html_page_bottom1();

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
 * @param $ex_bug_id
 * @param $version_date
 * @param $int_filter_string
 * @return array
 */
function calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string )
{
   $output_value = null;
   $spec_filter_string = lang_get( $int_filter_string );

   $min_time_difference = 0;
   $min_time_difference_event_id = 0;
   $bug_history_events = history_get_events_array( $ex_bug_id );

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
 * @param $ex_bug_id
 * @param $version_date
 * @param $type_id
 * @return null
 */
function calculateLastTextfields( $ex_bug_id, $version_date, $type_id )
{
   $output_value = null;
   $min_pos_time_difference = 0;
   $min_pos_time_difference_description = null;
   $min_neg_time_difference = 0;
   $min_neg_time_difference_description = null;

   $revision_events = bug_revision_list( $ex_bug_id );

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
 * @param $ex_bug_id
 * @param $version_date
 * @return int
 */
function calculateLastBugnotes( $ex_bug_id, $version_date )
{
   $bugnote_count = 0;

   $bugnotes = bugnote_get_all_bugnotes( $ex_bug_id );
   foreach ( $bugnotes as $bugnote )
   {
      if ( $bugnote->date_submitted <= $version_date )
      {
         $bugnote_count++;
      }
   }
   return $bugnote_count;
}