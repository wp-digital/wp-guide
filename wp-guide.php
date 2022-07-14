<?php
/**
 * Plugin Name:     WP Guide
 * Description:     Display additional panel in Gutenberg editor with guide
 * Version:         1.2.0
 * Author:          Innocode
 * Author URI:      https://innocode.com
 * License:         GPLv2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use Innocode\Guide;

define( 'INNOCODE_GUIDE_FILE', __FILE__ );

Guide\Plugin::register();
