<?php

class print_api
{
   public function getMantisVersion()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   public function printFormTitle( $colspan, $lang_string )
   {
      echo '<tr>';
      echo '<td class="form-title" colspan="' . $colspan . '">';
      echo plugin_lang_get( $lang_string );
      echo '</td>';
      echo '</tr>';
   }

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

   public function printCategoryField( $colspan, $rowspan, $lang_string )
   {
      echo '<td class="category" colspan="' . $colspan . '" rowspan="' . $rowspan . '">';
      echo plugin_lang_get( $lang_string );
      echo '</td>';
   }

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

   public function printSpacer( $colspan )
   {
      echo '<tr>';
      echo '<td class="spacer" colspan="' . $colspan . '">&nbsp;</td>';
      echo '</tr>';
   }

   public function print_plugin_menu()
   {
      echo '<table align="center">';
      echo '<tr">';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'choose_document' ) . '">';
      echo plugin_lang_get( 'menu_choosedoc' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   public function print_editor_menu()
   {
      echo '<table align="center">';
      echo '<tr">';
      /* General */

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'choose_document' ) . '">';
      echo plugin_lang_get( 'menu_choosedoc' );
      echo '</a> ]';
      echo '</td>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'document_print' ) . '">';
      echo plugin_lang_get( 'menu_printbutton' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   public function printBugUpdateFields( $type, $types, $version, $work_package, $ptime )
   {
      $this->printRow();
      echo '<td class="category">';
      echo '<form name="options" action="" method="get">';
      echo '<label for="option"><span>' . plugin_lang_get( 'bug_view_specification_req' ) . '</span></label>';
      echo '</form>';
      echo '</th>';
      echo '<td colspan="5">';
      echo '<select ' . helper_get_tab_index() . ' name="types">';
      foreach ( $types as $act_type )
      {
         echo '<option value="' . $act_type . '"';
         check_selected( string_attribute( $type ), $act_type );
         echo '>' . string_html_specialchars( $act_type );
         echo '</option>';
      }
      echo '</select>';
      echo '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">' . plugin_lang_get( 'bug_view_specification_src' ) . '</td>';
      echo '<td>';
      echo '<select ' . helper_get_tab_index() . ' name="doc_version">';
      print_version_option_list( $version );
      echo '</select>';
      echo '</td>';
      echo '<td class="category">' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</td>';
      echo '<td colspan="3">';
      echo '<input ', helper_get_tab_index(), ' type="text" id="work_package" name="work_package" size="50" maxlength="50" value="', $work_package, '" />';
      echo '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category"><label for="ptime">' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')' . '</label></td>';
      echo '<td colspan="5">';
      echo '<input ', helper_get_tab_index(), ' type="text" id="ptime" name="ptime" size="50" maxlength="50" value="', $ptime, '" />';
      echo '</td>';
      echo '</tr>';
   }

   public function printBugViewFields( $requirement, $version, $work_package, $ptime )
   {
      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_req' ), '</td>';
      echo '<td colspan="5" id="types">', $requirement, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_src' ), '</td>';
      echo '<td id="doc_version">', $version, '</td>';
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_wpg' ), '</td>';
      echo '<td colspan="3" id="work_package">', $work_package, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_planned_time' ), ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')', '</td>';
      echo '<td colspan="5" id="ptime">', $ptime, '</td>';
      echo '</tr>';
   }

   public function printBugReportFields( $types, $version, $work_package, $ptime )
   {
      if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
      {
         $this->printRow();
         echo '<td class="category">';
         echo '<form name="options" action="" method="get">';
         echo '<label for="option"><span>' . plugin_lang_get( 'bug_view_specification_req' ) . '</span></label>';
         echo '</td>';
         echo '<td>';
         echo '<select ' . helper_get_tab_index() . ' name="types">';
         foreach ( $types as $type )
         {
            echo '<option value="' . $type . '">' . $type . '</option>';
         }
         echo '<span class="label-style"></span>';
         echo '</td>';
         echo '</form>';
         echo '</tr>';

         $this->printRow();
         echo '<td class="category">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_specification_src' ) . '</span></label>';
         echo '</td>';
         echo '<td>';
         echo '<select ' . helper_get_tab_index() . ' name="doc_version">';
         print_version_option_list( $version );
         echo '</select>';
         echo '<span class="label-style"></span>';
         echo '</td>';
         echo '</tr>';

         $this->printRow();
         echo '<td class="category">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</span></label>';
         echo '</td>';
         echo '<td>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="work_package" name="work_package" size="50" maxlength="50" value="' . string_attribute( $work_package ) . '" />';
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
         echo '<input ' . helper_get_tab_index() . ' type="text" id="ptime" name="ptime" size="50" maxlength="50" value="' . string_attribute( $ptime ) . '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</td>';
         echo '</tr>';
      }
      else
      {
         echo '<div class="field-container">';
         echo '<form name="options" action="" method="get">';
         echo '<label for="option"><span>' . plugin_lang_get( 'bug_view_specification_req' ) . '</span></label>';
         echo '<span class="select">';
         echo '<select ' . helper_get_tab_index() . ' id="types" name="types">';
         foreach ( $types as $type )
         {
            echo '<option value="' . $type . '">' . $type . '</option>';
         }
         echo '</select>';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</form>';
         echo '</div>';

         echo '<div class="field-container">';
         echo '<label for="doc_version"><span>' . plugin_lang_get( 'bug_view_specification_src' ) . '</span></label>';
         echo '<span class="select">';
         echo '<select ' . helper_get_tab_index() . ' name="doc_version">';
         print_version_option_list( $version );
         echo '</select>';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';

         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_specification_wpg' ) . '</span></label>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="work_package" name="work_package" size="50" maxlength="50" value="' . string_attribute( $work_package ) . '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';

         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')' . '</span></label>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="ptime" name="ptime" size="50" maxlength="50" value="' . string_attribute( $ptime ) . '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';
      }
   }

