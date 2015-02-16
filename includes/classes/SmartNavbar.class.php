<?php
// Thanks to https://wordpress.org/plugins/custom-headers-and-footers/


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Smart-Navbar: Illegal Page Call!'); }

/**
* SmartNavbar class for turning POST titles into intuitive nav-bar
*
*/
class SmartNavbar {

  var $debug = true;
  var $help = false;
  var $i18n = 'smart-navbar';             // key for internationalization stubs
  var $opt_key = '_snb_plugin_options';   // options key
  var $options = array();
  var $plugin_file = 'smart-navbar/smart-navbar.php';  // this helps us with the plugin_links
  var $slug = 'smart-navbar';
  
  public function __construct() {
	  // initialize the dates
	  $this->options = get_option($this->opt_key);
  }   

  public function __destruct() {

  }
  public function activate_plugin() {
    // no logging here -it barfs on activation
    $this->log("in the activate_plugin()");
    // $this->check_plugin_version();
  }
  public function deactivate_plugin() {
    $this->options = false;
    delete_option($this->opt_key);  // remove the options from db
	  return;
  }
  public function configuration_screen() {
    if (is_user_logged_in() && is_admin() ){
      // $message = $this->update_options($_POST);
      // $opts = get_option(SGW_PLUGIN_OPTTIONS);
      // $posts = $this->get_post_meta();
      // $existing = array();
      
      if ($message) {
        printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
      } elseif ($this->error) { // reload the form post in this form
        // stuff here if we have an error
      }
      // the rest of the admin screen here...
      print("<h2>This is where the form goes</h2>");
    }
  }
	/* Contextual Help for the Plugin Configuration Screen */
  public function configuration_screen_help($contextual_help, $screen_id, $screen) {
    if ($screen_id == $this->help) {
      $contextual_help = <<<EOF
<h2>Smart Navbar</h2>      
<p>Here's where we put the help.</p>
EOF;
    }
  	return $contextual_help;
  }
  public function header_bar( &$wp_query) {
    global $wp_the_query;
    if ( ( $wp_query === $wp_the_query ) && !is_admin() && !is_feed() && !is_robots() && !is_trackback() ) {
      $text = '<div id="smart-navbar">This is the smart nav bar</div>' ;
      echo $text;
    }
  }
  
  public function plugin_links($links) {
    $url = $this->settings_url();
    $settings = '<a href="'. $url . '">'.__("Settings", $this->i18n).'</a>';
    array_unshift($links, $settings);  // push to left side
    return $links;
  }
  public function plugin_style() {
    wp_enqueue_style( 'snb_style', SNB_BASE_URL . 'includes/css/smart_nav.css', array(), SNB_PLUGIN_VERSION );
  }
  private function settings_url() {
    $url = 'options-general.php?page='.$this->slug;
    return $url;
  }
  // Private functions
  private function log($msg) {
    if ($this->debug) {
      error_log(sprintf("%s\n",$msg),3,dirname(__FILE__) . '/../../error.log');
    }
  }

}
?>