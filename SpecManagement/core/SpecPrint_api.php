<?php

class SpecPrint_api
{
   public function system_getMantisVersion()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   public function printRow()
   {
      if ( $this->system_getMantisVersion() == '1.2' )
      {
         echo '<tr class="row-1">';
      }
      else
      {
         echo '<tr>';
      }
   }

   public function printBugUpdateFields( $types, $source, $ptime )
   {
      $this->printRow();
      echo '<th class="category">';
      echo '<form name="options" action="" method="get">';
      echo '<label for="option"><span>' . plugin_lang_get( 'bug_add_form_specification_req' ) . '</span></label>';
      echo '</form>';
      echo '</th>';
      echo '<td colspan="5">';
      /* resort the list into ascending order */
      /* Referenz: print_api.php -> Zeile 996 */
      ksort( $types );
      reset( $types );
      /* TODO check_selected arbeitet noch nicht richtig */
      echo '<select ' . helper_get_tab_index() . 'id="types" name="types">';
      foreach ( $types as $type )
      {
         echo '<option value="' . $type . '"';
         echo '>' . string_html_specialchars( $type ) . '</option>';
      }
      echo '</select>';
      echo '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<th class="category"><label for="source">' . plugin_lang_get( 'bug_add_form_specification_src' ) . '</label></th>';
      echo '<td colspan="5">';
      echo '<input ', helper_get_tab_index(), ' type="text" id="source" name="source" size="105" maxlength="128" value="', $source, '" />';
      echo '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<th class="category"><label for="ptime">' . plugin_lang_get( 'bug_add_form_planned_time' ) . plugin_lang_get( 'bug_add_form_ptime_expl' ) . '</label></th>';
      echo '<td colspan="5">';
      echo '<input ', helper_get_tab_index(), ' type="text" id="ptime" name="ptime" size="105" maxlength="128" value="', $ptime, '" />';
      echo '</td>';
      echo '</tr>';
   }

   public function printBugViewFields( $requirement, $source, $ptime )
   {
      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_add_form_specification_req' ), '</td>';
      echo '<td colspan="5" id="types">', $requirement, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_add_form_specification_src' ), '</td>';
      echo '<td colspan="5" id="source">', $requirement . ': ' . $source, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_add_form_planned_time' ), plugin_lang_get( 'bug_add_form_ptime_expl' ), '</td>';
      echo '<td colspan="5" id="ptime">', $ptime, '</td>';
      echo '</tr>';
   }

   public function printBugReportFields( $types, $source, $ptime )
   {
      if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
      {
         /* TODO: 1.2 Content */
      }
      else
      {
         echo '<div class="field-container">';
         echo '<form name="options" action="" method="get">';
         echo '<label for="option"><span>' . plugin_lang_get( 'bug_add_form_specification_req' ) . '</span></label>';
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
         echo '<label><span>' . plugin_lang_get( 'bug_add_form_specification_src' ) . '</span></label>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="source" name="source" size="105" maxlength="128" value="' . string_attribute( $source ) . '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';

         echo '<div class="field-container">';
         echo '<label><span>' . plugin_lang_get( 'bug_add_form_planned_time' ) . plugin_lang_get( 'bug_add_form_ptime_expl' ) . '</span></label>';
         echo '<span class="input">';
         echo '<input ' . helper_get_tab_index() . ' type="text" id="ptime" name="ptime" size="105" maxlength="128" value="' . string_attribute( $ptime ) . '" />';
         echo '</span>';
         echo '<span class="label-style"></span>';
         echo '</div>';
      }
   }

   public function print_chapter_title( $chapter_index, $chapter_title, $duration )
   {
      if ( is_null( $duration ) )
      {
         $duration = plugin_lang_get( 'editor_work_package_duration_null' );
      }

      echo '<tr>';
      echo '<td class="workpackagehead" colspan="1">' . $chapter_index . '</td>';
      echo '<td class="workpackagehead" colspan="2">' . $chapter_title;
      if ( plugin_config_get( 'ShowDuration' ) )
      {
         echo ' [' . plugin_lang_get( 'editor_work_package_duration' ) . ': ' . $duration . ' ' . plugin_lang_get( 'editor_duration_unit' ) . ']';
      }
      echo '</td>';
      echo '</tr>';
   }

   public function print_document_head( $document_type, $source )
   {
      echo '<table class="width100">';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_title' ) . '</td>';
      echo '<td class="form-title">' . $document_type . ' - ' . $source . '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td class="field-container">' . plugin_lang_get( 'head_customer' ) . '</td>';
      echo '<td class="form-title">' . project_get_name( helper_get_current_project() ) . '</td>';
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
      echo '<td class="form-title">' . user_get_name( auth_get_current_user_id() ) . '</td>';
      echo '</tr>';

      echo '</table>';

      echo '<br />';
   }

   public function print_bugs( $chapter_index, $sub_chapter_index, $bug_id, $ptime )
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
      if ( plugin_config_get( 'ShowDuration' ) )
      {
         echo ', ' . plugin_lang_get( 'ptime' ) . ': ' . $ptime . ' ' . plugin_lang_get( 'editor_duration_unit' );
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
      print_bug_attachments_list( $bug_id );
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

      echo '<tr>';
      echo '<td colspan="1" />';
      echo '<td class="infohead" colspan="2">';
      print_bug_link( $bug_id, true );
      echo '</td>';
      echo '</tr>';
   }
}