   public function print_chapter_title( $chapter_index, $chapter_title, $print_duration, $duration )
   {
      if ( is_null( $duration ) )
      {
         $duration = plugin_lang_get( 'editor_work_package_duration_null' );
      }

      echo '<tr>';
      echo '<td class="workpackagehead" colspan="1">' . $chapter_index . '</td>';
      echo '<td class="workpackagehead" colspan="2">' . $chapter_title;
      if ( !is_null( $print_duration ) )
      {
         echo ' [' . plugin_lang_get( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ']';
      }
      echo '</td>';
      echo '</tr>';
   }

   public function print_document_head( $document_type, $version, $parent_project, $allRelevantBugs )
   {
      echo '<table class="width100">';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_title' ) . '</td>';
      echo '<td class="form-title">' . $document_type . ' - ' . $version . '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_customer' ) . '</td>';
      echo '<td class="form-title">' . project_get_name( $parent_project ) . '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_project' ) . '</td>';
      echo '<td class="form-title">' . project_get_name( helper_get_current_project() ) . '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_date' ) . '</td>';
      echo '<td class="form-title">' . date( 'j\. F Y' ) . '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_person_in_charge' ) . '</td>';
      echo '<td class="form-title">' . user_get_realname( auth_get_current_user_id() ) . '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_process' ) . '</td>';
      echo '<td class="form-title">';
      $this->print_document_progress( $allRelevantBugs );
      echo '</td>';
      echo '</tr>';

      echo '</table>';

      echo '<br />';
   }

   public function print_bugs( $chapter_index, $sub_chapter_index, $bug_id, $print_duration, $ptime )
   {
      $bug_description = bug_get_text_field( $bug_id, 'description' );
      $bug_streproduce = bug_get_text_field( $bug_id, 'steps_to_reproduce' );
      $bug_ainformation = bug_get_text_field( $bug_id, 'additional_information' );
      $bug_attachments = bug_get_attachments( $bug_id );
      $bug_bugnotes = bugnote_get_all_bugnotes( $bug_id );

      if ( is_null( $ptime ) )
      {
         $ptime = plugin_lang_get( 'editor_work_package_duration_null' );
      }

      echo '<tr>';
      echo '<td colspan="1">' . $chapter_index . '.' . $sub_chapter_index . '</td>';
      echo '<td colspan="2">' . bug_get_field( $bug_id, 'summary' ) . ' (';
      print_bug_link( $bug_id, true );
      echo ')';
      if ( !is_null( $print_duration ) )
      {
         echo ', ' . plugin_lang_get( 'editor_bug_duration' ) . ': ' . $ptime . ' ' . plugin_lang_get( 'editor_duration_unit' );
      }
      echo '</td>';
      echo '</tr>';

      $this->print_bug_infos( $bug_description );
      $this->print_bug_infos( $bug_streproduce );
      $this->print_bug_infos( $bug_ainformation );
      if ( !empty( $bug_attachments ) )
      {
         $this->print_bug_attachments( $bug_id );
      }
      if ( !empty( $bug_bugnotes ) )
      {
         $this->print_bugnote_note( $bug_id );
      }
   }

   /**
    * @param $info
    * @internal param $bug_description
    */
   public function print_bug_infos( $info )
   {
      if ( !is_null( $info ) )
      {
         echo '<tr>';
         echo '<td colspan="1" />';
         echo '<td colspan="2">' . $info . '</td>';
         echo '</tr>';
      }
   }

   public function print_bug_attachments( $bug_id )
   {
      $attachment_count = file_bug_attachment_count( $bug_id );
      echo '<tr>';
      echo '<td colspan="1" />';
      echo '<td class="infohead" colspan="2">' . plugin_lang_get( 'editor_bug_attachments' ) . ' (' . $attachment_count . ')</td>';
      echo '</tr>';

      echo '<tr id="attachments">';
      echo '<td colspan="1" />';
      echo '<td class="bug-attachments" colspan="2">';
      $this->print_bug_attachments_list( $bug_id );
      echo '</td>';
      echo '</tr>';
   }

   public function print_bugnote_note( $bug_id )
   {
      $bugnote_count = count( bugnote_get_all_bugnotes( $bug_id ) );
      echo '<tr>';
      echo '<td colspan="1" />';
      echo '<td class="infohead" colspan="2">' . plugin_lang_get( 'editor_bug_notes_note' ) . ' (' . $bugnote_count . ')</td>';
      echo '</tr>';
   }

   private function calculate_document_progress( $allRelevantBugs )
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

         $bug_resolution = bug_get_field( $bug_id, 'resolution' );

         /* TODO spezifiziere prozentualen Fortschritt
          * 10:offen,
          * 20:erledigt,
          * 30:wiedereröffnet,
          * 40:nicht reproduzierbar,
          * 50:unlösbar,
          * 60:doppelt,
          * 70:keine Änderung notwendig,
          * 80:aufgeschoben,
          * 90:wird nicht behoben ---
          */
         switch ( $bug_resolution )
         {
            case 10:
               $bug_spec_progress = 0;
               break;
            case 20:
               $bug_spec_progress = 100;
               break;
            case 30:
               $bug_spec_progress = 0;
               break;
            case 40:
               $bug_spec_progress = 100;
               break;
            case 50:
               $bug_spec_progress = 100;
               break;
            case 60:
               $bug_spec_progress = 100;
               break;
            case 70:
               $bug_spec_progress = 100;
               break;
            case 80:
               $bug_spec_progress = 0;
               break;
         }

         $segment_process += $bug_spec_progress;
      }

      $document_process = $segment_process / $segments;

      return $document_process;
   }

   public function print_document_progress( $allRelevantBugs )
   {
      $document_process = 0;
      if ( !empty( $allRelevantBugs ) )
      {
         $document_process = $this->calculate_document_progress( $allRelevantBugs );
      }

      echo '<div class="progress400">';
      echo '<span class="bar" style="width: ' . $document_process . '%;">' . $document_process . '%</span>';
      echo '</div>';
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
}