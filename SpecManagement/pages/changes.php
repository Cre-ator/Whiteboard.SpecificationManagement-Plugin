<?php
require_once SPECMANAGEMENT_CORE_URI . 'database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'print_api.php';


/* TODO:
 * - Alle Bugs beider Versionen sammeln
 * - Prüfen, ob Bugs in beiden Versionen enthalten -> Meldung, wenn Bug neu bzw. weg
 * - Für jeden Bug, der in beiden Versionen enthalten ist prüfen, ob irgendwo eine
 *   Änderung vorgenommen wurde -> Meldung über Änderung mit entspr. Details
 */
if ( isset( $_POST['version_old'] ) && isset( $_POST['version_act'] ) )
{
   if ( $_POST['version_old'] != $_POST['version_act'] )
   {
      calculate_changes();
   }
}

/**
 */
function calculate_changes()
{
   $print_api = new print_api();
   /* old bug */
   $old_version = version_get( $_POST['version_old'] );
   $old_version_data = get_version_data( $old_version );
   /* act bug */
   $act_version = version_get( $_POST['version_act'] );
   $act_version_data = get_version_data( $act_version );

   print_page_top( $old_version, $act_version );
   $print_api->printTableTop( '60' );
   print_changes_table_head( $old_version, $act_version, $old_version_data, $act_version_data );

   echo '<tbody>';
   echo '<tr>';

   /* old bugs */
   print_bug_table( $old_version_data );
   /* end old bugs */

   /* act bugs */
   print_bug_table( $act_version_data );
   /* end act bugs */

   echo '</tr>';
   echo '</tbody>';

   $print_api->printTableFoot();
   html_page_bottom1();
}

/**
 * @param $version_data
 */
function print_bug_table( $version_data )
{
   echo '<td colspan="2">';
   echo '<table>';
   foreach ( $version_data[0] as $bug )
   {
      echo '<tr>';
      echo '<td>';
      echo bug_format_id( $bug );
      echo '</td>';
      echo '</tr>';
   }
   echo '</table>';
   echo '</td>';
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
   $print_api = new print_api();
   $version_data = array();

   $relevant_bugs = $database_api->getVersionSpecBugs( $version->version );
   $relevant_bugs_duration = $database_api->getBugDuration( $relevant_bugs );
   $status_process = null;
   if ( count( $relevant_bugs ) > 0 )
   {
      $status_process = $print_api->calculate_status_doc_progress( $relevant_bugs );
   }

   $version_data[0] = $relevant_bugs;
   $version_data[1] = $relevant_bugs_duration;
   $version_data[2] = $status_process;

   return $version_data;
}

/**
 * @param $old_version
 * @param $act_version
 * @param $old_version_data
 * @param $act_version_data
 */
function print_changes_table_head( $old_version, $act_version, $old_version_data, $act_version_data )
{
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