<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

if ( isset( $_POST['version_other'] ) && isset( $_POST['version_my'] ) )
{
   if ( $_POST['version_other'] != $_POST['version_my'] )
   {
      calculate_changes();
   }
}

/**
 * Calculate page content
 */
function calculate_changes()
{
   $specmanagement_print_api = new specmanagement_print_api();

   $other_version = version_get( $_POST['version_other'] );
   $my_version = version_get( $_POST['version_my'] );
   $specified_versions = specify_version( $my_version, $other_version );
   $old_version = $specified_versions[0];
   $new_version = $specified_versions[1];

   $specmanagement_print_api->print_page_head( plugin_lang_get( 'changes_title' ) . ': ' . $old_version->version . ' / ' . $new_version->version );
   $specmanagement_print_api->printTableTop( '60' );
   print_changes_table_head( $old_version, $new_version );
   print_changes_table_body( $old_version, $new_version );
   $specmanagement_print_api->printTableFoot();
   html_page_bottom1();
}

/**
 * @param $my_version
 * @param $other_version
 * @return array
 */
function specify_version( $my_version, $other_version )
{
   $version_array = array();
   if ( $my_version->date_order < $other_version->date_order )
   {
      $version_array[0] = $my_version;
      $version_array[1] = $other_version;
   }
   else
   {
      $version_array[0] = $other_version;
      $version_array[1] = $my_version;
   }

   return $version_array;
}

/**
 * @param $old_version
 * @param $new_version
 */
function print_changes_table_body( $old_version, $new_version )
{
   $old_version_data = get_version_data( $old_version );
   $new_version_data = get_version_data( $new_version );
   $all_issues = initialize_bug_array( $old_version_data[0], $new_version_data[0] );

   echo '<tbody>';
   foreach ( $all_issues as $issue )
   {
      echo '<tr>';
      if ( check_inserted( $issue, $old_version_data[0], $new_version_data[0] ) )
      {
         echo '<td colspan="2"></td><td colspan="2" class="center">';
         echo print_bug_link( $issue, true ) . ' ( + ' . plugin_lang_get( 'changes_inserted' ) . ')';
         echo '</td>';
      }
      if ( check_removed( $issue, $old_version_data[0], $new_version_data[0] ) )
      {
         echo '<td colspan="2" class="center">';
         echo print_bug_link( $issue, true ) . ' ( - ' . plugin_lang_get( 'changes_removed' ) . ')';
         echo '</td><td colspan="2"></td>';
      }
      if ( check_edited( $issue, $old_version_data[0], $new_version_data[0] ) )
      {
         for ( $index = 0; $index < 2; $index++ )
         {
            echo '<td colspan="2" class="center">';
            echo print_bug_link( $issue, true ) . ' ( # ' . plugin_lang_get( 'changes_edited' ) . ')';
            echo '</td>';
         }
      }
      echo '</tr>';
   }
   echo '</tbody>';
}

/**
 * @param $old_version_data
 * @param $new_version_data
 * @return mixed
 */
function initialize_bug_array( $old_version_data, $new_version_data )
{
   $all_issues = array();
   foreach ( $old_version_data as $old_issue )
   {
      array_push( $all_issues, $old_issue );
   }
   foreach ( $new_version_data as $new_issue )
   {
      /**
       * ist ein Issue in beiden Arrays enthalten
       * => wurde es geändert
       * => muss dennoch nicht zwei Mal gelistet werden!
       */
      if ( in_array( $new_issue, $all_issues ) )
      {
         continue;
      }
      else
      {
         array_push( $all_issues, $new_issue );
      }
   }
   sort( $all_issues );

   return $all_issues;
}

/**
 * @param $issue
 * @param $old_issues
 * @param $new_issues
 * @return bool
 */
function check_inserted( $issue, $old_issues, $new_issues )
{
   if ( !in_array( $issue, $old_issues ) && in_array( $issue, $new_issues ) )
   {
      return true;
   }
   else
   {
      return false;
   }
}

/**
 * @param $issue
 * @param $old_issues
 * @param $new_issues
 * @return bool
 */
function check_removed( $issue, $old_issues, $new_issues )
{
   if ( in_array( $issue, $old_issues ) && !in_array( $issue, $new_issues ) )
   {
      return true;
   }
   else
   {
      return false;
   }
}

/**
 * @param $issue
 * @param $old_issues
 * @param $new_issues
 * @return bool
 */
function check_edited( $issue, $old_issues, $new_issues )
{
   if ( in_array( $issue, $old_issues ) && in_array( $issue, $new_issues ) )
   {
      return true;
   }
   else
   {
      return false;
   }
}

/**
 * Get issue data for specific versions
 *
 * @param $version
 * @return array
 */
