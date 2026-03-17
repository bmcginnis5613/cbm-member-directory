<?php
/**
 * Plugin Name:       Member Directory
 * Plugin URI:        https://firsttracksmarketing.com
 * Description:       A lightweight member directory for WooCommerce Memberships. Displays members of a specific membership plan with custom profile fields. Embed with [member_directory] shortcode.
 * Version:           1.1.2
 * Author:            FirstTracks Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BMD_VERSION', '1.0.0' );
define( 'BMD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BMD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load core files
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-fields.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-query.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-shortcode.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-admin.php';
require_once BMD_PLUGIN_DIR . 'includes/class-bmd-profile.php';

/**
 * Bootstrap the plugin.
 */
function bmd_init() {
    BMD_Fields::init();
    BMD_Shortcode::init();
    BMD_Admin::init();
    BMD_Profile::init();
}

add_action( 'plugins_loaded', 'bmd_init' );

/**
 * Enqueue front-end assets only when the shortcode is present.
 */
function bmd_enqueue_assets() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'member_directory' ) ) {
        wp_enqueue_style(
            'bmd-styles',
            BMD_PLUGIN_URL . 'assets/css/directory.css',
            [],
            BMD_VERSION
        );
        wp_enqueue_script(
            'bmd-scripts',
            BMD_PLUGIN_URL . 'assets/js/directory.js',
            [],
            BMD_VERSION,
            true
        );
        wp_localize_script( 'bmd-scripts', 'BMD', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'bmd_nonce' ),
        ] );
    }
}
add_action( 'wp_enqueue_scripts', 'bmd_enqueue_assets' );

/**
 * Activation hook
 */
function bmd_activate() {
    // Future: create custom DB table for extended fields if needed.
}
register_activation_hook( __FILE__, 'bmd_activate' );
