<?php
// URL to SpecificationManagement plugin
define( 'SPECIFICATIONMANAGEMENT_PLUGIN_URL', config_get_global( 'path' ) . 'plugins/' . plugin_get_current() . '/' );

// Path to SpecificationManagement plugin folder
define( 'SPECIFICATIONMANAGEMENT_PLUGIN_URI', config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR );

// Path to SpecificationManagement core folder
define( 'SPECIFICATIONMANAGEMENT_CORE_URI', SPECIFICATIONMANAGEMENT_PLUGIN_URI . 'core' . DIRECTORY_SEPARATOR );

define( 'PLUGINS_SPECIFICATIONMANAGEMENT_THRESHOLD_LEVEL_DEFAULT', ADMINISTRATOR );