function get_version_data( $version )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $version_data = array();

   /* Prjekte sammeln */
   $project_ids = prepare_relevant_projects();
   /* Issues sammeln */
   $reachable_issue_ids = prepare_relevant_issues( $project_ids );
   /* Unpassende Issues aussortieren */
   $relevant_issue_ids = calculate_relevant_issues( $version, $reachable_issue_ids );
   /* Dauer für relevante Issues berechnen */
   $relevant_issues_duration = $specmanagement_database_api->get_bug_array_duration( $relevant_issue_ids );
   /* Fortschritt berechnen */
   $status_process = calculate_status( $relevant_issue_ids );
   /* Daten sammeln */
   $version_data[0] = $relevant_issue_ids;
   $version_data[1] = $relevant_issues_duration;
   $version_data[2] = $status_process;

   return $version_data;
}

/**
 * @param $relevant_issue_ids
 * @return array
 */
function calculate_status( $relevant_issue_ids )
{
   $specmanagement_print_api = new specmanagement_print_api();
   $status_process = null;
   if ( count( $relevant_issue_ids ) > 0 )
   {
      $relevant_issue_ids = array_merge( $relevant_issue_ids );
      $status_process = $specmanagement_print_api->calculate_status_doc_progress( $relevant_issue_ids );
   }
   return $status_process;
}

/**
 * @param $version
 * @param $reachable_issue_ids
 */
function calculate_relevant_issues( $version, $reachable_issue_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $version_date = $version->date_order;
   $int_filter_string = 'target_version';
   /* Prüfen ob Bug zum gegebenen Zeitpunkt dieser Zielversion zugeordnet war */
   foreach ( $reachable_issue_ids as $reachable_issue_id )
   {
      $target_version = $specmanagement_database_api->calculate_last_change( $reachable_issue_id, $version_date, $int_filter_string );
      if ( $target_version != $version->version )
      {
         if ( ( $key = array_search( $reachable_issue_id, $reachable_issue_ids ) ) !== false )
         {
            unset( $reachable_issue_ids[$key] );
         }
      }
   }
   return $reachable_issue_ids;
}

/**
 * @param $project_ids
 * @return mixed
 */
function prepare_relevant_issues( $project_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $reachable_issue_ids = array();

   foreach ( $project_ids as $project_id )
   {
      $project_related_issue_ids = $specmanagement_database_api->get_bugs_by_project( $project_id );
      if ( !is_null( $project_related_issue_ids ) )
      {
         foreach ( $project_related_issue_ids as $project_related_issue_id )
         {
            array_push( $reachable_issue_ids, $project_related_issue_id );
         }
      }
   }
   return $reachable_issue_ids;
}

/**
 * @return array
 */
function prepare_relevant_projects()
{
   $project_ids = array();
   $project_id = helper_get_current_project();
   $sub_project_ids = project_hierarchy_get_all_subprojects( $project_id );
   array_push( $project_ids, $project_id );
   foreach ( $sub_project_ids as $sub_project_id )
   {
      array_push( $project_ids, $sub_project_id );
   }
   return $project_ids;
}

/**
 * @param $old_version
 * @param $act_version
 */
function print_changes_table_head( $old_version, $act_version )
{
   $old_version_data = get_version_data( $old_version );
   $act_version_data = get_version_data( $act_version );
   echo '<thead>';
   echo '<tr>';
   echo '<th colspan="2" class="center">' . $old_version->version . '</th>';
   echo '<th colspan="2" class="center">' . $act_version->version . '</th>';
   echo '</tr>';

   echo '<tr>';
   print_version_deadline( $old_version );
   print_version_deadline( $act_version );
   echo '</tr>';

   echo '<tr>';
   print_version_progress( $old_version_data[1], $old_version_data[2] );
   print_version_progress( $act_version_data[1], $act_version_data[2] );
   echo '</tr>';

   echo '<tr>';
   echo '<td colspan="4">';
   echo '<hr width="100%"/>';
   echo '</td>';
   echo '</tr>';
   echo '</thead>';
}

/**
 * @param $relevant_issues_duration
 * @param $status_process
 */
function print_version_progress( $relevant_issues_duration, $status_process )
{
   $status_process_round = round( $status_process, 2 );
   echo '<td>';
   echo plugin_lang_get( 'versview_progress' );
   echo '</td>';
   echo '<td class="progress400">';
   if ( $relevant_issues_duration > 0 )
   {
      echo $status_process_round . '%';
   }
   echo '</td>';
}

/**
 * @param $version
 */
function print_version_deadline( $version )
{
   echo '<td>';
   echo plugin_lang_get( 'versview_deadline' );
   echo '</td>';
   echo '<td>';
   echo date_is_null( $version->date_order ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version->date_order ) );
   echo '</td>';
}