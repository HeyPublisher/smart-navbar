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
  var $cookie_name = '_snb_user';
  var $cookie_val = null;
  var $db_version = 1; // update only when changing db schema
  var $db_table_name = null;
  
  public function __construct() {
	  // initialize the dates
	  $this->options = get_option($this->opt_key);
  }   

  public function __destruct() {

  }
  public function activate_plugin() {
    // no logging here -it barfs on activation
    $this->log("in the activate_plugin()");
    $this->check_plugin_version();
  }
  public function check_plugin_version() {
    $this->log("in check_plugin_version()");
    
    $opts = get_option($this->opt_key);
    // printf("<pre>In check_plugin_version()\n opts = %s</pre>",print_r($opts,1));
    if (!$opts || !$opts[plugin] || $opts[plugin][version_last] == false) {
      $this->log("no old version - initializing");
      $this->init_plugin();
      return;
    }
    // check for upgrade option here
    if ($opts[plugin][version_current] != SNB_PLUGIN_VERSION) {
      $this->log("need to upgrade version");
      $this->upgrade_plugin($opts);
      return;
    }
  }
  
  public function deactivate_plugin() {
    $this->options = false;
    delete_option($this->opt_key);  // remove the options from db
    $this->delete_db();
	  return;
  }
  public function ajax_handler() {
    global $wpdb; // this is how you get access to the database
    $this->log("in ajax handler");
    $what = $_POST;
    $what['actor'] = $this->read_cookies();
    $results = $this->update_user_setting($what);
    $this->log(sprintf("POST params = %s\n RESULTS: %s",print_r($what,1),$results));
    if ($results) {
      echo 'OK';
      wp_die(); // this is required to terminate immediately and return a proper response    
    } else {
      echo 'Unable to save';
      wp_die('Error','Unable to save change',400); 
    }
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
    global $wp_the_query,$post;
    if ( ( $wp_query === $wp_the_query ) && !is_admin() && !is_feed() && !is_robots() && !is_trackback() && !is_home()) {
      // $this->log(sprintf("post => %s",print_r($post,1)));
      $author = get_the_author_meta('display_name',$post->post_author);
      $author_link = sprintf("<a href='%s'>%s</a>",get_author_posts_url($post->post_author),$author );
      $category = get_the_category_list(',', '', $post->ID);
      $img = SNB_BASE_URL . 'includes/images/';
      $is_admin = '';
      if (is_admin_bar_showing()) { $is_admin = 'class="with-admin"';}
      $actor = $this->read_cookies();
      $data = $this->get_user_setting($actor);
      $this->log(sprintf("ROW DATA: %s",print_r($data,1)));
      $heart = $data->heart > 0 ? 'fa-heart' : 'fa-heart-o';
      $bookmark = $data->bookmark > 0 ? 'fa-bookmark' : 'fa-bookmark-o';
      
      
      // TODO: Get navigation into bar
      $text = <<<EOF
        <div id="smart-navbar" {$is_admin}>
          <div id="smart-navbar-left">
            <!--<i id='snb-arrow-circle' class='fa fa-arrow-circle-left fa-lg' title='Previous'></i>-->
            <i id='snb-heart' class='fa {$heart} fa-lg' title='Add to Favorites'></i>
            <i id='snb-bookmark' class='fa {$bookmark} fa-lg' title='Add to Bookmarks'></i>
            <!--<i id='snb-share-square' class='fa fa-share-square-o fa-lg' title='Share with Friends'></i>-->
            <i id='snb-share-square' class='fa fa-question-circle fa-lg' title='What is This?'></i>
          </div>
          
          <div id="smart-navbar-right">
            <!--<i class='fa fa-arrow-circle-right fa-lg' title='Next'></i>-->
          </div>
          <div id="smart-navbar-center">
            <h3 class='entry-title'>{$post->post_title}</h3>
            <div class='author'>by {$author_link}</div>
            <div class='categories-links'>posted in {$category}</div>
          </div>
        </div>
EOF;
      echo $text;
    }
  }
  
  public function plugin_links($links) {
    $url = $this->settings_url();
    $settings = '<a href="'. $url . '">'.__("Settings", $this->i18n).'</a>';
    array_unshift($links, $settings);  // push to left side
    return $links;
  }
  // load custom style and js
  public function plugin_init() {
    wp_enqueue_style( 'snb_font_css', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), SNB_PLUGIN_VERSION );
    wp_enqueue_style( 'snb_core_css', SNB_BASE_URL . 'includes/css/smart_nav.css', array(), SNB_PLUGIN_VERSION );
    wp_enqueue_script('snb_core_js', SNB_BASE_URL . 'includes/js/smart-nav.js', array('jquery'), SNB_PLUGIN_VERSION );
    wp_localize_script('snb_core_js', 'ajax_object',array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
  }
  public function read_cookies() {
    $cookie = isset( $_COOKIE[$this->cookie_name] ) ? $_COOKIE[$this->cookie_name] : null;
    if ($cookie) { $this->cookie_val = $cookie; }
    // $this->log("cookie = $this->cookie_val");
    return $this->cookie_val;
  }
  public function write_cookies() {
    $cookie = $this->read_cookies();
    // $this->log("existing cookie in write_cookies : $cookie");
    if (!$cookie) { $cookie = $this->uuid(); }
    setcookie($this->cookie_name, $cookie, time()+(365 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, false); // 1 year cookie
  }
  /*
    PRIVATE FUNCTIONS
  */
  private function delete_db() {
    global $wpdb;
    $table = $this->get_user_table_name();
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" ); 
    return;
  }
  private function get_user_setting($actor) {
    global $wpdb;
    if ($actor) {
      $table = $this->get_user_table_name();
      $sql = $wpdb->prepare("SELECT * FROM $table WHERE user = %s",$actor);
      // $this->log(sprintf("SQL = %s",print_r($sql,1)));
      $row = $wpdb->get_row($sql);
      $this->log(sprintf("ROW = %s",print_r($row,1)));
      return $row;
    }
    return null;
  }
  
  private function get_user_table_name() {
    global $wpdb;
    if (!$this->db_table_name) {
      $this->db_table_name = $wpdb->prefix . "snb_user_data";
    }
    return $this->db_table_name;
  }
  private function init_install_options() {
    $this->options = array(
      'plugin' => array(
        'current_plugin_version'    => null,
        'db_version'                => null,
        'last_plugin_version_last'  => null,
        'install_date'    => null,
        'upgrade_date'    => null
      )
    );
    return;
  }

  // http://codex.wordpress.org/Options_API
  private function init_plugin() {
    $this->init_install_options();
    $this->init_db();
    $this->options[plugin][version_last] = SNB_PLUGIN_VERSION;
    $this->options[plugin][version_current] = SNB_PLUGIN_VERSION;
    $this->options[plugin][install_date] = Date('Y-m-d');
    $this->options[plugin][upgrade_date] = Date('Y-m-d');
    $this->options[plugin][db_version] = $this->db_version;
    add_option($this->opt_key,$this->options);
    return;
  }

  // http://codex.wordpress.org/Creating_Tables_with_Plugins
  private function init_db() {
    global $wpdb;
    $this->delete_db();  // covers an edge case where plugin is manually removed but table is not
    $table = $this->get_user_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      user varchar(32) NOT NULL,
      bookmark tinyint DEFAULT 0,
      heart tinyint DEFAULT 0,
      created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY user (user)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
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
  // http://codex.wordpress.org/Creating_Tables_with_Plugins
  private function upgrade_plugin($opts) {
    // No updates yet.
    // $ver = $this->get_version_as_int($this->options[plugin][version_current]);
    // $this->log("Version = $ver");
    // // printf("<pre>In upgrade_plugin()\n ver = %s\nopts = %s</pre>",print_r($ver,1),print_r($this->options,1));
    // if ($ver < 210) {
    //   $url = $this->plugin_admin_url();
    //   // need to show the mesage about id changing 
    //   // $html = '<div class="updated"><p>';
    //   // $html .= __( 'You will need to update your Amazon Associate ID <a href="'.$url.'">on the Settings page</a>.', 'sgw' );
    //   // echo $html;
    // }
    // $this->options[plugin][version_last] = $this->options[plugin][version_current];
    // $this->options[plugin][version_current] = SGW_PLUGIN_VERSION;
    // $this->options[plugin][upgrade_date] = Date('Y-m-d');
    // update_option($this->opt_key,$this->options);
  }

  // Update the user's action in the database.
  // http://codex.wordpress.org/Class_Reference/wpdb 
  private function update_user_setting($data) {
    global $wpdb;
    // $data should have 3 vals, like : actor:_snb54f64574f28af2.62829307, item:bookmark, state:off
    if ($data['actor'] && $data['item'] && $data['state']) {
      $table = $this->get_user_table_name();
      $bool = $data['state'] == 'on' ? 1 : 0;
      $time = Date('Y-m-d H:i:s');
      $sql = $wpdb->prepare("SELECT id FROM $table WHERE user = %s",$data['actor']);
      // $this->log(sprintf("SQL = %s",print_r($sql,1)));
      $id = $wpdb->get_var($sql);
      // $this->log(sprintf("ID = %s",print_r($id,1)));
      if (!$id) {
        $wpdb->insert( $table, array('user'=>$data['actor'],$data['item']=>$bool,'created_at'=>$time,'updated_at'=>$time),array( '%s', '%d', '%s', '%s' ));
      } else {
        $wpdb->update( $table, array($data['item']=>$bool,'updated_at'=>$time),array('id'=>$id),array('%d','%s'));
      }
      return true;
    }
    return false;
  }
  private function uuid() {
   return uniqid('_snb',true);
  }

}
?>