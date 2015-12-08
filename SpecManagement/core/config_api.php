<?php

class config_api
{
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

   public function updateButton( $config )
   {
      $button = gpc_get_int( $config );

      if ( plugin_config_get( $config ) != $button )
      {
         plugin_config_set( $config, $button );
      }
   }
}