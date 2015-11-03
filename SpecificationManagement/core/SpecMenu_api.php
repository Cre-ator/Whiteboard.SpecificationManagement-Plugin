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

      echo '<td>';
      echo '[ <a href="' . plugin_page( 'Specification_Print' ) . '">';
      echo plugin_lang_get( 'menu_printbutton' );
      echo '</a> ]';
      echo '</td>';

      echo '</tr>';
      echo '</table>';
   }
}