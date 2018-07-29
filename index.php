<?php

/*
Plugin Name: BMW parser
Description: BMW parser
Plugin URI: http://#
Author: BMW parser
Author URI: http://#
Version: 1.0
License: GPL2
*/

set_time_limit(300);

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WCST_PARSER_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WCST_PARSER_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCST_PARSER_WP_UPLOADS_DIR_PATH', wp_upload_dir()['basedir'] );



function wcst_parser_activate() {

}
register_activation_hook( __FILE__, 'wcst_parser_activate' );


function wcst_parser_deactivate() {

}
register_deactivation_hook(  __FILE__, 'wcst_parser_deactivate' );




include('inc/admin_side.php');