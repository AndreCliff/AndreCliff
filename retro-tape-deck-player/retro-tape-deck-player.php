<?php
/**
 * Plugin Name: Retro Tape Deck MP3 Player
 * Plugin URI:  https://github.com/AndreCliff/retro-tape-deck-player
 * Description: A nostalgic early-1990s dual tape deck MP3 player shortcode for WordPress.
 * Version:     1.0.0
 * Author:      AndreCliff
 * License:     GPL-2.0-or-later
 * Text Domain: retro-tape-deck
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'RTD_VERSION', '1.0.0' );
define( 'RTD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RTD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once RTD_PLUGIN_DIR . 'includes/class-rtd-admin.php';
require_once RTD_PLUGIN_DIR . 'includes/class-rtd-shortcode.php';

/**
 * Enqueue front-end assets only when the shortcode is present.
 */
function rtd_enqueue_assets() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'retro_tape_deck' ) ) {
        wp_enqueue_style(
            'rtd-style',
            RTD_PLUGIN_URL . 'assets/css/tape-deck.css',
            array(),
            RTD_VERSION
        );
        wp_enqueue_script(
            'rtd-script',
            RTD_PLUGIN_URL . 'assets/js/tape-deck.js',
            array(),
            RTD_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'rtd_enqueue_assets' );

/**
 * Register the shortcode.
 */
function rtd_register_shortcode() {
    add_shortcode( 'retro_tape_deck', array( 'RTD_Shortcode', 'render' ) );
}
add_action( 'init', 'rtd_register_shortcode' );

/**
 * Boot the admin page.
 */
if ( is_admin() ) {
    new RTD_Admin();
}
