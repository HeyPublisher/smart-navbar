<?php
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
  
  public function plugin_links($links) {
    $url = $this->settings_url();
    $settings = '<a href="'. $url . '">'.__("Settings", $this->i18n).'</a>';
    array_unshift($links, $settings);  // push to left side
    return $links;
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