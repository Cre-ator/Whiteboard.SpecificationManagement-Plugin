<?php

class print_api
{
   /**
    * Get suffix of mantis version
    *
    * @return string
    */
   public function getMantisVersion()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   /**
    * Prints a header row in a table
    *
    * @param $colspan
    * @param $lang_string
    */
   public function printFormTitle( $colspan, $lang_string )
   {
      echo '<tr>';
      echo '<td class="form-title" colspan="' . $colspan . '">';
      echo plugin_lang_get( $lang_string );
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Starts a new row in a table
    */
   public function printRow()
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         echo '<tr ' . helper_alternate_class() . '>';
      }
      else
      {
         echo '<tr>';
      }
   }

   /**
    * Creates a new category-column
    *
    * @param $colspan
    * @param $rowspan
    * @param $lang_string
    */
   public function printCategoryField( $colspan, $rowspan, $lang_string )
   {
      echo '<td class="category" colspan="' . $colspan . '" rowspan="' . $rowspan . '">';
      echo plugin_lang_get( $lang_string );
      echo '</td>';
   }

   /**
    * Prints a radio button in a table
    *
    * @param $colspan
    * @param $name
    */
   public function printRadioButton( $colspan, $name )
   {
      echo '<td width="100px" colspan="' . $colspan . '">';
      echo '<label>';
      echo '<input type="radio" name="' . $name . '" value="1"';
      echo ( ON == plugin_config_get( $name ) ) ? 'checked="checked"' : '';
      echo '/>' . lang_get( 'yes' );
      echo '</label>';
      echo '<label>';
      echo '<input type="radio" name="' . $name . '" value="0"';
      echo ( OFF == plugin_config_get( $name ) ) ? 'checked="checked"' : '';
      echo '/>' . lang_get( 'no' );
      echo '</label>';
      echo '</td>';
   }

   /**
    * Prints a space-element in a table
    *
    * @param $colspan
    */
   public function printSpacer( $colspan )
   {
      echo '<tr>';
      echo '<td class="spacer" colspan="' . $colspan . '">&nbsp;</td>';
      echo '</tr>';
   }

   /**
    * Prints the whiteboardmenu plugin specific menu
    */
   public function print_whiteboardplugin_menu()
   {
      echo '<table align="center">';
      echo '<tr">';

      if ( plugin_is_installed( 'UserProjectView' )
         && file_exists( config_get_global( 'plugin_path' ) . 'UserProjectView' )
      )
      {
         echo '<td>';
         echo '| ';
         echo '<a href="' . plugin_page( 'UserProject', false, 'UserProjectView' ) . '&sortVal=userName&sort=ASC">' . plugin_lang_get( 'menu_userprojecttitle', 'UserProjectView' ) . '</a>';
         echo '</td>';
      }

      if ( plugin_is_installed( 'SpecManagement' )
         && file_exists( config_get_global( 'plugin_path' ) . 'SpecManagement' )
      )
      {
         echo '<td>';
         echo '| ';
         echo '<a href="' . plugin_page( 'choose_document', false, 'SpecManagement' ) . '">' . plugin_lang_get( 'menu_title', 'SpecManagement' ) . '</a>';
         echo '</td>';
      }

      echo '<td>';
      echo ' |';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   /** todo *easier to find the method
    * Prints the plugin specific menu
    */
   public function print_plugin_menu()
   {
      echo '<table align="center">';
      echo '<tr><td colspan="4" class="center" ><font color="#8b0000" size="5">*** Plugin befindet sich in Entwicklungsphase ***</font></td></tr>';

      echo '<tr>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'choose_document' ) . '">';
      echo plugin_lang_get( 'menu_choosedoc' );
      echo '</a> ]';
      echo '</td>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'manage_versions' ) . '">';
      echo plugin_lang_get( 'menu_manversions' );
      echo '</a> ]';
      echo '</td>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'manage_types' ) . '">';
      echo plugin_lang_get( 'menu_mantypes' );
      echo '</a> ]';
      echo '</td>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'version_view' ) . '">';
      echo plugin_lang_get( 'menu_versview' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   /**
    * Prints the editor specific menu
    */
   public function print_editor_menu()
   {
      echo '<table align="center">';
      echo '<tr">';
      /* General */

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'document_print' ) . '">';
      echo plugin_lang_get( 'menu_printbutton' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   /**
    * Prints the specific plugin fields in the bug-update user interface
    *
    * @param $type
    * @param $work_package
    * @param $ptime
    */
   public function printBugUpdateFields( $type, $work_package, $ptime )
   {
      $database_api = new database_api();
      $work_packages = $database_api->getProjectSpecWorkPackages();

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_req' ), '</td>';
      echo '<td colspan="5" id="requirement">', $type, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</td>';
      echo '<td colspan="5">';
      echo '<input type="text" value="' . $work_package . '" id="work_package" name="work_package" list="work_packages"/>';
      echo '<button type="button" onClick="document.getElementById(\'work_package\').value=\'\';">X</button>';
      if ( !is_null( $work_packages ) )
      {
         echo '<datalist id="work_packages">';
         foreach ( $work_packages as $existing_work_package )
         {
            echo '<option value="' . $existing_work_package . '">';
         }
         echo '</datalist>';
      }
      echo '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category"><label for="ptime">' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')' . '</label></td>';
      echo '<td colspan="5">';
      echo '<input ', helper_get_tab_index(), ' type="text" id="ptime" name="ptime" size="50" maxlength="50" value="', $ptime, '" />';
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Prints the specific plugin fields in the bug-view user interface
    *
    * @param $type
    * @param $work_package
    * @param $ptime
    */
   public function printBugViewFields( $type, $work_package, $ptime )
   {
      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_req' ), '</td>';
      echo '<td colspan="5" id="requirement">', $type, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_wpg' ), '</td>';
      echo '<td colspan="5" id="work_package">', $work_package, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_planned_time' ), ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')', '</td>';
      echo '<td colspan="5" id="ptime">', $ptime, '</td>';
      echo '</tr>';
   }

   /**
    * Prints the specific plugin fields in the bug-report user interface
    */
   public function printBugReportFields()
   {
      $database_api = new database_api();
      $work_packages = $database_api->getProjectSpecWorkPackages();

      if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
      {
         $this->printRow();
         echo '<td class="category">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</span></label>';
         echo '</td>';
         echo '<td>';
         echo '<span class="input">';
         echo '<input type="text" id="work_package" name="work_package" list="work_packages"/>';
         echo '<button type="button" onClick="document.getElementById(\'work_package\').value=\'\';">X</button>';
         echo '<datalist id="work_packages">';
         if ( !empty( $work_packages ) )
         {
            foreach ( $work_packages as $existing_work_package )
            {
               echo '<option value="' . $existing_work_package . '">';
            }
         }
         echo '</datalist>';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</td>';
         echo '</tr>';

         $this->printRow();
         echo '<td class="category">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')' . '</span></label>';
         echo '</td>';
         echo '<td>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="ptime" name="ptime" size="50" maxlength="50" value="" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</td>';
         echo '</tr>';
      }
      else
      {
         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</span></label>';
         echo '<span class="input">';
         echo '<input type="text" id="work_package" name="work_package" list="work_packages"/>';
         echo '<button type="button" onClick="document.getElementById(\'work_package\').value=\'\';">X</button>';
         echo '<datalist id="work_packages">';
         if ( !empty( $work_packages ) )
         {
            foreach ( $work_packages as $existing_work_package )
            {
               echo '<option value="' . $existing_work_package . '">';
            }
         }
         echo '</datalist>';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';

         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')' . '</span></label>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="ptime" name="ptime" size="50" maxlength="50" value="" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';
      }
   }

   /**
    * Prints a new chapter title element in a document
    *
    * @param $chapter_index
    * @param $work_package
    * @param $option_show_duration
    * @param $duration
    */
   public function print_chapter_title( $chapter_index, $work_package, $option_show_duration, $duration )
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
    * Prints a new chapter title element in a document
    *
    * @param $chapter_index
    * @param $option_show_duration
    * @param $duration
    */
   public function print_simple_chapter_title( $chapter_index, $option_show_duration, $duration )
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
    * Prints the header element of a document
    *
    * @param $type_string
    * @param $version_id
    * @param $parent_project_id
    * @param $allRelevantBugs
    */
   public function print_document_head( $type_string, $version_id, $parent_project_id, $allRelevantBugs )
   {
      $versions = version_get_all_rows( helper_get_current_project() );
      $act_version = version_get( $version_id );

      echo '<table class="width60">';
      $this->print_doc_head_row( 'head_title', $type_string . ' - ' . version_full_name( $version_id ) );
      $this->print_doc_head_row( 'head_version', version_full_name( $version_id ) );
      $this->print_doc_head_row( 'head_customer', project_get_name( $parent_project_id ) );
      $this->print_doc_head_row( 'head_project', project_get_name( helper_get_current_project() ) );
      $this->print_doc_head_row( 'head_date', date( 'j\.m\.Y' ) );
      $this->print_doc_head_row( 'head_person_in_charge', $this->calculate_person_in_charge() );
      if ( !is_null( $allRelevantBugs ) )
      {
         $this->print_doc_head_row( 'head_process', $this->print_document_progress( $allRelevantBugs ) );
      }
      $this->print_doc_head_versions( $versions, $act_version );
      echo '</table>';
      echo '<br />';
   }


   /**
    * Calculates the process of a document
    *
    * @param $allRelevantBugs
    * @return float
    */
   public function calculate_status_doc_progress( $allRelevantBugs )
   {
      $segments = count( $allRelevantBugs );
      if ( $segments == 0 )
      {
         $segments++;
      }
      $segment_process = 0;
      $bug_spec_progress = 0;

      for ( $segment = 0; $segment < $segments; $segment++ )
      {
         $bug_id = $allRelevantBugs[$segment];

         /**
          * TODO spezifiziere prozentualen Fortschritt
          */
         $bug_status = bug_get_field( $bug_id, 'status' );
         $bug_resolution = bug_get_field( $bug_id, 'resolution' );

         switch ( $bug_resolution )
         {
            case PLUGINS_SPECMANAGEMENT_RES_OPEN:
               $bug_spec_progress = 0;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_FIXED:
               $bug_spec_progress = 100;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_REOPENED:
               $bug_spec_progress = 0;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_UNABLETOREPRODUCE:
               $bug_spec_progress = 100;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_NOTFIXABLE:
               $bug_spec_progress = 100;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_DUPLICATE:
               $bug_spec_progress = 100;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_NOCHANGEREQUIRED:
               $bug_spec_progress = 100;
               break;
            case PLUGINS_SPECMANAGEMENT_RES_SUSPENDED:
               $bug_spec_progress = 0;
               break;
         }

         $segment_process += $bug_spec_progress;
      }

      $document_process = $segment_process / $segments;

      return $document_process;
   }

   /**
    * Gets the sum of each planned time for a bunch of issues
    *
    * @param $allRelevantBugs
    * @return array
    */
   public function calculate_pt_doc_progress( $allRelevantBugs )
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
    * Prints the process of a document
    *
    * @param $allRelevantBugs
    * @return string
    */
   public function print_document_progress( $allRelevantBugs )
   {
      $process_string = '';
      $database_api = new database_api();
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
            $status_process = $this->calculate_status_doc_progress( $allRelevantBugs );
         }

         $process_string .= '<div class="progress400">';
         $process_string .= '<span class="bar" style="width: ' . $status_process . '%;">' . round( $status_process, 2 ) . '%</span>';
         $process_string .= '</div>';
      }
      else
      {
         $sum_pt = $this->calculate_pt_doc_progress( $allRelevantBugs );
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

   # List the attachments belonging to the specified bug.  This is used from within
   # bug_view_inc.php
   function print_bug_attachments_list( $p_bug_id )
   {
      $t_attachments = file_get_visible_attachments( $p_bug_id );
      $t_attachments_count = count( $t_attachments );

      $i = 0;
      $image_previewed = false;

      foreach ( $t_attachments as $t_attachment )
      {
         $t_file_display_name = string_display_line( $t_attachment['display_name'] );
         $t_date_added = date( config_get( 'normal_date_format' ), $t_attachment['date_added'] );

         if ( $image_previewed )
         {
            $image_previewed = false;
            echo '<br />';
         }

         if ( $t_attachment['can_download'] )
         {
            $t_href_start = '<a href="' . string_attribute( $t_attachment['download_url'] ) . '">';
            $t_href_end = '</a>';
         }
         else
         {
            $t_href_start = '';
            $t_href_end = '';
         }

         if ( !$t_attachment['exists'] )
         {
            print_file_icon( $t_file_display_name );
            echo '&#160;<span class="strike">' . $t_file_display_name . '</span>' . lang_get( 'word_separator' ) . '(' . lang_get( 'attachment_missing' ) . ')';
         }
         else
         {
            echo $t_href_start;
            print_file_icon( $t_file_display_name );
            echo $t_href_end . '&#160;' . $t_href_start . $t_file_display_name . $t_href_end . ' <span class="italic">(' . $t_date_added . ')</span>';
         }

         if ( $t_attachment['exists'] )
         {
            if ( ( FTP == config_get( 'file_upload_method' ) ) && $t_attachment['exists'] )
            {
               echo ' (' . lang_get( 'cached' ) . ')';
            }

            if ( $t_attachment['preview'] && ( $t_attachment['type'] == 'text' ) )
            {
               $c_id = db_prepare_int( $t_attachment['id'] );
               $t_bug_file_table = db_get_table( 'mantis_bug_file_table' );

               echo "<script type=\"text/javascript\" language=\"JavaScript\">
<!--
function swap_content( span ) {
displayType = ( document.getElementById( span ).style.display == 'none' ) ? '' : 'none';
document.getElementById( span ).style.display = displayType;
}

 -->
 </script>";
               echo " <span id=\"hideSection_$c_id\">[<a class=\"small\" href='#' id='attmlink_" . $c_id . "' onclick='swap_content(\"hideSection_" . $c_id . "\");swap_content(\"showSection_" . $c_id . "\");return false;'>" . lang_get( 'show_content' ) . "</a>]</span>";
               echo " <span style='display:none' id=\"showSection_$c_id\">[<a class=\"small\" href='#' id='attmlink_" . $c_id . "' onclick='swap_content(\"hideSection_" . $c_id . "\");swap_content(\"showSection_" . $c_id . "\");return false;'>" . lang_get( 'hide_content' ) . "</a>]";

               echo "<pre>";

               /** @todo Refactor into a method that gets contents for download / preview. */
               switch ( config_get( 'file_upload_method' ) )
               {
                  case DISK:
                     if ( $t_attachment['exists'] )
                     {
                        $v_content = file_get_contents( $t_attachment['diskfile'] );
                     }
                     break;
                  case FTP:
                     if ( file_exists( $t_attachment['exists'] ) )
                     {
                        file_get_contents( $t_attachment['diskfile'] );
                     }
                     else
                     {
                        $ftp = file_ftp_connect();
                        file_ftp_get( $ftp, $t_attachment['diskfile'], $t_attachment['diskfile'] );
                        file_ftp_disconnect( $ftp );
                        $v_content = file_get_contents( $t_attachment['diskfile'] );
                     }
                     break;
                  default:
                     $query = "SELECT *
	                  					FROM $t_bug_file_table
				            			WHERE id=" . db_param();
                     $result = db_query_bound( $query, Array( $c_id ) );
                     $row = db_fetch_array( $result );
                     $v_content = $row['content'];
               }

               echo htmlspecialchars( $v_content );
               echo "</pre></span>\n";
            }

            if ( $t_attachment['can_download'] && $t_attachment['preview'] && $t_attachment['type'] == 'image' )
            {
               $t_preview_style = 'border: 0;';
               $t_max_width = config_get( 'preview_max_width' );
               if ( $t_max_width > 0 )
               {
                  $t_preview_style .= ' max-width:' . $t_max_width . 'px;';
               }

               $t_max_height = config_get( 'preview_max_height' );
               if ( $t_max_height > 0 )
               {
                  $t_preview_style .= ' max-height:' . $t_max_height . 'px;';
               }

               $t_preview_style = 'style="' . $t_preview_style . '"';
               $t_title = file_get_field( $t_attachment['id'], 'title' );

               $t_image_url = $t_attachment['download_url'] . '&amp;show_inline=1' . form_security_param( 'file_show_inline' );

               echo "\n<br />$t_href_start<img alt=\"$t_title\" $t_preview_style src=\"$t_image_url\" />$t_href_end";
               $image_previewed = true;
            }
         }

         if ( $i != ( $t_attachments_count - 1 ) )
         {
            echo "<br />\n";
            $i++;
         }
      }
   }

   /**
    * Prints the head of the expenses overview area
    */
   public function print_expenses_overview_head()
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
    * @param $lang_string
    * @param $col_data
    */
   public function print_doc_head_row( $lang_string, $col_data )
   {
      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( $lang_string ) . '</td>';
      echo '<td class="form-title" colspan="3">' . $col_data . '</td>';
      echo '</tr>';
   }

   /**
    * Gets the managers of the current selected project
    *
    * @return string
    */
   public function calculate_person_in_charge()
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
    * @param $versions
    * @param $act_version
    */
   public function print_doc_head_versions( $versions, $act_version )
   {
      foreach ( $versions as $version )
      {
         $same_version = $act_version->id == $version['id'];
         echo '<tr>';
         $this->print_doc_head_version_col( $same_version, date_is_null( $version['date_order'] ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $version['date_order'] ) ) );
         $this->print_doc_head_version_col( $same_version, version_full_name( $version['id'] ) );
         $change_button_string = '<form method="post" name="form_set_source" action="' . plugin_page( 'changes' ) . '">'
            . '<input type="hidden" name="version_old" value="' . $version['id'] . '" />'
            . '<input type="hidden" name="version_act" value="' . $act_version->id . '" />'
            . '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get( 'head_changes' ) . '"/>'
            . '</form>';
         $this->print_doc_head_version_col( $same_version, $change_button_string );
         $show_button_string = '<form method="post" name="form_set_source" action="' . plugin_page( 'editor' ) . '">'
            . '<input type="hidden" name="version_id" value="' . $version['id'] . '" />'
            . '<input type="submit" name="formSubmit" class="button" value="' . plugin_lang_get( 'head_view' ) . '"/>'
            . '</form>';
         $this->print_doc_head_version_col( $same_version, $show_button_string );
         echo '</tr>';
      }
   }

   /**
    * @param $same_version
    * @param $data
    */
   public function print_doc_head_version_col( $same_version, $data )
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
}