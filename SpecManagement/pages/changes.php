<?php
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';

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
   $print_api = new print_api();

   $other_version = version_get( $_POST['version_other'] );
   $my_version = version_get( $_POST['version_my'] );
   $specified_versions = specify_version( $my_version, $other_version );
   $old_version = $specified_versions[0];
   $new_version = $specified_versions[1];

   print_page_top( $old_version, $new_version );
   $print_api->printTableTop( '60' );
   print_changes_table_head( $old_version, $new_version );
   print_changes_table_body( $old_version, $new_version );
   $print_api->printTableFoot();
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
   $all_bugs = initialize_bug_array( $old_version_data[0], $new_version_data[0] );

   echo '<tbody>';
   foreach ( $all_bugs as $bug )
   {
      echo '<tr>';
      echo '<td colspan="4">';
      if ( check_inserted( $bug, $old_version_data[0], $new_version_data[0] ) )
      {
         echo '+ ' . bug_format_id( $bug ) . ' (' . plugin_lang_get( 'changes_inserted' ) . ')';
      }
      if ( check_removed( $bug, $old_version_data[0], $new_version_data[0] ) )
      {
         echo '- ' . bug_format_id( $bug ) . ' (' . plugin_lang_get( 'changes_removed' ) . ')';
      }
      if ( check_edited( $bug, $old_version_data[0], $new_version_data[0] ) )
      {
         echo '# ' . bug_format_id( $bug ) . ' (' . plugin_lang_get( 'changes_edited' ) . ')';
      }
      echo '</td>';
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
   $all_bugs = array();
   foreach ( $old_version_data as $old_bug )
   {
      array_push( $all_bugs, $old_bug );
   }
   foreach ( $new_version_data as $new_bug )
   {
      /**
       * ist ein Issue in beiden Arrays enthalten
       * => wurde es geändert
       * => muss dennoch nicht zwei Mal gelistet werden!
       */
      if ( in_array( $new_bug, $all_bugs ) )
      {
         continue;
      }
      else
      {
         array_push( $all_bugs, $new_bug );
      }
   }
   sort( $all_bugs );

   return $all_bugs;
}

/**
 * @param $bug
 * @param $old_bugs
 * @param $new_bugs
 * @return bool
 */
function check_inserted( $bug, $old_bugs, $new_bugs )
{
   if ( !in_array( $bug, $old_bugs ) && in_array( $bug, $new_bugs ) )
   {
      return true;
   }
   else
   {
      return false;
   }
}

/**
 * @param $bug
 * @param $old_bugs
 * @param $new_bugs
 * @return bool
 */
function check_removed( $bug, $old_bugs, $new_bugs )
{
   if ( in_array( $bug, $old_bugs ) && !in_array( $bug, $new_bugs ) )
   {
      return true;
   }
   else
   {
      return false;
   }
}

/**
 * @param $bug
 * @param $old_bugs
 * @param $new_bugs
 * @return bool
 */
function check_edited( $bug, $old_bugs, $new_bugs )
{
   if ( in_array( $bug, $old_bugs ) && in_array( $bug, $new_bugs ) )
   {
      return true;
   }
   else
   {
      return false;
   }
}

/**
 * @param $old_version
 * @param $act_version
 */
function print_page_top( $old_version, $act_version )
{
   $print_api = new print_api();
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'specmanagement.css">';
   html_page_top1( plugin_lang_get( 'changes_title' ) . ': ' . $old_version->version . ' / ' . $act_version->version );
   html_page_top2();
   if ( plugin_is_installed( 'WhiteboardMenu' ) )
   {
      $print_api->print_whiteboardplugin_menu();
   }
   $print_api->print_plugin_menu();
}

/**
 * Get bug data for specific versions
 *
 * @param $version
 * @return array
 */
function get_version_data( $version )
{
   $database_api = new database_api();
   $version_data = array();

   /* Prjekte sammeln */
   $project_ids = prepare_relevant_projects();
   /* Issues sammeln */
   $reachable_issue_ids = prepare_relevant_issues( $project_ids );
   /* Unpassende Issues aussortieren */
   $relevant_issue_ids = calculate_relevant_issues( $version, $reachable_issue_ids );
   /* Dauer für relevante Issues berechnen */
   $relevant_issues_duration = $database_api->getBugDuration( $relevant_issue_ids );
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
   $print_api = new print_api();
   $status_process = null;
   if ( count( $relevant_issue_ids ) > 0 )
   {
      $relevant_issue_ids = array_merge( $relevant_issue_ids );
      $status_process = $print_api->calculate_status_doc_progress( $relevant_issue_ids );
   }
   return $status_process;
}

/**
 * @param $version
 * @param $reachable_bug_ids
 */
function calculate_relevant_issues( $version, $reachable_bug_ids )
{
   $database_api = new database_api();
   $version_date = $version->date_order;
   $int_filter_string = 'target_version';
   /* Prüfen ob Bug zum gegebenen Zeitpunkt dieser Zielversion zugeordnet war */
   foreach ( $reachable_bug_ids as $reachable_bug_id )
   {
      $target_version = $database_api->calculate_lastChange( $reachable_bug_id, $version_date, $int_filter_string );
      if ( $target_version != $version->version )
      {
         if ( ( $key = array_search( $reachable_bug_id, $reachable_bug_ids ) ) !== false )
         {
            unset( $reachable_bug_ids[$key] );
         }
      }
   }
   return $reachable_bug_ids;
}

/**
 * @param $project_ids
 * @return mixed
 */
function prepare_relevant_issues( $project_ids )
{
   $database_api = new database_api();
   $reachable_bug_ids = array();
   foreach ( $project_ids as $project_id )
   {
      $project_related_bug_ids = $database_api->getBugsByProject( $project_id );
      foreach ( $project_related_bug_ids as $project_related_bug_id )
      {
         array_push( $reachable_bug_ids, $project_related_bug_id );
      }
   }
   return $reachable_bug_ids;
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
 * @param $relevant_bugs_duration
 * @param $status_process
 */
function print_version_progress( $relevant_bugs_duration, $status_process )
{
   echo '<td>';
   echo plugin_lang_get( 'versview_progress' );
   echo '</td>';
   echo '<td>';
   if ( $relevant_bugs_duration > 0 )
   {
      echo $status_process . '%';
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