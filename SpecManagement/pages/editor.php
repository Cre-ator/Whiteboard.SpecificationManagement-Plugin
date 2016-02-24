<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

$specmanagement_database_api = new specmanagement_database_api();
$specmanagement_print_api = new specmanagement_print_api();

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
$parent_project_id = $specmanagement_database_api->getMainProjectByHierarchy( helper_get_current_project() );

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
   $plugin_version_obj = $specmanagement_database_api->getPluginVersionRowByVersionId( $version_id );
   $p_version_id = $plugin_version_obj[0];
   $type_string = $specmanagement_database_api->getTypeString( $specmanagement_database_api->getTypeByVersion( $version_id ) );
   $type_id = $specmanagement_database_api->getTypeId( $type_string );
   $type_row = $specmanagement_database_api->getTypeRow( $type_id );

   $type_options_set = $type_row[2];
   $type_options = explode( ';', $type_options_set );

   $option_show_duration = $type_options[0];
   $option_show_expenses_overview = $type_options[1];

   $work_packages = $specmanagement_database_api->getDocumentSpecWorkPackages( $p_version_id );

   if ( $work_packages != null )
   {
      $chapters = calculate_initial_chapters( $work_packages );
   }

   $versionSpecBugIds = $specmanagement_database_api->getVersionSpecBugs( version_get_field( $version_id, 'version' ) );
   $no_workpackage_bug_ids = array();

   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'specmanagement.css">';
   html_page_top1( plugin_lang_get( 'editor_title' ) . ': ' . $type_string . ' - ' . version_get_field( $version_id, 'version' ) );
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
   }
   print_document_head( $type_string, $version_id, $parent_project_id, $versionSpecBugIds, $print_flag );
   print_editor_table_head( $print_flag );

   $chapter_index = 1;
   if ( $chapters != null )
   {
      foreach ( $chapters as $chapter )
      {
         if ( $chapter == '' )
         {
            continue;
         }

         $duration = $specmanagement_database_api->getWorkpackageDuration( $p_version_id, $chapter );
         print_chapter_title( $chapter_index, $chapter, $option_show_duration, $duration );
         $work_package_bug_ids = $specmanagement_database_api->getWorkPackageSpecBugs( $p_version_id, $chapter );

         $sub_chapter_index = 10;
         foreach ( $work_package_bug_ids as $bug_id )
         {
            if ( bug_exists( $bug_id ) )
            {
               $bug_data = calculate_bug_data( $bug_id, $version_date );
               print_bugs( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
               $sub_chapter_index += 10;
               if ( ( $key = array_search( $bug_id, $versionSpecBugIds ) ) !== false )
               {
                  unset( $versionSpecBugIds[$key] );
               }
            }
         }
         $chapter_index++;

         /** ******************************************************************************************************** */
         $sub_chapters = array();
         $sub_chapters_path = array();
         foreach ( $work_packages as $work_package )
         {
            $order = explode( '/', $work_package );
            if ( $order[0] == $chapter && count( $order ) > 1 )
            {
               $full_chapter_string = implode( '/', $order );
               unset( $order[0] );
               sort( $order );
               $sub_chapter_string = implode( '/', $order );
               array_push( $sub_chapters, $full_chapter_string );
               array_push( $sub_chapters_path, $sub_chapter_string );
            }
         }

         foreach ( $sub_chapters as $sub_chapter )
         {
            $duration = $specmanagement_database_api->getWorkpackageDuration( $p_version_id, $sub_chapter );
            print_chapter_title( $chapter_index, $sub_chapter, $option_show_duration, $duration );
            $work_package_bug_ids = $specmanagement_database_api->getWorkPackageSpecBugs( $p_version_id, $sub_chapter );

            $sub_chapter_index = 10;
            foreach ( $work_package_bug_ids as $bug_id )
            {
               if ( bug_exists( $bug_id ) )
               {
                  $bug_data = calculate_bug_data( $bug_id, $version_date );
                  print_bugs( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
                  $sub_chapter_index += 10;
                  if ( ( $key = array_search( $bug_id, $versionSpecBugIds ) ) !== false )
                  {
                     unset( $versionSpecBugIds[$key] );
                  }
               }
            }
            $chapter_index++;
         }
         /** ******************************************************************************************************** */
      }
   }
   echo '<tr><td colspan="3"><hr width="100%" align="center" /></td></tr>';

   /**
    * If there are bugs left without work packages, print them too, if it is set in the config
    */
   if ( count( $versionSpecBugIds ) > 0 )
   {
      $duration = $specmanagement_database_api->getBugArrayDuration( $versionSpecBugIds );
      print_simple_chapter_title( $chapter_index, $option_show_duration, $duration );

      $sub_chapter_index = 10;
      foreach ( $versionSpecBugIds as $versionSpecBugId )
      {
         if ( bug_exists( $versionSpecBugId ) )
         {
            $bug_data = calculate_bug_data( $versionSpecBugId, $version_date );
            print_bugs( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
            $sub_chapter_index += 10;
            if ( ( $key = array_search( $versionSpecBugId, $versionSpecBugIds ) ) !== false )
            {
               array_push( $no_workpackage_bug_ids, $versionSpecBugId );
               unset( $versionSpecBugIds[$key] );
            }
         }
      }
   }

   echo '</table>';

   if ( $option_show_expenses_overview == '1' )
   {
      print_expenses_overview( $work_packages, $p_version_id, $print_flag, $no_workpackage_bug_ids );
   }

   if ( !$print_flag )
   {
      html_page_bottom1();
   }
}
else
{
   print_successful_redirect( 'plugin.php?page=SpecManagement/choose_document' );
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
   $specmanagement_database_api = new specmanagement_database_api();
   /* Initialize bug data array */
   $bug_data = array();
   /* ID */
   $bug_data[0] = $bug_id;
   /* Summary */
   $bug_data[1] = get_bug_summary( $bug_id, $version_date );
   /* Description */
   $bug_data[2] = get_bug_description( $bug_id, $version_date );
   /* Steps to reproduce */
   $bug_data[3] = get_bug_stepstoreproduce( $bug_id, $version_date );
   /* Additional information */
   $bug_data[4] = get_bug_additionalinformation( $bug_id, $version_date );
   /* Attached files */
   $bug_data[5] = bug_get_attachments( $bug_id );
   /* Notes */
   $bug_data[6] = $specmanagement_database_api->calculateLastBugnotes( $bug_id, $version_date );
   /* planned duration for each bug */
   $bug_data[7] = $specmanagement_database_api->getPtimeRow( $bug_id )[2];

   return $bug_data;
}

/**
 * @param $bug_id
 * @param $version_date
 * @return null
 */
function get_bug_additionalinformation( $bug_id, $version_date )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $additional_information_value = null;
   $value_type = 3;
   $additional_information_value = $specmanagement_database_api->calculateLastTextfields( $bug_id, $version_date, $value_type );
   return $additional_information_value;
}

/**
 * @param $bug_id
 * @param $version_date
 * @return array
 */
function get_bug_stepstoreproduce( $bug_id, $version_date )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $steps_to_reproduce_value = null;
   $value_type = 2;
   $steps_to_reproduce_value = $specmanagement_database_api->calculateLastTextfields( $bug_id, $version_date, $value_type );
   return $steps_to_reproduce_value;
}

