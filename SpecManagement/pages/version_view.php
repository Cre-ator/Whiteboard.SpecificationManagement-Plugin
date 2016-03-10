<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

define( 'COLS', 7 );

$obsolete_flag = false;
if ( isset( $_POST['obsolete_flag'] ) )
{
   $obsolete_flag = true;
}
if ( isset( $_POST['non_obsolete_flag'] ) )
{
   $obsolete_flag = false;
}

$show_zero_issues = false;
if ( isset( $_POST['show_zero_issues'] ) )
{
   $show_zero_issues = true;
}
if ( isset( $_POST['non_show_zero_issues'] ) )
{
   $show_zero_issues = false;
}

$print_flag = false;
if ( isset( $_POST['print_flag'] ) )
{
   $print_flag = true;
}

/**
 * Page content
 */
calculate_page_content( $print_flag, $obsolete_flag, $show_zero_issues );

/**
 * @param $print_flag
 * @param $obsolete_flag
 * @param $show_zero_issues
 */
function calculate_page_content( $print_flag, $obsolete_flag, $show_zero_issues )
{
   $specmanagement_print_api = new specmanagement_print_api();

   html_page_top1( plugin_lang_get( 'menu_versview' ) );
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'specmanagement.css">';
   if ( !$print_flag )
   {
      html_page_top2();
      if ( plugin_is_installed( 'WhiteboardMenu' ) )
      {
         require_once WHITEBOARDMENU_CORE_URI . 'whiteboard_print_api.php';
         $whiteboard_print_api = new whiteboard_print_api();
         $whiteboard_print_api->printWhiteboardMenu();
      }
      $specmanagement_print_api->print_plugin_menu();
      echo '<div align="center">';
      echo '<hr size="1" width="100%" />';
   }

   print_table( $obsolete_flag, $show_zero_issues, $print_flag );

   if ( !$print_flag )
   {
      html_page_bottom1();
   }
}

/**
 * @param $obsolete_flag
 * @param $show_zero_issues
 * @param $print_flag
 */
function print_table( $obsolete_flag, $show_zero_issues, $print_flag )
{
   $specmanagement_print_api = new specmanagement_print_api();
   $obsolote = false;
   if ( $obsolete_flag )
   {
      $obsolote = null;
   }
   $versions = version_get_all_rows_with_subs( helper_get_current_project(), null, $obsolote );
   $amount_stat_columns = plugin_config_get( 'CAmount' );
   if ( $amount_stat_columns > PLUGINS_SPECMANAGEMENT_MAX_COLUMNS )
   {
      $amount_stat_columns = PLUGINS_SPECMANAGEMENT_MAX_COLUMNS;
   }

   $specmanagement_print_api->printTableTop( '90' );
   print_tablehead( $amount_stat_columns, $obsolete_flag, $show_zero_issues, $print_flag );
   print_tablebody( $amount_stat_columns, $print_flag, $show_zero_issues, $versions );
   $specmanagement_print_api->printTableFoot();
}

/**
 * @param $amount_stat_columns
 * @param $print_flag
 * @param $show_zero_issues
 * @param $versions
 */
function print_tablebody( $amount_stat_columns, $print_flag, $show_zero_issues, $versions )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $specmanagement_print_api = new specmanagement_print_api();

   echo '<tbody>';
   for ( $version_index = 0; $version_index < count( $versions ); $version_index++ )
   {
      $version = $versions[$version_index];
      $version_spec_bug_ids = $specmanagement_database_api->get_version_spec_bugs( $version['version'] );
      if ( is_null( $version_spec_bug_ids ) && !$show_zero_issues )
      {
         continue;
      }
      $version_deadline = date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) );
      $timeleft = time() - $version['date_order'];

      $unsolved_bug_duration = null;
      $add_rel_duration = 0;
      $status_process = null;
      $uncertainty_bug_ids = array();
      $add_rel_uncertainty_bug_ids = array();
      $sum_duration = 0;
      $uncertainty_status_process = null;
      $unsolved_bug_finished_date = null;
      $null_issues_flag = true;

      if ( !is_null( $version_spec_bug_ids ) )
      {
         $unsolveld_bug_ids = get_unsolved_issues( $version_spec_bug_ids );
         $unsolved_bug_duration = $specmanagement_database_api->get_bug_array_duration( $unsolveld_bug_ids );
         $rel_based_data = calculate_rel_based_data( $unsolveld_bug_ids );
         $add_rel_duration = $rel_based_data[0];
         $add_rel_uncertainty_bug_ids = $rel_based_data[1];
         $sum_duration = $unsolved_bug_duration + $add_rel_duration;
         $status_process = 100 * round( 1 - ( count( $unsolveld_bug_ids ) ) / ( count( $version_spec_bug_ids ) ), 2 );
         $uncertainty_bug_ids = get_uncertainty_issues( $unsolveld_bug_ids );
         $uncertainty_status_process = 100 * round( ( count( $uncertainty_bug_ids ) ) / ( count( $version_spec_bug_ids ) ), 2 );
         $null_issues_flag = false;
      }

      $time_delay = calc_time_delay( $timeleft, $sum_duration );
      $specmanagement_print_api->printRow();
      print_version( $version );
      print_date( $version_deadline, $time_delay[0] );
      print_issue_amount( $amount_stat_columns, $print_flag, $version, $version_spec_bug_ids, $null_issues_flag );
      print_process( $status_process, $null_issues_flag );
      print_duration( $sum_duration, $add_rel_duration, $null_issues_flag );
      print_uncertainty( $uncertainty_bug_ids, $add_rel_uncertainty_bug_ids, $uncertainty_status_process, $null_issues_flag );
      print_information( $version, $null_issues_flag, $time_delay[1] );
      echo '</tr>';
   }
   echo '</tbody>';
}

