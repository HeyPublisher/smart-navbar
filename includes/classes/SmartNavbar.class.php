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
  var $donate_link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Y8SL68GN5J2PL';
  var $page_shortcode = '__SMARTNAVBAR_FAVORITES_LIST_GOES_HERE__';
  var $page_permalink = null;
  
  
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
    $this->delete_bookmarks_page();
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
      // $opts = get_option(SNB_PLUGIN_OPTTIONS);
      // $posts = $this->get_post_meta();
      // $existing = array();
      
      if ($message) {
        printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
      } elseif ($this->error) { // reload the form post in this form
        // stuff here if we have an error
      }
?>
      <style type='text/css'>
        a.snb_PayPal {
          background-image:url(<?php echo SNB_BASE_URL; ?>includes/images/paypal.png);
        }
        a.snb_Home {
          background-image:url(<?php echo SNB_BASE_URL; ?>includes/images/home.png);
        }
        a.snb_Suggestion {
          background-image:url(<?php echo SNB_BASE_URL; ?>includes/images/suggestion.png);
        }
        a.snb_Contact {
          background-image:url(<?php echo SNB_BASE_URL; ?>includes/images/contact.png);
        }
        a.snb_More {
          background-image:url(<?php echo SNB_BASE_URL; ?>includes/images/more.png);
        }
      </style>
      <div class="wrap">
        <h2>Amazon Book Store Widget</h2>
<?php
        if (!$message) {
?>
          <div class="updated">
    				<p><strong>Thanks for using this plugin! If it works for you, <a href='<?php echo $this->donate_link; ?>' target='_blank'>please donate!</a> Donations help keep this plugin free for everyone to use.</strong></p>
          </div>
<?php
        }
?>
        <div id="poststuff" class="metabox-holder has-right-sidebar">
          <!-- Right Side -->
  				<div class="inner-sidebar">
    					<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
<?php 
                  $this->html_box_header('snb_about',__('About this Plugin',$this->i18n),true);
                  // side bar elems
                  $this->sidebar_link('PayPal',$this->donate_link,'Donate with PayPal'); 
                  $this->sidebar_link('Home','https://wordpress.org/plugins//','Plugin Homepage'); 
                  $this->sidebar_link('Suggestion','https://wordpress.org/support/plugin/','Suggestions'); 
                  $this->sidebar_link('Contact','mailto:wordpress@loudlever.com','Contact Us'); 
                  $this->sidebar_link('More','https://wordpress.org/plugins/search.php?q=loudlever','More Plugins by Us'); 
              	  $this->html_box_footer(true); 
