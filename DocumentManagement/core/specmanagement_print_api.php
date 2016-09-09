<?php

class specmanagement_print_api
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
    * Prints head elements of a page
    * @param $string
    */
   public function print_page_head( $string )
   {
      html_page_top1( $string );
      echo '<script language="javascript" type="text/javascript" src="' . SPECMANAGEMENT_PLUGIN_URL . 'files/checkbox.js"></script>';
      echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/specmanagement.css">';
      html_page_top2();
      if ( plugin_is_installed ( 'WhiteboardMenu' )
         && file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' )
      )
      {
         require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
            'WhiteboardMenu' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'wmApi.php' );
         echo '<link rel="stylesheet" href="plugins/WhiteboardMenu/files/whiteboardmenu.css"/>';
         wmApi::printWhiteboardMenu ();
      }
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
    * Prints top element(s) of the table
    *
    * @param $table_width
    */
   public function printTableTop( $table_width )
   {
      if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
      {
         echo '<table class="width' . $table_width . '">';
      }
      else
      {
         echo '<div class="table-container">';
         echo '<table>';
      }
   }

   /**
    * Prints foot element(s) of the table
    */
   public function printTableFoot()
   {
      echo '</table>';
      if ( substr( MANTIS_VERSION, 0, 4 ) != '1.2.' )
      {
         echo '</div>';
      }
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
    * Prints the specific plugin fields in the bug-update user interface
    *
    * @param $type
    * @param $work_package
    * @param $ptime
    */
   public function printBugUpdateFields( $type, $work_package, $ptime )
   {
      echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/specmanagement_tooltip.css">';
      $specmanagement_database_api = new specmanagement_database_api();
      $work_packages = $specmanagement_database_api->get_project_spec_workpackages();

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_view_specification_req' ), '</td>';
      echo '<td colspan="5" id="requirement">', $type, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">';
      echo plugin_lang_get( 'bug_view_specification_wpg' ) . '<br />';
      $this->print_workpackage_description_field();
      echo '</td>';
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
      echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/specmanagement_tooltip.css">';
      $this->printRow();
      echo '<td class="category">' . plugin_lang_get( 'bug_view_specification_req' ) . '</td>';
      echo '<td colspan="5" id="requirement">' . $type . '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">';
      echo plugin_lang_get( 'bug_view_specification_wpg' ) . '<br />';
      $this->print_workpackage_description_field();
      echo '</td>';
      echo '<td colspan="5" id="work_package">' . $work_package . '&nbsp</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">' . plugin_lang_get( 'bug_view_planned_time' ) . ' (' . plugin_lang_get( 'editor_duration_unit' ) . ')' . '</td>';
      echo '<td colspan="5" id="ptime">' . $ptime . '</td>';
      echo '</tr>';
   }

   /**
    * Prints the specific plugin fields in the bug-report user interface
    */
   public function printBugReportFields()
   {
      echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_PLUGIN_URL . 'files/specmanagement_tooltip.css">';
      $specmanagement_database_api = new specmanagement_database_api();
      $work_packages = $specmanagement_database_api->get_project_spec_workpackages();

      if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
      {
         $this->printRow();
         echo '<td class="category">';
         echo '<label><span>';
         echo plugin_lang_get( 'bug_view_specification_wpg' ) . '<br />';
         $this->print_workpackage_description_field();
         echo '</span></label>';
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
         echo '</td>';
         echo '</tr>';

         $this->printRow();
         echo '<td class="category" > ';
         echo '<label ><span > ' . plugin_lang_get( 'bug_view_planned_time' ) . ' ( ' . plugin_lang_get( 'editor_duration_unit' ) . ')' . ' </span ></label > ';
         echo '</td > ';
         echo '<td > ';
         echo '<span class="input" > ';
         echo '<input ' . helper_get_tab_index() . ' type = "text" id = "ptime" name = "ptime" size = "50" maxlength = "50" value = "" />';
         echo '</span > ';
         echo '<span class="label-style" ></span > ';
         echo '</td > ';
         echo '</tr > ';
      }
      else
      {
         echo '<div class="field-container" > ';
         echo '<label><span >';
         echo plugin_lang_get( 'bug_view_specification_wpg' ) . '<br />';
         $this->print_workpackage_description_field();
         echo ' </span ></label > ';
         echo '<span class="input" > ';
         echo '<input type = "text" id = "work_package" name = "work_package" list = "work_packages" />';
         echo '<button type = "button" onClick = "document.getElementById(\'work_package\').value=\'\';" > X</button > ';
         echo '<datalist id = "work_packages" > ';
         if ( !empty( $work_packages ) )
         {
            foreach ( $work_packages as $existing_work_package )
            {
               echo '<option value = "' . $existing_work_package . '" > ';
            }
         }
         echo '</datalist > ';
         echo '</span > ';
         echo '<span class="label-style" ></span > ';
         echo '</div > ';

         echo '<div class="field-container" > ';
         echo '<label ><span > ' . plugin_lang_get( 'bug_view_planned_time' ) . ' ( ' . plugin_lang_get( 'editor_duration_unit' ) . ')' . ' </span ></label > ';
         echo '<span class="input" > ';
         echo '<input ' . helper_get_tab_index() . ' type = "text" id = "ptime" name = "ptime" size = "50" maxlength = "50" value = "" />';
         echo '</span > ';
         echo '<span class="label-style" ></span > ';
         echo '</div > ';
      }
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
            echo ' < br />';
         }

         if ( $t_attachment['can_download'] )
         {
            $t_href_start = ' < a href = "' . string_attribute( $t_attachment['download_url'] ) . '" > ';
            $t_href_end = '</a > ';
         }
         else
         {
            $t_href_start = '';
            $t_href_end = '';
         }

         if ( !$t_attachment['exists'] )
         {
            print_file_icon( $t_file_display_name );
            echo ' &#160;<span class="strike">' . $t_file_display_name . '</span>' . lang_get( 'word_separator' ) . '(' . lang_get( 'attachment_missing' ) . ')';
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
    * Prints description how to define and seperate workpackages / chapters
    */
   public function print_workpackage_description_field()
   {
      echo '<span class="small">' . plugin_lang_get( 'bug_view_work_package_description' ) . '</span>';
      echo '<a class="rcv_tooltip">';
      echo '&nbsp[i]';
      echo '<span>';
      echo '<div class="rcv_tooltip_content">';
      echo utf8_substr( string_email_links( plugin_lang_get( 'bug_view_work_package_description_detailed' ) ), 0, 255 );
      echo '</div>';
      echo '</span>';
      echo '</a>';
   }
}