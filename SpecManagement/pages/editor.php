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

   /* Letzte Summary vor Versionstermin */
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

   /* Letzte Priorität vor Versionstermin */
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

   /* Letzte Produktversion vor Versionstermin */
   /**
    * Eintrag #1 Produktversion geändert
    */
   echo '<tr>';
   echo '<td>';
   echo 'Testbetrieb für Issue #1 - Produktversion: ';

   $product_version_value = null;
   $int_filter_string = 'product_version';
   $product_version_value = calculate_lastChange( $ex_bug_id, $version_date, $int_filter_string );
   if ( strlen( $product_version_value ) == 0 )
   {
      $product_version_value = bug_get_field( $ex_bug_id, 'version' );
   }
   echo string_display( $product_version_value );

   echo '</td>';
   echo '</tr>';

   /* Letzter Status vor Versionstermin */
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

   /* Letzte Lösung vor Versionstermin */
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


   echo '</tbody>';
   echo '</table>';

}
html_page_bottom1();

/**
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

//   var_dump( $bug_history_events );

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