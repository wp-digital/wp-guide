<?php
/**
 * Plugin Name:     Innocode WP Guide
 * Description:     Display additional panel in Gutenberg editor with guide
 * Version:         0.1.0
 * Author:          Innocode
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     innocode
 *
 * @package         InnocodeWPGuide
 */

define( 'WP_GUIDE_PLUGIN_PATH', __FILE__ );

require_once __DIR__ . '/includes/class-guide.php';

InnocodeWPGuide\Guide::register();
