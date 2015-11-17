<?php

class SpecMenu_api
{
   public function printWhiteboardMenu()
   {
      echo '<table align="center">';
      echo '<tr">';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'UserProject', true, 'UserProjectView' ) . '&sortVal=userName&sort=ASC">';
      echo plugin_lang_get( 'menu_userprojecttitle', 'UserProjectView' );
      echo '</a> ]';
      echo '</td>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'SpecManagement' ) . '">';
      echo plugin_lang_get( 'menu_title' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   public function printPluginMenu()
   {
      echo '<table align="center">';
      echo '<tr">';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'ChooseDocument' ) . '">';
      echo plugin_lang_get( 'menu_choosedoc' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }

   public function printEditorMenu()
   {
      echo '<table align="center">';
      echo '<tr">';

      /* Editor */

//      echo '<td>';
//      echo '<form method="post" name="form_set_requirement" action="' . plugin_page( 'Editor_update' ) . '">';
//      echo '<input type="submit" name="newChapter" class="button-small" value="' . plugin_lang_get( 'menu_new_chapter' ) . '" />';
//      echo '</form>';
//      echo '</td>';
//
//      echo '<td>';
//      echo '<form method="post" name="form_set_requirement" action="' . plugin_page( 'Editor_update' ) . '">';
//      echo '<input type="submit" name="newSubChapter" class="button-small" value="' . plugin_lang_get( 'menu_new_sub_chapter' ) . '" />';
//      echo '</form>';
//      echo '</td>';


      /* General */

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'ChooseDocument' ) . '">';
      echo plugin_lang_get( 'menu_choosedoc' );
      echo '</a> ]';
      echo '</td>';

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'Specification_Print' ) . '">';
      echo plugin_lang_get( 'menu_printbutton' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }
}