/**
 * @param $bug_id
 * @param $version_date
 * @return array
 */
function get_bug_description( $bug_id, $version_date )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $description_value = null;
   $bug = bug_get( $bug_id );
   $value_type = 1;
   $description_value = $specmanagement_database_api->calculateLastTextfields( $bug_id, $version_date, $value_type );
   if ( strlen( $description_value ) == 0 )
   {
      $description_value = $bug->description;
   }
   return $description_value;
}

/**
 * @param $bug_id
 * @param $version_date
 * @return string
 */
function get_bug_summary( $bug_id, $version_date )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $int_filter_string = 'summary';
   $summary_value = $specmanagement_database_api->calculate_lastChange( $bug_id, $version_date, $int_filter_string );
   if ( strlen( $summary_value ) == 0 )
   {
      $summary_value = bug_get_field( $bug_id, 'summary' );
      return $summary_value;
   }
   return $summary_value;
}

/**
 * Gets the managers of the current selected project
 *
 * @param $version_id
 * @return string
 */
function calculate_person_in_charge( $version_id )
{
   $person_in_charge = '';
   $project_id = helper_get_current_project();
   if ( $project_id == 0 )
   {
      $project_id = version_get_field( $version_id, 'project_id' );
   }
   $project_related_users = project_get_local_user_rows( $project_id );
   $count = 0;
   foreach ( $project_related_users as $project_related_user )
   {
      if ( $project_related_user['project_id'] == $project_id
         && $project_related_user['access_level'] == 70
         && user_is_enabled( $project_related_user['user_id'] )
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
   $specmanagement_database_api = new specmanagement_database_api();
   $sum_pt = array();
   $sum_pt_all = 0;
   $sum_pt_bug = 0;
   foreach ( $allRelevantBugs as $bug_id )
   {
      $ptime_row = $specmanagement_database_api->getPtimeRow( $bug_id );
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
      echo '<td />';
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
   echo '<td />';
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
   $specmanagement_print_api = new specmanagement_print_api();
   $attachment_count = file_bug_attachment_count( $bug_id );
   echo '<tr>';
   echo '<td />';
   echo '<td class="infohead" colspan="2">' . plugin_lang_get( 'editor_bug_attachments' ) . ' (' . $attachment_count . ')</td>';
   echo '</tr>';

   echo '<tr id="attachments">';
   echo '<td />';
   echo '<td class="bug-attachments" colspan="2">';
   $specmanagement_print_api->print_bug_attachments_list( $bug_id );
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
   print_bug_head( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag );
   print_bug_infos( string_display_links( trim( $bug_data[2] ) ) );
   print_bug_infos( string_display_links( trim( $bug_data[3] ) ) );
   print_bug_infos( string_display_links( trim( $bug_data[4] ) ) );
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
 * @param $chapter_index
 * @param $sub_chapter_index
 * @param $bug_data
 * @param $option_show_duration
 * @param $print_flag
 */
function print_bug_head( $chapter_index, $sub_chapter_index, $bug_data, $option_show_duration, $print_flag )
{
   echo '<tr>';
   echo '<td class="form-title">' . $chapter_index . '.' . $sub_chapter_index . '</td>';
   echo '<td class="form-title">' . string_display( $bug_data[1] ) . ' (';
   if ( !$print_flag )
   {
      print_bug_link( $bug_data[0], true );
   }
   else
   {
      echo bug_format_id( $bug_data[0] );
   }
   echo ')';
   echo '</td>';
   echo '<td class="duration_title">';
   if ( $option_show_duration == '1' && !( $bug_data[7] == 0 || is_null( $bug_data[7] ) ) )
   {
      echo plugin_lang_get( 'editor_bug_duration' ) . ': ' . $bug_data[7] . ' ' . plugin_lang_get( 'editor_duration_unit' );
   }
   echo '</td>';
   echo '</tr>';
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
   $project_id = helper_get_current_project();
   $versions = version_get_all_rows( $project_id );
   $act_version = version_get( $version_id );
   $head_project_id = $project_id;
   if ( $parent_project_id == 0 )
   {
      $parent_project_id = version_get_field( $version_id, 'project_id' );
      $head_project_id = version_get_field( $version_id, 'project_id' );
   }
   print_editor_table_head( $print_flag );
   print_editor_table_title( $type_string, $version_id, $print_flag );
   print_doc_head_row( 'head_version', version_get_field( $version_id, 'version' ) );
   print_doc_head_row( 'head_customer', project_get_name( $parent_project_id ) );
   print_doc_head_row( 'head_project', project_get_name( $head_project_id ) );
   print_doc_head_row( 'head_date', date( 'd\.m\.Y' ) );
   print_doc_head_row( 'head_person_in_charge', calculate_person_in_charge( $version_id ) );
   if ( !is_null( $allRelevantBugs ) )
   {
      print_doc_head_row( 'head_process', get_process_string( $allRelevantBugs ) );
   }
   if ( !$print_flag )
   {
      print_doc_head_versions( $versions, $act_version );
   }
   echo '</table>';
   echo '<br />';
}

/**
 * @param $type_string
 * @param $version_id
 * @param $print_flag
 */
function print_editor_table_title( $type_string, $version_id, $print_flag )
{
   echo '<tr>';
   echo '<td class="field-container">' . plugin_lang_get( 'head_title' ) . '</td>';
   echo '<td class="form-title" colspan="2">' . $type_string . ' - ' . version_get_field( $version_id, 'version' ) . '</td>';
   if ( !$print_flag )
   {
      echo '<td class="form-title">';
      echo '<form action="' . plugin_page( 'editor' ) . '" method="post">';
      echo '<span class="input">';
      echo '<input type="hidden" name="version_id" value="' . $version_id . '" />';
      echo '<input type="submit" name="print" class="button" value="' . lang_get( 'print' ) . '"/>';
      echo '</span>';
      echo '</form>';
      echo '</td>';
   }
   echo '</tr>';
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
   echo '<tr>';
   echo '<td class="form-title">' . $chapter_index . '</td>';
   echo '<td class="form-title">' . plugin_lang_get( 'editor_no_workpackage' );
   echo '</td>';
   echo '<td class="duration_title">';
   if ( $option_show_duration == '1' && !( $duration == 0 || is_null( $duration ) ) )
   {
      echo '[' . plugin_lang_get( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ']';
   }
   echo '</td>';
   echo '</tr>';
   echo '<tr><td colspan="3"><hr width="100%" align="center" /></td></tr>';
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
   echo '<tr>';
   echo '<td class="form-title">' . $chapter_index . '</td>';
   echo '<td class="form-title">' . $work_package;
   echo '</td>';
   echo '<td class="duration_title">';
   if ( $option_show_duration == '1' && !( $duration == 0 || is_null( $duration ) ) )
   {
      echo '[' . plugin_lang_get( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ']';
   }
   echo '</td>';
   echo '</tr>';
   echo '<tr><td colspan="3"><hr width="100%" align="center" /></td></tr>';
}

/**
 * Prints the expenses overview area
 *
 * @param $work_packages
 * @param $p_version_id
 * @param $print_flag
 * @param $no_workpackage_bug_ids
 */
function print_expenses_overview( $work_packages, $p_version_id, $print_flag, $no_workpackage_bug_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $document_duration = 0;

   echo '<br />';
   print_editor_table_head( $print_flag );
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
         $duration = $specmanagement_database_api->getWorkpackageDuration( $p_version_id, $work_package );
         $document_duration += $duration;
         echo '<tr>';
         echo '<td>' . $work_package . '</td>';
         echo '<td class="duration">' . $duration . '</td>';
         echo '</tr>';
      }
   }
   if ( count( $no_workpackage_bug_ids ) > 0 )
   {
      $sum_no_work_package_bug_duration = 0;

      foreach ( $no_workpackage_bug_ids as $no_workpackage_bug_id )
      {
         $no_work_package_bug_duration = $specmanagement_database_api->getBugDuration( $no_workpackage_bug_id );
         if ( !is_null( $no_work_package_bug_duration ) )
         {
            $sum_no_work_package_bug_duration += $no_work_package_bug_duration;
         }
      }

      $document_duration += $sum_no_work_package_bug_duration;
      echo '<tr>';
      echo '<td>' . plugin_lang_get( 'editor_no_workpackage' ) . '</td>';
      echo '<td class="duration">' . $sum_no_work_package_bug_duration . '</td>';
      echo '</tr>';
   }
   echo '<tr>';
   echo '<td colspan="2"><hr width="100%" align="center" /></td>';
   echo '</tr>';
   echo '<tr>';
   echo '<td>';
   echo plugin_lang_get( 'editor_expenses_overview_sum' ) . ':';
   echo '</td>';
   echo '<td class="duration">' . $document_duration . '</td>';
   echo '</tr>';
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
   echo '<th>' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</th>';
   echo '<th class="duration">' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')</th>';
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
 * @param $print_flag
 */
function print_editor_table_head( $print_flag )
{
   if ( !$print_flag )
   {
      echo '<table class="editor">';
   }
   else
   {
      echo '<table class="editorprint">';
   }
}

/**
 * Prints all available versions into the document head
 *
 * @param $versions
 * @param $act_version
 */
function print_doc_head_versions( $versions, $act_version )
{
   $specmanagement_database_api = new specmanagement_database_api();
   foreach ( $versions as $version )
   {
      $type_string = $specmanagement_database_api->getTypeString( $specmanagement_database_api->getTypeByVersion( $version['id'] ) );
      if ( strlen( $type_string ) > 0 )
      {
         $same_version = $act_version->id == $version['id'];
         echo '<tr>';
         print_doc_head_version_col( $same_version, date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) ) );
         print_doc_head_version_col( $same_version, version_full_name( $version['id'] ) );
         $change_button_string = '<form method="post" name="form_set_source" action="' . plugin_page( 'changes' ) . '">'
            . '<input type="hidden" name="version_other" value="' . $version['id'] . '" />'
            . '<input type="hidden" name="version_my" value="' . $act_version->id . '" />'
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
 * @param $allRelevantBugs
 * @return string
 */
function get_process_string( $allRelevantBugs )
{
   $specmanagement_print_api = new specmanagement_print_api();
   $process_string = '';
   $status_flag = check_status_flag( $allRelevantBugs );
   if ( $status_flag )
   {
      $status_process = 0;
      if ( !empty( $allRelevantBugs ) )
      {
         $status_process = $specmanagement_print_api->calculate_status_doc_progress( $allRelevantBugs );
      }
      $process_string .= '<div class="progress400">';
      $process_string .= '<span class="bar" style="width: ' . $status_process . '%;">' . round( $status_process, 2 ) . '%</span>';
      $process_string .= '</div>';
      return $process_string;
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
      return $process_string;
   }
}

/**
 * @param $allRelevantBugs
 * @return bool
 */
function check_status_flag( $allRelevantBugs )
{
   $specmanagement_database_api = new specmanagement_database_api();
   $status_flag = false;
   foreach ( $allRelevantBugs as $bug_id )
   {
      $ptime_row = $specmanagement_database_api->getPtimeRow( $bug_id );
      if ( is_null( $ptime_row[2] ) || 0 == $ptime_row[2] )
      {
         $status_flag = true;
         break;
      }
   }
   return $status_flag;
}

/**
 * calculate main chapters
 * @param $work_packages
 * @return array
 */
function calculate_initial_chapters( $work_packages )
{
   $initial_chapters = array();
   foreach ( $work_packages as $work_package )
   {
      $order = explode( '/', $work_package );
      if ( array_search( $order[0], $initial_chapters ) === false )
      {
         array_push( $initial_chapters, $order[0] );
      }
   }
   return $initial_chapters;
}