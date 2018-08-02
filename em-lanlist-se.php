<?php
/*
Plugin Name: EM Lånlist Sverige
Description: Liste for privatlånlistan
Version: 1.0.0
GitHub Plugin URI: zeah/EM-lanlist-se
*/

defined('ABSPATH') or die('Blank Space');

// constant for plugin location
define('LANLIST_SE_PLUGIN_URL', plugin_dir_url(__FILE__));

// require_once 'inc/lanlist-editor.php';
require_once 'inc/lanlist-posttype.php';
// require_once 'inc/lanlist-taxonomy.php';
require_once 'inc/lanlist-shortcode.php';

function init_emlanlistse() {

	Lanlist_posttype::get_instance();
	Lanlist_shortcode::get_instance();

}
add_action('plugins_loaded', 'init_emlanlistse');