<?php
// URL to SpecManagement plugin
define( 'SPECMANAGEMENT_PLUGIN_URL', config_get_global( 'path' ) . 'plugins/' . plugin_get_current() . '/' );

// Path to SpecManagement plugin folder
define( 'SPECMANAGEMENT_PLUGIN_URI', config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR );

// Path to SpecManagement core folder
define( 'SPECMANAGEMENT_CORE_URI', SPECMANAGEMENT_PLUGIN_URI . 'core' . DIRECTORY_SEPARATOR );

define( 'PLUGINS_SPECMANAGEMENT_THRESHOLD_LEVEL_DEFAULT', ADMINISTRATOR );

define( 'PLUGINS_SPECMANAGEMENT_READ_LEVEL_DEFAULT', REPORTER );

define( 'PLUGINS_SPECMANAGEMENT_WRITE_LEVEL_DEFAULT', DEVELOPER );