?>  
              </div>
            </div>
            <!-- Left Side -->
            <div class="has-sidebar sm-padded">
    					<div id="post-body-content" class="has-sidebar-content">
    						<div class="meta-box-sortabless">
                  <form method="post" action="admin.php?page=<?php echo SNB_ADMIN_PAGE; ?>">
                    <?php
                      if(function_exists('wp_nonce_field')){ wp_nonce_field(SNB_ADMIN_PAGE_NONCE); }
                    ?>   
                    <!-- Default Settings -->
                    <?php $this->html_box_header('snb_default_asins',__('Settings',$this->i18n),true); ?>
      						  <p>Configure Below</p>

                      <p>
                        <label class='snb_label' for='snb_affiliate_id'>Affiliate ID:</label>
                        <input type="text" name="snb_opt[affiliate_id]" id="affiliate_id" class='snb_input' value="<?php echo  $opts['affiliate_id']; ?>" />
                      </p>
                      <p>
                        <label class='snb_label' for='country_id'>Affiliate Country:</label>
                        <select name="snb_opt[country_id]" id="country_id" class='snb_input'>
                          <?php
                            $countries = $this->supported_countries();
                            foreach ($countries as $key=>$val) {
                              $sel = '';
                              if ($opts['country_id']==$key) { $sel = 'selected="selected"'; }
                              printf("<option value='%s' %s>%s</option>",$key,$sel,$val);
                            }
                          ?>          
                        </select>
                      </p>

                      <p>
                        <label class='snb_label' for='snb_default_asins'>Default ASINs:</label>
                        <input type="text" name="snb_opt[default]" id="snb_default" class='snb_input' value="<?php echo  $opts['default']; ?>" />
                        <input type="hidden" name="save_settings" value="1" />
                      </p>
                    <?php $this->html_box_footer(true); ?>  
                    <?php $this->html_box_header('snb_post_asins',__('POST-specific ASINs',$this->i18n),true); ?>
                      <p>Stuff here <code><?php echo $this->post_meta_key; ?></code>.</p>
                  	<?php $this->html_box_footer(true); ?>  
                    <input type="submit" class="button-primary" name="save_button" value="<?php _e('Update Settings', $this->i18n); ?>" />
      	          </form>
                </div>
              </div>
            </div>
        </div>
      </div>
      <?php

      }
  }
	/* Contextual Help for the Plugin Configuration Screen */
  public function configuration_screen_help($contextual_help, $screen_id, $screen) {
    if ($screen_id == $this->help) {
      $contextual_help = <<<EOF
<h2>Overview</h2>      
<p>Stuff here
</p>

<h2>Settings</h2>
<p>Stuff here</p>
<p>More stuff and <code>CODE_HERE</code>.</p>
EOF;
    }
  	return $contextual_help;
  }
  public function header_bar( &$wp_query) {
    global $wp_the_query,$post;
    if ( ( $wp_query === $wp_the_query ) && !is_admin() && !is_feed() && !is_robots() && !is_trackback() && !is_home() && !is_page())  {
      $this->log(sprintf("post => %s",print_r($post,1)));
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
            <a href="{$this->page_permalink}"><i id='snb-bars' class='fa fa-bars fa-lg' title='Settings'></i></a>
            <i id='snb-heart' class='fa {$heart} fa-lg' title='Add to Favorites' data-id='{$post->ID}'></i>
            <i id='snb-bookmark' class='fa {$bookmark} fa-lg' title='Add to Bookmarks' data-id='{$post->ID}'></i>
            <!--<i id='snb-share-square' class='fa fa-share-square-o fa-lg' title='Share with Friends'></i>-->
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
	public function html_box_header($id, $title) {
    $text = <<<EOF
  		<div id="{$id}" class="postbox">
  			<h3 class="hndle"><span>{$title}</span></h3>
  			<div class="inside">
EOF;
    echo $text;
	}
	public function html_box_footer() {
    $text = <<<EOF
				</div>
			</div>
EOF;
    echo $text;
	}
  // No attrs at this stage
	public function parse_shortcode($atrs) {
    $actor = $this->read_cookies();
    $data = $this->get_all_user_settings($actor);
    // need to loop over this and add stuff in hashes
    $bookmark = array();
    $favorites = array();
    foreach($data as $obj) {
      if ($obj->bookmark > 0) { $bookmark[$obj->post_title] = $obj; }
      if ($obj->heart > 0) { $favorites[$obj->post_title] = $obj; }
    }
    $this->log(sprintf("BOOKS: %s\nHears = %s",print_r($bookmarks,1),print_r($favorites,1)));
    
    
	  $book_html = "<h2>Bookmarks</h2>";
	  $fav_html = "<h2>Favorites</h2>";
	  return "This is the page youw ant!!";
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
  public function register_admin_page() {
    // ensure our js and style sheet only get loaded on our admin page
    $this->help = add_options_page('Smart-Navbar Settings','Smart-Navbar', 'administrator', $this->slug, array(&$this,'admin_core'));
    add_action("admin_print_scripts-". $this->help, array(&$this,'admin_js'));
    add_action("admin_print_styles-". $this->help, array(&$this,'admin_stylesheet') );
  }
  
  public function sidebar_link($key,$link,$text) {
    printf('<a class="snb_button snb_%s" href="%s" target="_blank">%s</a>',$key,$link,__($text,$this->i18n));
  }
  
  public function write_cookies() {
    $cookie = $this->read_cookies();
    // $this->log("existing cookie in write_cookies : $cookie");
    if (!$cookie) { $cookie = $this->uuid(); }
    setcookie($this->cookie_name, $cookie, time()+(365 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, false); // 1 year cookie
    // This can NOT be done in the constructor - and this is the soonest we can get that loaded
    $this->log(sprintf("PLUGIN OPTS = %s\n POST ID = {$this->options[post_id]}",print_r($this->options,1)));
    $this->page_permalink = get_permalink($this->options[post_id]);
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
    global $wpdb, $post;
    if ($actor) {
      $table = $this->get_user_table_name();
      $sql = $wpdb->prepare("SELECT * FROM $table WHERE user = %s and post_ID = %d",$actor,$post->ID);
      $this->log(sprintf("SQL = %s",print_r($sql,1)));
      $row = $wpdb->get_row($sql);
      $this->log(sprintf("ROW = %s",print_r($row,1)));
      return $row;
    }
    return null;
  }
  private function get_all_user_settings($actor) {
    global $wpdb;
    if ($actor) {
      $table = $this->get_user_table_name();
      $sql = $wpdb->prepare("
        SELECT u.post_ID, u.bookmark,u.heart,p.post_title  
        FROM $table u
        INNER JOIN $wpdb->posts p
        ON u.post_ID = p.ID
        WHERE u.user = %s",$actor
      );
      $this->log(sprintf("SQL = %s",print_r($sql,1)));
      $rows = $wpdb->get_results($sql);
      $this->log(sprintf("ROWS = %s",print_r($rows,1)));
      return $rows;
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
      ),
      'post_id' => null
    );
    return;
  }

  // http://codex.wordpress.org/Options_API
  private function init_plugin() {
    $this->init_install_options();
    $this->init_db();
    $post_id = $this->create_bookmarks_page();
    
    $this->options[plugin][version_last] = SNB_PLUGIN_VERSION;
    $this->options[plugin][version_current] = SNB_PLUGIN_VERSION;
    $this->options[plugin][install_date] = Date('Y-m-d');
    $this->options[plugin][upgrade_date] = Date('Y-m-d');
    $this->options[plugin][db_version] = $this->db_version;
    $this->options[post_id] = $post_id; // This is the page that display's users bookmarks and favorites
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
      post_ID bigint(20) unsigned NOT NULL,
      bookmark tinyint DEFAULT 0,
      heart tinyint DEFAULT 0,
      created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY user_post_id (user,post_ID)
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
      $sql = $wpdb->prepare("SELECT id FROM $table WHERE user = %s AND post_ID = %d",$data['actor'],$data['post_ID']);
      // $this->log(sprintf("SQL = %s",print_r($sql,1)));
      $id = $wpdb->get_var($sql);
      // $this->log(sprintf("ID = %s",print_r($id,1)));
      if (!$id) {
        $wpdb->insert( 
          $table,
          array('user'=>$data['actor'],'post_ID'=>$data['post_ID'],$data['item']=>$bool,'created_at'=>$time,'updated_at'=>$time),
          array( '%s', '%d', '%d', '%s', '%s' )
        );
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

  public function admin_core() {
    $this->configuration_screen();
  }
  public function admin_js() {
    // $this->log("in the admin_js()");
    // wp_enqueue_script('sgw', WP_PLUGIN_URL . '/support-great-writers/js/sgw.js'); 
  }
  public function admin_stylesheet() {
    $this->log("in the admin_stylesheet()");
    printf("<link rel='stylesheet' href='%sincludes/css/smart_nav_admin.css' type='text/css' />",SNB_BASE_URL); 
  }

  // Create the 'PAGE' in WordPress where a user's favorites and bookmarks are displayed.
  private function create_bookmarks_page() {
    global $current_user;
    $title = 'Bookmarks and Favorites';
    $content = sprintf('[%s]',$this->page_shortcode);

    // Create the page
    $post = array (
      "post_content"   => $content,
      "post_title"     => $title,
      "post_author"    => $current_user->ID,
      "post_status"    => 'publish',
      "post_type"      => "page"
    );
    $post_ID = wp_insert_post($post);
    return $post_ID;
  }
  private function delete_bookmarks_page() {
    $post_id = intval($this->options[post_id]);
    if ($post_id > 0) {
      wp_delete_post($post_id);
    }
    return;
  }


}
?>