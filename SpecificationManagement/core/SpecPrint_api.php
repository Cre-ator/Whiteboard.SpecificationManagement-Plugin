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

   public function printBugUpdateFields( $types, $source )
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
   }

   public function printBugViewFields( $requirement, $source )
   {
      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_add_form_specification_req' ), '</td>';
      echo '<td colspan="5" id="types">', $requirement, '</td>';
      echo '</tr>';

      $this->printRow();
      echo '<td class="category">', plugin_lang_get( 'bug_add_form_specification_src' ), '</td>';
      echo '<td colspan="5" id="source">', $requirement . ': ' . $source, '</td>';
      echo '</tr>';
   }

   public function printBugReportFields( $types, $source )
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
      }
   }

   public function print_chapter_title( $chapter_index, $chapter_title )
   {
      echo '<tr>';
      echo '<td class="form-title" colspan="1">' . $chapter_index . '</td>';
      echo '<td class="form-title" colspan="2">' . $chapter_title . '</td>';
      echo '</tr>';
   }

   public function print_bugs( $chapter_index, $sub_chapter_index, $bug_id )
   {
      echo '<tr>';
      echo '<td colspan="1">' . $chapter_index . '.' . $sub_chapter_index . '</td>';
      echo '<td colspan="2">' . bug_get_field( $bug_id, 'summary' ) . ' (' . bug_format_id( $bug_id ) . ')</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td colspan="1" />';
      echo '<td colspan="2">' . bug_get_text_field( $bug_id, 'description' ) . '</td>';
      echo '</tr>';
   }

   public function print_bugnotes( $bugnotes )
   {
      if ( $bugnotes != null )
      {
         echo '<tr>';
         echo '<td colspan="1" />';
         echo '<td colspan="2">Notizen</td>';
         echo '</tr>';
         $bugnote_index = 1;
         foreach ( $bugnotes as $bugnote )
         {
            echo '<tr>';
            echo '<td colspan="1" />';
            echo '<td colspan="1">' . $bugnote_index . '</td>';
            echo '<td colspan="1">' . $bugnote->note . '</td>';
            echo '</tr>';

            $bugnote_index++;
         }
      }
   }
}