/**
 * @param $unsolveld_bug_ids
 * @return mixed
 */
function calculate_rel_based_data( $unsolveld_bug_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $rel_based_data = array();
   $add_rel_duration = 0;
   $add_rel_uncertainty_bug_ids = array();

   foreach ( $unsolveld_bug_ids as $unsolveld_bug_id )
   {
      $bug_src_rels = relationship_get_all_src( $unsolveld_bug_id );
      foreach ( $bug_src_rels as $bug_src_rel )
      {
         if ( $bug_src_rel->src_bug_id == $unsolveld_bug_id )
         {
            $blocking_bug_id = $bug_src_rel->dest_bug_id;
            $blocking_bug_status = bug_get_field( $blocking_bug_id, 'status' );
            $blocking_bug_duration = $specmanagement_database_api->get_bug_duration( $blocking_bug_id );
            if ( ( $blocking_bug_duration > 0 || !is_null( $blocking_bug_duration ) )
               && !( $blocking_bug_status == 80 || $blocking_bug_status == 90 )
            )
            {
               array_push( $add_rel_uncertainty_bug_ids, $blocking_bug_id );
               $add_rel_duration += $blocking_bug_duration;
            }
         }
      }
   }
   $rel_based_data[0] = $add_rel_duration;
   $rel_based_data[1] = $add_rel_uncertainty_bug_ids;

   return $rel_based_data;
}

/**
 * @param $unsolveld_bug_ids
 * @return array
 */
function get_uncertainty_issues( $unsolveld_bug_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $uncertainty_bug_ids = array();
   foreach ( $unsolveld_bug_ids as $unsolveld_bug_id )
   {
      if ( $specmanagement_database_api->get_bug_duration( $unsolveld_bug_id ) == 0 )
      {
         array_push( $uncertainty_bug_ids, $unsolveld_bug_id );
      }
   }
   return $uncertainty_bug_ids;
}

/**
 * @param $version_spec_bug_ids
 * @return mixed
 */
function get_unsolved_issues( $version_spec_bug_ids )
{
   $unsolveld_bug_ids = array();
   foreach ( $version_spec_bug_ids as $version_spec_bug_id )
   {
      if ( !( ( bug_get_field( $version_spec_bug_id, 'status' ) == 80 || bug_get_field( $version_spec_bug_id, 'status' ) == 90 ) ) )
      {
         array_push( $unsolveld_bug_ids, $version_spec_bug_id );
      }
   }
   return $unsolveld_bug_ids;
}

/**
 * @param $amount_stat_columns
 * @param $print_flag
 * @param $version
 * @param $version_spec_bugs
 * @param $null_issues_flag
 */
