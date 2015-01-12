<?php
/*
Plugin Name: Smart Nav-Bar
Plugin URI: http://www.loudlever.com/wordpress-plugins/smart-navbar/
Description: A navbar to help your readers better navigate your site when reading.
Author: Loudlever
Author URI: http://www.loudlever.com
Version: 0.0.1

  Copyright 2014-2015 Loudlever (wordpress@loudlever.com)

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.

*/

// // Make sure we don't expose any info if called directly
// if ( !function_exists( 'add_action' ) ) {
//  echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
//  exit;
// }
// 
// /*
// ---------------------------------------------------------------------------------
//   OPTION SETTINGS
// ---------------------------------------------------------------------------------
// */  
// 
// define('SGW_DEBUG',false);
// define('SGW_PLUGIN_VERSION', '2.1.1');
// define('SGW_PLUGIN_OPTTIONS', '_sgw_plugin_options');
// define('SGW_BASE_URL', get_option('siteurl').'/wp-content/plugins/support-great-writers/');
// define('SGW_DEFAULT_IMAGE', get_option('siteurl').'/wp-content/plugins/support-great-writers/images/not_found.gif');
// define('SGW_POST_META_KEY','SGW_ASIN');
// define('SGW_ADMIN_PAGE','amazon_bookstore');
// define('SGW_ADMIN_PAGE_NONCE','sgw-save-options');
// define('SGW_PLUGIN_ERROR_CONTACT','Please contact <a href="mailto:wordpress@loudlever.com?subject=Amazon%20Bookstore%20Widget">wordpress@loudlever.com</a> if you have any questions');
// define('SGW_BESTSELLERS','0812974492,0316055441');
// define('SGW_PLUGIN_FILE',plugin_basename(__FILE__));
// 
// require_once(dirname(__FILE__) . '/include/classes/SGW_Widget.class.php');
// require_once(dirname(__FILE__) . '/include/classes/SGW_Admin.class.php');
// $sgw = new SGW_Admin;
// 
// // enable link to settings page
// add_filter($sgw->plugin_filter(), array(&$sgw,'plugin_link'), 10, 2 );
// 
// function RegisterAdminPage() {
//   global $sgw;
//   // ensure our js and style sheet only get loaded on our admin page
//   $page = add_options_page('Amazon Book Store', 'Amazon Book Store', 'manage_options', SGW_ADMIN_PAGE, 'AdminPage');
//   $sgw->help = $page;
//   add_action("admin_print_scripts-$page", 'AdminInit');
//   add_action("admin_print_styles-$page", 'AdminHeader' );
// }
// 
// function AdminHeader() {
// 
//   <link rel='stylesheet' href=' echo SGW_BASE_URL; css/sgw_style.css' type='text/css' />
// 
// }
// function AdminPage() {
//   global $sgw;
//   $sgw->configuration_screen();
//   // require_once('admin/admin.php');
// }
// function AdminInit() {
//   wp_enqueue_script('sgw', WP_PLUGIN_URL . '/support-great-writers/js/sgw.js'); 
// }
// function RegisterWidgetStyle() {
//  wp_enqueue_style( 'sgw_widget', SGW_BASE_URL . 'css/sgw_widget.css', array(), '1.2.2' );
// }
// 
// if (class_exists("SupportGreatWriters")) {
//   add_action('widgets_init', create_function('', 'return register_widget("SupportGreatWriters");'));
//   add_action('admin_menu', 'RegisterAdminPage'); 
//  add_action('wp_enqueue_scripts','RegisterWidgetStyle');
//  add_filter('contextual_help', array(&$sgw,'configuration_screen_help'), 10, 3);
// 
// }
// register_activation_hook( __FILE__, array(&$sgw,'activate_plugin'));
// register_deactivation_hook( __FILE__, array(&$sgw,'deactivate_plugin'));
// 
// 
// 

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

// Load the KindleFeed class and associated scoped functionality
load_template(dirname(__FILE__) . '/includes/SmartNavbar.class.php');
$snb = new SmartNavbar();

// enable our link to the settings
add_filter('plugin_action_links', array(&$snb,'plugin_links'), 10, 2 );
// Enable the Admin Menu and Contextual Help
add_action('admin_menu', 'smart_navbar_admin_settings');
add_filter('contextual_help', array(&$snb,'configuration_screen_help'), 10, 3);

function smart_navbar_admin_settings() {
  global $snb;
 	//create Options Management Screen
	if (function_exists('add_options_page')) {
	  $role = 'administrator'; // in future may want to lower to 'manage_options'
		$snb->help = add_options_page('Smart-Navbar Settings','Smart-Navbar', $role, $snb->slug, 'smart_navbar_settings_page');
  }
  //   if (function_exists('add_action')) {
  //    add_action( 'admin_init', array(&$snb,'register_options') );
  // }
}

// This callback does not handle class functions, thus we wrap it....
function smart_navbar_settings_page() {
  global $snb;
  $snb->configuration_screen();
}

register_activation_hook( __FILE__, array(&$snb,'activate_plugin'));
register_deactivation_hook( __FILE__, array(&$snb,'deactivate_plugin'));

?>
