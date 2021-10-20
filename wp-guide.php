<?php
/**
 * Plugin Name:     Innocode WP Guide
 * Description:     Display additional panel in Gutenberg editor with guide
 * Version:         1.1.0
 * Author:          Innocode
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     innocode
 *
 * @package         InnocodeWPGuide
 */

define( 'INNOCODE_WP_GUIDE_PLUGIN_PATH', __FILE__ );
define( 'INNOCODE_WP_GUIDE', 'innocode_wp_guide' );
define( 'INNOCODE_WP_GUIDE_VERSION', '0.4.0' );

require_once __DIR__ . '/includes/class-guide.php';

InnocodeWPGuide\Guide::register();
