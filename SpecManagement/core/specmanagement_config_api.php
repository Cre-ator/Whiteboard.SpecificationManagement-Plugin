<?php

class specmanagement_config_api
{
   /**
    * Updates a value in the plugin configuration
    *
    * @param $value
    * @param $constant
    */
   public function updateValue( $value, $constant )
   {
      $act_value = null;

      if ( is_int( $value ) )
      {
         $act_value = gpc_get_int( $value, $constant );
      }

      if ( is_string( $value ) )
      {
         $act_value = gpc_get_string( $value, $constant );
      }

      if ( plugin_config_get( $value ) != $act_value )
      {
         plugin_config_set( $value, $act_value );
      }
   }

   /**
    * Updates a button in the plugin configuration
    *
    * @param $config
    */
   public function updateButton( $config )
   {
      $button = gpc_get_int( $config );

      if ( plugin_config_get( $config ) != $button )
      {
         plugin_config_set( $config, $button );
      }
   }

   /**
    * @param $value
    * @param $constant
    */
   public function updateDynamicValues( $value, $constant )
   {
      $column_amount = plugin_config_get( 'CAmount' );

      for ( $columnIndex = 1; $columnIndex <= $column_amount; $columnIndex++ )
      {
         $act_value = $value . $columnIndex;

         $this->updateValue( $act_value, $constant );
      }
   }
}