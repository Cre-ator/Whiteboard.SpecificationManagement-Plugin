<?php

class SpecConfig_api
{
   public function printFormTitle( $colspan, $langString )
   {
      echo '<tr>';
      echo '<td class="form-title" colspan="' . $colspan . '">';
      echo plugin_lang_get( $langString );
      echo '</td>';
      echo '</tr>';
   }

   public function printTableRow()
   {
      if ( substr( MANTIS_VERSION, 0, 4 ) == '1.2.' )
      {
         echo '<tr ' . helper_alternate_class() . '>';
      }
      else
      {
         echo '<tr>';
      }
   }

   public function printCategoryField( $colspan, $rowspan, $langString )
   {
      echo '<td class="category" colspan="' . $colspan . '" rowspan="' . $rowspan . '">';
      echo plugin_lang_get( $langString );
      echo '</td>';
   }

   public function printRadioButton( $colspan, $name )
   {
      echo '<td width="100px" colspan="' . $colspan . '">';
      echo '<label>';
      echo '<input type="radio" name="' . $name . '" value="1"';
      echo ( ON == plugin_config_get( $name ) ) ? 'checked="checked"' : '';
      echo '/>' . plugin_lang_get( 'config_y' );
      echo '</label>';
      echo '<label>';
      echo '<input type="radio" name="' . $name . '" value="0"';
      echo ( OFF == plugin_config_get( $name ) ) ? 'checked="checked"' : '';
      echo '/>' . plugin_lang_get( 'config_n' );
      echo '</label>';
      echo '</td>';
   }

   public function printSpacer( $colspan )
   {
      echo '<tr>';
      echo '<td class="spacer" colspan="' . $colspan . '">&nbsp;</td>';
      echo '</tr>';
   }

   public function updateValue( $value, $constant )
   {
      $actValue = null;

      if ( is_int( $value ) )
      {
         $actValue = gpc_get_int( $value, $constant );
      }

      if ( is_string( $value ) )
      {
         $actValue = gpc_get_string( $value, $constant );
      }

      if ( plugin_config_get( $value ) != $actValue )
      {
         plugin_config_set( $value, $actValue );
      }
   }

   public function updateButton( $config )
   {
      $button = gpc_get_int( $config );

      if ( plugin_config_get( $config ) != $button )
      {
         plugin_config_set( $config, $button );
      }
   }
}