function print_issue_amount( $amount_stat_columns, $print_flag, $version, $version_spec_bugs, $null_issues_flag )
{
   if ( plugin_config_get( 'ShowSpecStatCols' ) == ON )
   {
      for ( $column_index = 1; $column_index <= $amount_stat_columns; $column_index++ )
      {
         $column_spec_status = plugin_config_get( 'CStatSelect' . $column_index );
         $column_spec_bug_count = 0;
         if ( !$null_issues_flag )
         {
            foreach ( $version_spec_bugs as $version_spec_bug )
            {
               if ( bug_get_field( $version_spec_bug, 'status' ) == $column_spec_status )
               {
                  $column_spec_bug_count++;
               }
            }
            echo '<td class="status" bgcolor="' . get_status_color( $column_spec_status ) . '">';
            print_amount( $print_flag, $column_spec_bug_count, $version );
         }
         else
         {
            echo '<td class="status" bgcolor="' . get_status_color( $column_spec_status ) . '">0';
         }
         echo '</td>';
      }
   }
   $version_spec_bug_count = count( $version_spec_bugs );
   echo '<td class="status">';
   print_amount( $print_flag, $version_spec_bug_count, $version );
   echo '</td>';
}

/**
 * @param $obsolete_flag
 * @param $show_zero_issues
 * @param $print_flag
 * @param $amount_stat_columns
 */
function print_thead_headrow( $obsolete_flag, $show_zero_issues, $print_flag, $amount_stat_columns )
{
   echo '<tr>';
   if ( !$print_flag )
   {
      echo '<td class="form-title" colspan="' . ( COLS + $amount_stat_columns - 5 ) . '">' . plugin_lang_get( 'menu_versview' ) . '</td>';
      echo '<td colspan="4"><form action="' . plugin_page( 'version_view' ) . '" method="post">';
      print_thead_headrow_obsoletebutton( $obsolete_flag );
      print_thead_headrow_showzeroissuebutton( $show_zero_issues );
      echo '&nbsp<input type="submit" name="print_flag" class="button" value="' . lang_get( 'print' ) . '"/>';
      echo '</form></td>';
   }
   else
   {
      echo '<td class="center" colspan="' . COLS . '">' . plugin_lang_get( 'versview_thead' ) . '</td>';
   }
   echo '</tr>';
}

/**
 * @param $show_zero_issues
 */
function print_thead_headrow_showzeroissuebutton( $show_zero_issues )
{
   if ( $show_zero_issues === false )
   {
      echo '&nbsp<input type="submit" name="show_zero_issues" class="button" value="' . plugin_lang_get( 'versview_show_zero_issues' ) . '"/>';
   }
   else
   {
      echo '&nbsp<input type="submit" name="non_show_zero_issues" class="button" value="' . plugin_lang_get( 'versview_non_show_zero_issues' ) . '"/>';
   }
}

/**
 * @param $obsolete_flag
 */
function print_thead_headrow_obsoletebutton( $obsolete_flag )
{
   if ( $obsolete_flag === false )
   {
      echo '<input type="submit" name="obsolete_flag" class="button" value="' . plugin_lang_get( 'versview_obsolete_flag' ) . '"/>';
   }
   else
   {
      echo '<input type="submit" name="non_obsolete_flag" class="button" value="' . plugin_lang_get( 'versview_non_obsolete_flag' ) . '"/>';
   }
}

/**
 * @param $print_flag
 * @param $version_spec_bug_count
 * @param $version
 */
function print_amount( $print_flag, $version_spec_bug_count, $version )
{
   if ( $version_spec_bug_count > 0 && !$version['obsolete'] && !$print_flag )
   {
      echo '<a href="search.php?project_id=' . helper_get_current_project() . '&target_version=' . $version['version'] .
         '&sortby=last_updated&dir=DESC&hide_status_id=-2&match_type=0">';
   }
   echo string_display( $version_spec_bug_count );
   if ( $version_spec_bug_count > 0 && !$version['obsolete'] && !$print_flag )
   {
      echo '</a>';
   }
}

/**
 * @param $amount_stat_columns
 * @param $obsolete_flag
 * @param $show_zero_issues
 * @param $print_flag
 */
function print_tablehead( $amount_stat_columns, $obsolete_flag, $show_zero_issues, $print_flag )
{
   echo '<thead>';
   print_thead_headrow( $obsolete_flag, $show_zero_issues, $print_flag, $amount_stat_columns );
   echo '<tr class="row-category2">';
   echo '<th class="form-title, version" colspan="1">' . lang_get( 'version' ) . '</th>';
   print_thead_col( 'versview_scheduled' );
   if ( plugin_config_get( 'ShowSpecStatCols' ) == ON )
   {
      echo '<th class="form-title, version" colspan="' . ( $amount_stat_columns ) . '">' . plugin_lang_get( 'versview_amount' ) . '</th>';
      echo '<th class="form-title, version" colspan="1">&#931</th>';
   }
   else
   {
      print_thead_col( 'versview_amount' );
   }
   print_thead_col( 'versview_progress' );
   print_thead_col( 'versview_duration' );
   print_thead_col( 'versview_uncertainty' );
   print_thead_col( 'versview_information' );
   echo '</tr>';
   echo '</thead>';
}

