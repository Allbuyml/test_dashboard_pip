<?php
/*
Plugin Name: Dashboard Task Tracker
Description: Enterprise Dashboard V16.0 (Robust Fallbacks, Custom URLs, Dynamic Edits).
Version: 16.0
Author: JesusDevPIP
Text Domain: dashboard-task-tracker
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DTT_VERSION', '16.0' );
define( 'DTT_PATH', plugin_dir_path( __FILE__ ) );
define( 'DTT_URL', plugin_dir_url( __FILE__ ) );

require_once DTT_PATH . 'core/class-dtt-loader.php';

function dtt_init() {
    $plugin = new DTT_Loader();
    $plugin->run();
    
    // Register actions for custom form submissions directly
    require_once DTT_PATH . 'core/class-dtt-form-handler.php';
    $form_handler = new DTT_Form_Handler();
    add_action( 'admin_post_dtt_update_project', array( $form_handler, 'handle_update' ) );
    add_action( 'admin_post_dtt_create_project', array( $form_handler, 'handle_create' ) );
}
add_action( 'plugins_loaded', 'dtt_init' );

register_activation_hook( __FILE__, 'dtt_activate' );
function dtt_activate() {
    require_once DTT_PATH . 'core/class-dtt-cpt.php';
    $cpt = new DTT_CPT();
    $cpt->register_cpt();
    flush_rewrite_rules();
}