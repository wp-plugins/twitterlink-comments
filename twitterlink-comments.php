<?php /* Twitterlink Comments
Plugin Name: Twitterlink Comments
Plugin URI: http://comluv.com/download/twitterlink-comments
Description: Plugin to show a link to follow the comment author on twitter if they have entered in their username at least once in the comment form
Version: 1.1
Author: Andy Bailey
Author URI: http://comluv.com/
Copyright (C) <2009>  <Andy Bailey>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Avoid name collision
if (! class_exists ( 'twitlink' )) {
	// let class begin
	class twitlink {
	    //localization domain
            var $plugin_domain = 'twitlink';
	    var $db_option = 'twitlink';
            // start plugin
                function twitlink() {
                    global $wp_version, $pagenow;
                    // pages where commentluv needs translation
                    $local_pages = array ('plugins.php', 'twitterlink-comments.php' );
		    // check if translation needed on current page
                    if (in_array ( $pagenow, $local_pages ) || in_array ( $_GET ['page'], $local_pages )) {
		    	$this->handle_load_domain ();
                    }
                    $exit_msg = __ ( 'Twitterlink requires Wordpress 2.8 or newer.', $this->plugin_domain ) . '<a href="http://codex.wordpress.org/Upgrading_Wordpress">' . __ ( 'Please Update!', $this->plugin_domain ) . '</a>';
                    // can you dig it?
                    if (version_compare ( $wp_version, "2.8", "<" )) {
                    	exit ( $exit_msg ); // no diggedy
                    }
                    // filters for checking and adding twitter link
                    add_filter ( 'comments_array', array (&$this, 'add_twitlink_to_comment_array' ), 10 ); // check db for each email in comments array and add twitter username to each comments array if one exists
                    // action for when comment gets spammed
                    add_action ( 'wp_set_comment_status',array(&$this,'remove_spam_value'),10,2);
                    
		    // choose which filter to use depending on user setting of position
		    $options = $this->get_options();
		    if($options['manual'] == 'no'){
			// choose filter if not using manual output
			if($options['position'] == "under_name"){
				// under name
				add_filter ( 'get_comment_author_link', array (&$this, 'add_twitlink_to_comment_author_link' ), 10 ); // adds the html twitter link to author link
			} else {
				// in comment text
				add_filter ( 'comment_text', array (&$this, 'add_twitlink_to_comment_text' ), 10); // adds twitter link to comment text
			}
		    }
                    // actions for adding extra field and processing submitted field and settings page
		    if($options['auto_add']){
			add_action ( 'comment_form', array (&$this, 'add_twitter_field' ) ); // add a field for twitter username
		    }
                    add_action ( 'preprocess_comment', array (&$this, 'add_twitter_field_value' ) ); // add username to db after comment is posted
                    add_action ( 'admin_menu', array (&$this, 'admin_menu' ) ); // add settings page
		    add_filter ( 'plugin_action_links', array (&$this, 'twitlink_action' ), - 10, 2 ); // add a settings page link to the plugin description. use 2 for allowed vars
                }
                // hook the options page
		function admin_menu() {
			add_options_page ( __('TwitterLink Settings',$this->plugin_domain), __('TwitterLink Settings',$this->plugin_domain), 8, basename ( __FILE__ ), array (&$this, 'handle_options' ) );
		}

		// hook plugin page
		function twitlink_action($links, $file) {
			$this_plugin = plugin_basename ( __FILE__ );
			if ($file == $this_plugin) {
				$links [] = "<a href='options-general.php?page=twitterlink-comments.php'>". __( 'Settings', $this->plugin_domain ) . "</a>";
			}
			return $links;
		}
                // get plugin options
		function get_options() {
			// default values
			$options = array (
                            'auto_add' => 1,
                            'div_class' => 'twitlink',
			    'input_class' => 'input',
			    'pre_html' => '<small>',
			    'field_description' => __('You can add a link to follow you on twitter if you put your username in this box.<br>Only needs to be added once (unless you change your username). No http or @',$this->plugin_domain),
			    'post_html' => '</small> Twitter <br/>',
			    'position' => 'under_name',
			    'nofollow' => 'yes',
			    'newwindow' => 'yes',
			    'manual' => 'no',
			    'pre_link_html' => '<br>Twitter: ',
			    'anchor_text' => '[username]',
			    'post_link_html' => '<br>',
                'link_class' => 'twitter-anywhere-user'
			    
                            );
			// get saved options unless reset button was pressed
			$saved = '';
			if (! isset ( $_POST ['reset'] )) {
				$saved = get_option ( $this->db_option );
			}
			// assign values
			if (! empty ( $saved )) {
				foreach ( $saved as $key => $option ) {
					$options [$key] = $option;
				}
			}
			// update the options if necessary
			if ($saved != $options) {
				update_option ( $this->db_option, $options );
			}
			// return the options
			return $options;
		}
                // handle saving and displaying options
		function handle_options() {
			$options = $this->get_options ();
			if (isset ( $_POST ['save'] )) {
				// initialize the error class
				$errors = new WP_Error ( );
				// check security
				check_admin_referer ( 'twitterlink-nonce' );
				$options = array ();
				// validate submitted options here
				$options['auto_add'] = $_POST['auto_add'];
				$div_class = $_POST['div_class'];
				$input_class = $_POST['input_class'];
                $link_class = $_POST['link_class'];
				// make sure class names are sanitary
				if(!preg_match('/^[a-z0-9\-]+$/i', $div_class) || !preg_match('/^[a-z0-9\-]+$/i', $input_class)|| !preg_match('/^[a-z0-9\-]+$/i', $link_class)){
					 $errors->add('not_alphanum', __('CSS Classes can only contain letters, numbers and hyphen'));
				} else {
					$options['div_class'] = $div_class;
					$options['input_class'] = $input_class;
                    $options['link_class'] = $link_class;
				}
				$pre_html = $_POST['pre_html'];
				$field_description = $_POST['field_description'];
				$post_html = $_POST['post_html'];
				$pre_link_html = $_POST['pre_link_html'];
				$post_link_html = $_POST['post_link_html'];
				// max size of html is 120 chars
				if(sizeof($pre_html) > 120 || sizeof($post_html) > 120 || sizeof($pre_link_html) > 120 || sizeof($post_link_html) > 120){
					$errors->add('too_much_html_chars',__('Only 120 characters allowed in html'));
				} else {
					// set array of allowed tags in html fields
					$allowedtags = array(
					'br' => array(),
					'a' => array('href' => array(),'title' => array(),'rel' => array(),'target'=>array()),
					'small' =>array(),
					'p' =>array( 'class'=>array()),
					'strong' => array(),
					'img' => array('src' => array(),'alt' => array(),'width' => array(),'height' => array(),'align'=> array())
					);
					// put html through kses filter with allowed tags (above) before saving
					$options['pre_html'] = wp_kses($_POST['pre_html'],$allowedtags);
					$options['post_html'] = wp_kses($_POST['post_html'],$allowedtags);
					$options['field_description'] = wp_kses($_POST['field_description'],$allowedtags);
					$options['pre_link_html'] = wp_kses($_POST['pre_link_html'],$allowedtags);
					$options['post_link_html'] = wp_kses($_POST['post_link_html'],$allowedtags);
					//show message if tags were stripped from html
					if($options['pre_html']!=$pre_html || $options['field_description'] != $field_description || $options['post_html'] != $post_html || $options['pre_link_html'] != $pre_link_html || $options['post_link_html'] != $post_link_html ){
						echo '<div class="attention fade"><p>'.__('Some HTML tags were stripped (not in list of allowed tags)',$this->plugin_domain).'</p></div>';
					}
				}
				$options['position'] = $_POST['position'];
				$options['nofollow'] = $_POST['nofollow'];
				$options['newwindow'] = $_POST['newwindow'];
				$options['manual'] = $_POST['manual'];
				$options['anchor_text'] = wp_filter_nohtml_kses($_POST['anchor_text']);
				
				// check for errors
				if (count ( $errors->errors ) > 0) {
					echo '<div class="error"><h3>';
					_e ( 'There were errors with your chosen settings', $this->plugin_domain );
					echo '</h3>';
					foreach ( $errors->get_error_messages () as $message ) {
						echo $message;
					}
					echo '</div>';
				} else {
					//every-ting cool mon
					update_option ( $this->db_option, $options );
					echo '<div class="updated fade"><p>'.__('Plugin settings saved.',$this->plugin_domain).'</p></div>';
				}
			}
						
			// url for form submit
			$action_url = $_SERVER ['REQUEST_URI'];
			include ('twitterlink-comments-manager.php');
		}
                // check each comment in comments_array for existing twitter username and add it if one exists
                function add_twitlink_to_comment_array($array){
                    global $wpdb;
                    $returnarray = array();
                    // step through each comment data array
                    foreach($array as $comment){
                        // get twitter username if exists for comment author email
                        $query = $wpdb->prepare("SELECT twitid FROM {$wpdb->prefix}wptwitipid WHERE email = %s",$comment->comment_author_email);
                        $twitid = $wpdb->get_var($query);
                        // add to array if twitid exists
                        if($twitid && !$comment->twitlinkid){
                            $comment->twitlinkid = $twitid;
                        }
                        // add new comment array to array of comments
                        $returnarray[] = $comment;
                    }
		    // send back the modified array of arrays
                    return $returnarray;
                }
                // check for twitlinkid in comment array and put it under author url link if it exists
                function add_twitlink_to_comment_author_link($link){
                    global $comment;
                    if($twitlinkid = $comment->twitlinkid){
                        $link = $link.$this->make_twitlink($twitlinkid);
                    }
                    return $link;
                }
		// check for twitlinkid in comment array and put it in the comment text
		function add_twitlink_to_comment_text($comment_text){
			global $comment;
			// check for twitter link in this comments array
			if($twitlinkid = $comment->twitlinkid){
				// construct twitter link
				$link = $this->make_twitlink($twitlinkid);
				$options = $this->get_options();
				if($options['position'] == 'start_comment'){
					// prepend link
					$comment_text = $link.$comment_text;
				} else {
					// append link
					$comment_text .= $link;
				}
			}
			// send back modified comment text
			return $comment_text;
		}
		// construct twitter link and return
		function make_twitlink($twitlinkid){
			$options = $this->get_options();
			$nofollow = $options['nofollow'] == 'yes'? ' rel="nofollow"' : '';
			$newwindow = $options['newwindow'] == 'yes' ? 'target="_blank"' : 'target="_parent"';
			$anchor = str_replace('[username]',$twitlinkid,$options['anchor_text']);
            $link_class = $options['link_class'];
			$link = $options['pre_link_html'].
			'<a class="'.$link_class.'" href="http://twitter.com/'.$twitlinkid.'"'.$nofollow.' '.$newwindow.'>'.
			$anchor.'</a>'.
			$options['post_link_html'];
			return $link;
		}
		
                // add twitter field to comment form
                function add_twitter_field (){
			$options = $this->get_options();
                    // check if user set manually inserted field in comments.php and add if not 
		    if($options['auto_add']){
			echo '<div class="'.$options['div_class'].'">'.$options['pre_html'].$options['field_description'];
			echo '<input id="atf_twitter_id" type="text" name="atf_twitter_id" class="'.$options['field_class'].'"/>'.$options['post_html'].'</div>';
			if($options['div_class'] == 'twitlink'){
				// add style if using default class
				echo '<style>.twitlink {border: 1px solid #d1d1d1; background-color: lightBlue; padding: 5px; margin: 5px;}</style>';
			}
		    } 
                }
                // store twitter username in db if supplied
                function add_twitter_field_value($comment_data) {
                    $twitter=$_POST['atf_twitter_id'];
                    // access db
                    global $wpdb;
                    if($twitter){
                        // chop off any @ symbol or http twitter address
                        $find=array("@","http://twitter.com/");
                        $replace=array("");
                        $twitterid = str_replace($find,$replace,$twitter);
                        // store twitter id wptiwitpid
                        $query = $wpdb->prepare("SELECT twitid FROM {$wpdb->prefix}wptwitipid WHERE email = %s",$comment_data['comment_author_email']);
                        $exists = $wpdb->get_var($query);
                        if(!$exists){
                            // none yet, insert
                            $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wptwitipid (email,twitid) VALUES (%s,%s)",$comment_data['comment_author_email'],$twitterid);
                            $wpdb->query($query);
                            return $comment_data;
                        } 
                        if ($twitterid != $exists){
                            //exists but is different so update to new value
                            $query = $wpdb->prepare("UPDATE {$wpdb->prefix}wptwitipid SET twitid = %s WHERE email = %s",$twitterid,$comment_data['comment_author_email']);
                            $wpdb->query($query);
                        }
                    }
                    return $comment_data;
                }
                // install function
                function install () {
                    // add db table if it doesn't exist (backwards compatible with wp-twitip-id plugin)
                    global $wpdb;
                    $table_name = $wpdb->prefix . "wptwitipid";
                    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                          $sql = "CREATE TABLE " . $table_name . " (
                          id mediumint(9) NOT NULL AUTO_INCREMENT,
                          email varchar(120) NOT NULL,
                          twitid varchar(120) NOT NULL,
                          PRIMARY KEY  (id),
                          UNIQUE KEY (email)
                          );";
                          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                          dbDelta($sql);   
                    }
                }
                // Localization support
		function handle_load_domain() {
			// get current language
			$locale = get_locale ();
			// locate translation file
			$mofile = WP_PLUGIN_DIR . '/' . plugin_basename ( dirname ( __FILE__ ) ) . '/lang/' . $this->plugin_domain . '-' . $locale . '.mo';
			// load translation
			load_textdomain ( $this->plugin_domain, $mofile );
		}
        
        // remove spam value
        function remove_spam_value($id,$status){
            
            if($status == 'spam'){
                $tempcomment = get_comment($id);
                $email = $tempcomment->comment_author_email;
                global $wpdb;
               $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}wptwitipid WHERE email = %s",$email);
               $result = $wpdb->query($query); 
            }
        }
		 
        }
}
// start twitter-link class engines
if (class_exists ( 'twitlink' )) :
$twitlink = new twitlink ( );

// confirm warp capability
if (isset ( $twitlink )) {
	// engage
	register_activation_hook ( __FILE__, array (&$twitlink, 'install' ) );
}


endif;

// add slashes to html if magic quotes is not on
function twitlink_slashit($stringvar){
	if (!get_magic_quotes_gpc()){
		$stringvar = addslashes($stringvar);
	}
	return $stringvar;
} 
// remove slashes if magic quotes is on
function twitlink_deslashit($stringvar){
    if (1 == get_magic_quotes_gpc()){
        $stringvar = stripslashes($stringvar);
    }
    return $stringvar;
}
?>