/**
 * @param $lang_string
 */
function print_thead_col( $lang_string )
{
   echo '<th class="form-title, version" colspan="1">' . plugin_lang_get( $lang_string ) . '</th>';
}

/**
 * @param $version
 * @param $null_issues_flag
 * @param $deadline_flag
 */
function print_information( $version, $null_issues_flag, $deadline_flag )
{
   echo '<td style="version">';
   if ( $deadline_flag )
   {
      echo plugin_lang_get( 'versview_deadline_n' ) . '<br/>';
   }
   if ( $version['obsolete'] )
   {
      echo plugin_lang_get( 'versview_obsolete' ) . '<br/>';
   }
   if ( $null_issues_flag )
   {
      echo plugin_lang_get( 'versview_zero_issues' ) . '<br/>';
   }
   echo '</td>';
}

/**
 * @param $status_process
 * @param $null_issues_flag
 */
function print_process( $status_process, $null_issues_flag )
{
   echo '<td style="version">';
   if ( !$null_issues_flag )
   {
      echo $status_process . '%';
   }
   echo '</td>';
}

/**
 * @param $uncertainty_bug_ids
 * @param $add_rel_uncertainty_bug_ids
 * @param $uncertainty_status_process
 * @param $null_issues_flag
 */
function print_uncertainty( $uncertainty_bug_ids, $add_rel_uncertainty_bug_ids, $uncertainty_status_process, $null_issues_flag )
{
   $add_rel_uncertainty_bug_count = count( $add_rel_uncertainty_bug_ids );
   echo '<td style="version">';
   if ( !$null_issues_flag )
   {
      echo plugin_lang_get( 'versview_uncertainty_string1' ) . ' '
         . count( $uncertainty_bug_ids ) . '  '
         . plugin_lang_get( 'versview_uncertainty_string2' )
         . ' (' . $uncertainty_status_process
         . plugin_lang_get( 'versview_uncertainty_string3' ) . ').<br/>';
      if ( $add_rel_uncertainty_bug_count > 0 )
      {
         echo plugin_lang_get( 'versview_uncertainty_string4' ) . ' '
            . string_display( $add_rel_uncertainty_bug_count ) . ' '
            . plugin_lang_get( 'versview_uncertainty_string2' ) . ' '
            . plugin_lang_get( 'versview_duration1' );
      }
   }
   echo '</td>';
}

/**
 * @param $sum_duration
 * @param $add_rel_duration
 * @param $null_issues_flag
 */
function print_duration( $sum_duration, $add_rel_duration, $null_issues_flag )
{
   echo '<td style="version">';
   if ( !$null_issues_flag )
   {
      echo string_display( $sum_duration );
      if ( $add_rel_duration > 0 )
      {
         echo ' (' . plugin_lang_get( 'versview_duration0' ) . ' ' . string_display( $add_rel_duration ) . ' '
            . plugin_lang_get( 'versview_duration1' ) . ')';
      }
   }
   echo '</td>';
}

/**
 * @param $timeleft
 * @param $sum_duration
 * @return bool
 */
function calc_time_delay( $timeleft, $sum_duration )
{
   $time_delay = array();
   $deadline_flag = false;
   $sum_duration = $sum_duration * 86400;
   $time_difference = ( $timeleft + $sum_duration );
   if ( $time_difference >= 0 )
   {
      $deadline_flag = true;
   }

   $time_delay[0] = $time_difference;
   $time_delay[1] = $deadline_flag;

   return $time_delay;
}

/**
 * @param $version_deadline
 * @param $time_difference
 * @return bool
 */
function print_date( $version_deadline, $time_difference )
{
   $minutes = $time_difference / 60;
   $hours = $minutes / 60;
   $days = floor( $hours / 24 );
   $hours_left = round( $hours - ( $days * 24 ), 0 );

   echo '<td style="version">';
   echo $version_deadline . ' [';
   if ( $time_difference >= 0 )
   {
      echo '+ ';
   }
   else
   {
      echo '- ';
   }
   echo $days . 'd ' . $hours_left . 'h]';
   echo '</td>';
}

/**
 * @param $version
 */
function print_version( $version )
{
   echo '<td style="version">';
   echo string_display( version_full_name( $version['id'] ) );
   echo '</td>';
}