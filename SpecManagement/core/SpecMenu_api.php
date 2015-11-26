<?php

class SpecMenu_api
{
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