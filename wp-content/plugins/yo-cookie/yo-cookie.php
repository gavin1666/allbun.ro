<?php
/*
Plugin Name: Yo Cookie
Plugin URI:  https://plus.google.com/u/0/105572912160369565496
Description: Yo Cookie - flexible plugins to show and manage cookie and privacy agreement
Version: 1.0.2
Author: sgmediabox
Author URI:  https://profiles.wordpress.org/sgmediabox
License: GPLv3 or later
Text Domain: yo-cookie
Domain Path: /languages/
*/

/* */
if( !defined('WPINC') || !defined("ABSPATH") ) die();

/* Plugin path */
define("YO_COOKIE_PATH", 		plugin_dir_path( __FILE__ ) );

/* Plugin version */
define("YO_COOKIE_VERSION", 	'1.0.2' );

/* Plugin url*/
define("YO_COOKIE_URL", 		plugin_dir_url( __FILE__ ) );

/* Class include */
include_once( YO_COOKIE_PATH.'yo-cookie-class.php');
YO_COOKIE::get_instance();
