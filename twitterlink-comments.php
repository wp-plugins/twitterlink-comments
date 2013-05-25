<?php /* twitlink
    Plugin Name: Twitterlink Comments
    Plugin URI: http://comluv.com/
    Description: Plugin to show a link to follow the comment author on twitter if they have entered in their username at least once in the comment form
    Version: 1.30
    Author: Andy Bailey
    Author URI: http://comluv.com
    Copyright (C) <2011>  <Andy Bailey>

    // uses andy bailey skeleton framework for wp3.2 compatible plugins v0.8
    last updated 08 March 2013

    */

    if (! class_exists ( 'twitlink' )) {
        // let class begin
        class twitlink {
            // vars 

            /****************** stop editing ********************/
            //localization domain
            var $plugin_domain = 'twitlink';
            var $plugin_url;
            var $plugin_dir;
            var $includes_dir;
            var $image_url;
            var $db_option = 'twitlink';
            var $version = '1.30';
            var $slug = 'twitlink-settings';
            var $hook;



            /** twitlink
            * This is the constructor, it runs as soon as the class is created
            * Use this to set up hooks, filters, menus and language config
            */
            function __construct() {
                global $wp_version, $pagenow;
                // pages where this plugin needs translation  (change if using custom menu)
                $local_pages = array ('plugins.php', 'options-general.php' );
                // check if translation needed on current page
                if (in_array ( $pagenow, $local_pages ) || in_array ( $_GET ['page'], $local_pages )) {
                    $this->handle_load_domain ();
                }
                $exit_msg = __( 'Twitterlink requires Wordpress 3.0 or newer.', $this->plugin_domain ) . '<a href="http://codex.wordpress.org/Upgrading_Wordpress">' . __ ( 'Please Update!', $this->plugin_domain ) . '</a>';
                // can you dig it?
                if (version_compare ( $wp_version, "3.0", "<" )) {
                    echo ( $exit_msg ); // no diggedy
                }
                // plugin dir and url
                $this->plugin_url = trailingslashit ( WP_PLUGIN_URL . '/' . dirname ( plugin_basename ( __FILE__ ) ) );
                $this->image_url = $this->plugin_url.'images/';
                $this->plugin_dir = dirname(__FILE__);
                $this->include_dir = $this->plugin_dir.'/include';
                // get options for conditional filters/actions
                $options = $this->get_options();
                // hooks 
                if(defined('DOING_AJAX') && DOING_AJAX){
                    // set up any ajax actioins here
                    add_action ( 'wp_ajax_ab_subscribe', array(&$this,'ab_subscribe')); // ajax email subscribe handler
                } else {
                    if(is_admin()){
                        // admin hooks 
                        add_action ( 'admin_init', array (&$this, 'admin_init' ) ); // to register settings group
                        add_action ( 'admin_menu', array (&$this, 'admin_menu' ) ); // to setup menu link for settings page 
                        // admin filters
                        add_filter ( 'screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
                        add_filter ( 'plugin_action_links', array (&$this, 'plugin_action_link' ), - 10, 2 ); // add a settings page link to the plugin description. use 2
                    } else {
                        // public hooks
                        add_action ( 'init', array (&$this,'init') ); // to register styles and scripts
                        // actions for adding extra field and processing submitted field and settings page
                        add_action('template_redirect', array (&$this,'setup_comment_form')); // set up comment form
                    }
                    // action for when comment gets spammed
                    add_action ( 'wp_set_comment_status',array(&$this,'remove_spam_value'),10,2);
                    $options = $this->get_options();

                    // choose filter if not using manual output
                    if($options['position'] == "under_name"){
                        // under name
                        add_filter ( 'get_comment_author_link', array (&$this, 'add_twitlink_to_comment_author_link' ), 10 ); // adds the html twitter link to author link
                    } else {
                        // in comment text
                        add_filter ( 'comment_text', array (&$this, 'add_twitlink_to_comment_text' ), 10); // adds twitter link to comment text
                    }

                    add_action ( 'preprocess_comment', array (&$this, 'add_twitter_field_value' ) ); // add username to db after comment is posted
                }

            }
            /**
            * PHP4 constructor
            */
            function twitlink() {
                $this->__construct();
            }

            /**************************************************************
            * admin functions
            *************************************************************/

            /** install
            * This function is called when the plugin activation hook is fired when
            * the plugin is first activated or when it is auto updated via admin.
            * use it to make any changes needed for updated version or to add/check
            * new database tables on first install.
            */
            function install(){
                $options = $this->get_options();
                $isnew = false;
                if(!$installed_version = get_option('twitlink_version')){
                    // no cl_version saved yet, set it to start of version 3
                    $installed_version = '2.30';
                    $isnew = true;
                }
                if(!$isnew){
                    // new recode version from 1.27 to 1.30
                    if(version_compare($this->php_version($installed_version),'1.2.7','>') && version_compare($this->php_version($installed_version),'1.3.0','<')){
                        // use new default values
                        $options = $this->get_options(true);
                    }

                    // update options (do this at the end so all the updates can happen first)
                    update_option($this->db_option,$options);  
                }
                // update twitlink_version in db
                if($this->version != $installed_version){
                    update_option('twitlink_version',$this->version);
                }
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
            /** handle_load_domain
            * This function loads the localization files required for translations
            * It expects there to be a folder called /lang/ in the plugin directory
            * that has all the .mo files
            */
            function handle_load_domain() {
                // get current language
                $locale = get_locale ();
                // locate translation file
                $mofile = WP_PLUGIN_DIR . '/' . plugin_basename ( dirname ( __FILE__ ) ) . '/lang/' . $this->plugin_domain . '-' . $locale . '.mo';
                // load translation
                load_textdomain ( $this->plugin_domain, $mofile );
            }

            /** get_options
            * This function sets default options and handles a reset to default options
            * return array
            */
            function get_options($reset = false) {
                // default values
                $default = array (
                    'field_method' => 'after_fields', 'form_type' => 'wp3', 'input_label' => 'twitter (@username)','input_label_position' => 'before','div_class' => 'twitlink', 'input_class' => 'comment-form-url','input_field_class'=>'input','pre_html' => '<small>',
                    'field_description' => __('You can add a link to follow you on twitter if you put your username in this box.<br />Only needs to be added once (unless you change your username). No http or @',$this->plugin_domain),
                    'post_html' => '</small> Twitter <br/>','position' => 'under_name','dofollow' => 'off', 'newwindow' => 'on', 'clickable' => 'yes',
                    'pre_link_html' => '<br />Twitter: ', 'anchor_text' => '[username]','post_link_html' => '<br />',
                    'link_class' => 'twitter-anywhere-user','profile_field' => 'twitter' ,'field_label' => '@twitter username (no @ or link)',
                    'del_options'=> 'on','del_table'=>'off'
                );
                $options = get_option($this->db_option,$default);
                if($reset){
                    return $default;
                } else {
                    return $options;
                }
            }

            /** init
            * This function registers styles and scripts
            */
            function init(){
                wp_register_style( 'twitlink_style',$this->plugin_url.'css/style.css',null,$this->version );                           
            }
            /** admin_init
            * This function registers the settings group
            * it is called by add_action admin_init
            * options in the options page will need to be named using $this->db_option[option]
            */
            function admin_init(){
                // whitelist options
                register_setting( 'twitlink_options_group', $this->db_option ,array(&$this,'options_sanitize' ) );
                // admin scripts
                wp_register_script( 'twitlink_script', $this->plugin_url.'js/script.js',array('jquery'),$this->version );
                wp_register_script( 'twitlink_fancybox', $this->plugin_url.'js/jquery.fancybox.js',array('jquery'),$this->version );
                //wp_register_script( 'twitlink_fancybox-media', $this->plugin_url.'js/jquery.fancybox-media.js',array('jquery'),$this->version );
                // admin styles
                wp_register_style( 'twitlink_fancybox_style', $this->plugin_url.'style/jquery.fancybox.css',NULL,$this->version);
                // plugin settings
                $settings = array ('plugin_name' => 'twitlink','author_name' => 'Andy Bailey', 'home_page' => 'http://www.commentluv.com',
                    'twitter_id' => 'commentluv','linkedin_id' => 'commentluv','facebook_page' => 'http://www.facebook.com/CommentLuv',
                    'helpdesk_link' => 'https://commentluv.zendesk.com/','show_sidebar' => 'yes','show_subscribe_form' => 'yes',
                    'aweber_list_name' => 'ab_prem_plugin','video_url'=>'http://www.youtube.com/embed/e5u4xQdxgQ8?autoplay=1');

                foreach($settings as $key => $value){
                    $this->$key = $value;   
                } 
            }

            /** admin_menu
            * This function adds a link to the settings page to the admin menu
            * see http://codex.wordpress.org/Adding_Administration_Menus
            * it is called by add_action admin_menu
            */
            function admin_menu(){
                //$level = 'manage-options'; // for wpmu sub blog admins
                $level = 'administrator'; // for single blog intalls
                $this->hook = add_options_page ( 'TwitterLink Settings', 'TwitterLink Settings', $level, $this->slug, array (&$this, 'options_page' ) );
                // load meta box script handlers
                add_action ('load-'.$this->hook, array(&$this,'queue_options_page_scripts'));
            }
            /**
            * queues up wp scripts that handle drag and drop of meta boxes
            * called by add_action('load-'.$this->hook) 
            * could also be used to add_meta_box for boxes that can be removed by user when they uncheck the boxes in the help dropdown                                                           
            */
            function queue_options_page_scripts(){   
                wp_enqueue_script('common');
                wp_enqueue_script('wp-lists');
                wp_enqueue_script('postbox');
                wp_enqueue_script('twitlink_script');
                wp_enqueue_script('twitlink_fancybox');
                //wp_enqueue_script('twitlink_fancybox-media');
                wp_enqueue_style('twitlink_fancybox_style');
                // removeable metaboxes 
                //debugbreak();
                add_meta_box('twitlink_removable','Ads','twitlink_removeable_metabox',$this->hook,'normal');

                // localize for settings page messages
                $global_settings = array(
                    'reset_message'=>__('Are you sure you want to reset the settings back to the default values?',$this->plugin_domain)
                );
                wp_localize_script ( 'twitlink_script', 'ab_global',$global_settings);

            }
            /**
            * filter functino to tell wordpress we can do 2 columns
            * 
            * @param mixed $columns
            * @param mixed $screen
            */
            function on_screen_layout_columns($columns, $screen) {     
                if ($screen == $this->hook) {
                    $columns[$this->hook] = 2;
                }
                return $columns;
            }

            /** twitlink_action
            * This function adds a link to the settings page for the plugin on the plugins listing page
            * it is called by add filter plugin_action_links
            * @param $links - the links being filtered
            * @param $file - the name of the file
            * return array - the new array of links
            */
            function plugin_action_link($links, $file) {
                $this_plugin = plugin_basename ( __FILE__ );
                $slug = 'twitlink-settings';
                if ($file == $this_plugin) {
                    $links [] = "<a href='options-general.php?page={$this->slug}'>" . __ ( 'Settings', $this->plugin_domain ) . "</a>";
                }
                return $links;
            }
            /** options_sanitize
            * This is the callback function for when the settings get saved, use it to sanitize options
            * it is called by the callback setting of register_setting in admin_init
            * @param mixed $options - the options that were POST'ed
            * return mixed $options
            */
            function options_sanitize($options){
                // do checks here  
                //debugbreak();    
                if($options['reset'] == 'reset'){
                    add_settings_error('reset_settings','reset_settings',__('Settings have been reset back to default values',$this->plugin_domain),'updated');
                    return $this->get_options(true);
                }
                $options['clickable'] = $options['clickable'] == 'yes' ? 'yes' : 'no';            
                $options['dofollow'] = isset($options['dofollow']) ? 'on' : 'off';            
                $options['newwindow'] = isset($options['newwindow']) ? 'on' : 'off';            
                $options['del_options'] = isset($options['del_options']) ? 'on' : 'off';
                $options['del_table'] = isset($options['del_table']) ? 'on' : 'off';
                return $options;
            }

            /**************************************************************
            * admin output
            *************************************************************/

            /** options_page
            * This function shows the page for saving options
            * it is called by add_options_page
            * You can echo out or use further functions to display admin style widgets here
            */
            function options_page(){
                include($this->plugin_dir.'/include/options-page.php');
            }

            /**************************************************************
            * meta box content
            *************************************************************/
            function side_content(){
            ?>
            <center>
                <table class="widefat">
                    <tbody>
                        <?php //debugbreak();?>
                        <tr><td colspan="2"><center><div style="background: url(<?php echo $this->image_url;?>playbutton.png); text-align: center; font-size: 1.4em; width: 228px; height: 44px; overflow: hidden;"><br><?php _e('Start Here',$this->plugin_domain);?></div><div><a class="fancybox-media fancybox.iframe" href="<?php echo $this->video_url;?>"><img src="<?php echo $this->image_url;?>playbuttonfront.png"/></a></div></center></td></tr>
                        <tr><td><strong><?php _e('Author',$this->plugin_domain);?>:</strong></td><td><?php echo $this->author_name;?></td></tr>
                        <tr><td><strong><?php _e('Home Page',$this->plugin_domain);?>:</strong></td><td><a href="<?php echo $this->home_page;?>" target="_blank"><?php echo $this->home_page;?></a></td></tr>
                        <tr><td><strong><?php _e('Social',$this->plugin_domain);?>:</strong></td><td><a title="Follow <?php echo $this->twitter_id;?> on Twitter" href="http://twitter.com/<?php echo $this->twitter_id;?>/" target="_blank"><img src="<?php echo $this->image_url;?>twitter.png"/></a> <a title="Join me on LinkedIn" href="http://uk.linkedin.com/in/<?php echo $this->linkedin_id;?>" target="_blank"><img src="<?php echo $this->image_url;?>linkedin.png"/></a> <a title="Join me on Facebook" href="<?php $this->facebook_page;?>" target="_blank"><img src="<?php echo $this->image_url;?>facebook.png"/></a></td></tr>
                        <tr><td><strong><?php _e('Help',$this->plugin_domain);?>:</strong></td><td><a href="<?php echo $this->helpdesk_link;?>" target="_blank"><?php _e('Help Desk',$this->plugin_domain);?></a></td></tr>
                        <?php if($this->show_subscribe_form == 'yes'){
                            ?>
                            <tr class="alt"><td colspan="2"><?php _e('Subscribe',$this->plugin_domain);?>:</td></tr>
                            <tr>
                                <td colspan="2">
                                    <?php
                                        echo '<div id="sub_box"><p style="font-size: 1.3em; font-weight: bold">Get exclusive offers!</p>';
                                        echo '<input type="text" size="40" id="sub_email" value="'.get_bloginfo('admin_email').'"/>';
                                        echo '<br><img align="left" title="'.__('I promise not to spam you or sell your details',$this->plugin_domain).'" src="'.$this->image_url.'no_spam_button.jpg"/><span id="ab_sub_button" style="width: 100px; padding-top: 5px; border-top: 1px solid #cdcdcd; border-right: 1px solid #cdcdcd; border-bottom: 1px solid #ababab; border-left: 1px solid #ababab; display: block; text-align: center; float: right; cursor: pointer;">Subscribe</p>';    
                                        echo '</div>';
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                        <tr><td colspan="2">
                                <p style="font-size: 8pt;"><?php _e('Many thanks to these people for providing translations',$this->plugin_domain);?></p>
                                <ul>
                                    <li><?php _e('Italian by',$this->plugin_domain);?> <a class="italian" href="http://gidibao.net/">Gianni</a></li>
                                    <li><?php _e('German by',$this->plugin_domain);?> <a class="german" href="http://dieschatzen.at/">Mark Waiss</a></li>
                                    <li><?php _e('French by',$this->plugin_domain);?> <a class="french" href="http://www.wptrads.fr">Didier</a></li>
                                    <li><?php _e('Russian by',$this->plugin_domain);?> <a class="russian" href="http://yoyurec.in.ua">Yuriy</a></li>
                                    <li><?php _e('Swedish by',$this->plugin_domain);?> <a class="swedish" href="http://minablandadeinfall.se/">Stefan Ljungwall</a></li>
                                    <li><?php _e('Belorussian by',$this->plugin_domain);?> <a class="Belorussian" href="http://pc.de/">Patricia Clausnitzer</a></li>
                                </ul>
                            </td></tr>
                            <tr><td colspan="2">
                                <p style="font-size: 8pt;"><?php _e('Big thanks to these beta testers',$this->plugin_domain);?></p>
                                <ul>
                                    <li><?php _e('Jon Barry',$this->plugin_domain);?> <a class="working from home" href="http://jonbarry.co.uk/">jonbarry.co.uk - lets talk</a></li>
                                    <li><?php _e('Mark Hughes',$this->plugin_domain);?> <a class="social media marketing" href="http://www.funkysocialmedia.com">funkysocialmedia - marketing</a></li>
                                </ul>
                            </td></tr>
                    </tbody>
                </table>
            </center>
            <?php
            }


            /**************************************************************
            * helper functions
            *************************************************************/
            /**
            * adds link to author name
            * called by add_filter('comment_author_link')
            * 
            * @param string $link - the current link
            */
            function add_twitlink_to_comment_author_link($link){
                //debugbreak();
                global $comment;
                if($twitter_link = $this->make_twitlink($comment->comment_author_email)){
                    $link = $link.$twitter_link;
                }
                return $link;
            }
            /**
            * adds the twitter link to the comment text.
            * called by add_filter('comment_text')
            * 
            * @param string $comment_text
            * uses global $comment
            */
            function add_twitlink_to_comment_text($comment_text){
                global $comment;
                //get link if exists
                if($twitter_link = $this->make_twitlink($comment->comment_author_email)){
                    // construct twitter link
                    $link = $twitter_link;
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
            /**
            * add a twitter field in a box after the comment form at the comment_form action
            * called by add_action('comment_form in setup_commentform()
            * (conditionals have already been checked)
            * 
            */
            function add_twitterfield_box(){
                $options = $this->get_options();
                $output = '<div class="'.$options['div_class'].'">'.$options['field_description'] .
                '<input id="atf_twitter_id" name="atf_twitter_id" type="text" size="30" tabindex="5" value=""/>' . 
                '</div>';
                if($options['div_class'] == 'twitlink'){
                    $output .= '<style>.twitlink {border: 1px solid #d1d1d1; background-color: lightBlue; padding: 5px; margin: 5px;}</style>';
                }
                echo $output;
            }
            /**
            * add a twitter field at the comment_form_after_fields action
            * called by add_action('comment_form_after_fields in setup_commentform()
            * (conditionals have already been checked)
            * 
            */
            function add_twitterfield_legacy($args){ 
            //debugbreak();  
                $options = $this->get_options();
                $inputclass = $options['input_class'] ? 'class="'.$options['input_class'].'"' : '';  
                $inputfieldclass = $options['input_field_class'] ? 'class="'.$options['input_field_class'].'"' : '';  
                $label = $options['input_label'];
                $l_html = '<label for="atf_twitter_id">' . $label . '</label>';
                $i_html = '<input '.$inputfieldclass.' id="atf_twitter_id" name="atf_twitter_id" type="text" size="30" tabindex="4" value=""/>';
                if($options['input_label_position'] == 'before'){
                    $html = $l_html.$i_html;
                } else {
                    $html = $i_html.$l_html;
                }
                echo '<p '.$inputclass.'>'.$html.'</p>';
            }
            /**
            * filter the comment_form_defaults
            * called by add_filter('comment_form_defaults in setup_commentform()
            * (conditionals have already been checked)
            * no longer use value to populate name from cookie because cache plugins sometimes keep the value from last time the page
            * was viewed and had a value in it which results in wrong twitter name being used
            */
            function add_twitterfield_wp3($args){   
                $options = $this->get_options();
                $inputclass = $options['input_class'] ? 'class="'.$options['input_class'].'"' : '';
                $inputfieldclass = $options['input_field_class'] ? 'class="'.$options['input_field_class'].'"' : '';   
                $label = $options['input_label'];
                if($options['input_label_position'] == 'before'){
                    $args['fields']['twitter'] = '<p '.$inputclass.'><label for="atf_twitter_id">' . $label . '</label>' .
                    '<input '.$inputfieldclass.' id="atf_twitter_id" name="atf_twitter_id" type="text" size="30" value=""/></p>';
                } else {
                    $args['fields']['twitter'] = '<p '.$inputclass.'>' .
                    '<input '.$inputfieldclass.' id="atf_twitter_id" name="atf_twitter_id" type="text" size="30" value=""/>
                    <label for="atf_twitter_id">' . $label . '</label></p>';

                }
                return $args;
            }
            /**
            * adds the twitter username to the database if it was supplied
            * called by add_action('preprocess_comment')
            * @param array $comment_data - the data that was sent with the comment form submission
            * @return array $comment_data
            */
            function add_twitter_field_value($comment_data){
                //debugbreak();
                global $wpdb;
                if(isset($_POST['atf_twitter_id'])){
                    // clean value
                    $twitter = esc_attr(trim($_POST['atf_twitter_id']));
                    $twitter = str_replace('@','',$twitter);
                    // if user used url (which they do even if you tell them not to), remove it
                    if(stripos($twitter,'twitter.com')){
                        $arr = explode('/',$twitter);
                        $arr = array_filter($arr); // removes empty elements (if url had trailing slash)
                        $twitter = $arr[sizeof($arr)];  // gets last element in array
                    }
                    // update database
                    if($twitter && is_email($comment_data['comment_author_email'])){
                        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wptwitipid (email,twitid) VALUES (%s,%s) ON DUPLICATE KEY UPDATE twitid = %s",$comment_data['comment_author_email'],$twitter,$twitter);
                        $wpdb->query($query);
                        // set cookie - dont use this any more due to cache plugins causing shenanigans!
                        //$comment_cookie_lifetime = apply_filters('comment_cookie_lifetime', 30000000);
                        //setcookie('comment_author_twitter_' . COOKIEHASH, $twitter, time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
                    }
                }
                return $comment_data;
            }
            /**
            * internal function for making a twitterlink with dofollow or not
            * 
            * @param string $email  - the email to search for and to make as a link
            */
            function make_twitlink($email){
                //debugbreak();
                global $wpdb;
                $options = $this->get_options();
                $profiletwitter = false;
                $dbtwitter = $wpdb->get_var($wpdb->prepare("SELECT twitid FROM {$wpdb->prefix}wptwitipid WHERE email = %s",$email));
                // set this->twitter_id
                if($options['use_profile'] == 'on'){
                    $user = get_user_by_email($email);
                    $id = $user->ID;
                    if($options['profile_field']){
                        $profiletwitter = get_user_meta($id,$options['profile_field'],true);
                    }
                }
                // use profile value if set or else use db value 
                if($profiletwitter){
                    $this->twitter_id = $profiletwitter;
                } else {
                    $this->twitter_id = $dbtwitter;
                }
                $this->twitter_id = trim($this->twitter_id);
                if(!$this->twitter_id){
                    return false;
                }
                // construct link
                $pre = $options['pre_link_html'];
                $pst = $options['post_link_html'];     
                $search = '[username]';
                $replace = $this->twitter_id;
                $anchor = str_replace($search,$replace,$options['anchor_text']);
                if($options['clickable']=='yes'){
                    $nofollow = ' rel="nofollow"';
                    $target = '';
                    if($options['dofollow'] == 'on'){
                        $nofollow = ' rel="external"';
                    }
                    if($options['newwindow'] == 'on'){
                        $target = ' target="_blank"';
                    }
                    $class = ' class="'.$options['link_class'].'"';
                    $link = '<a'.$nofollow.$target.$class.' href="http://twitter.com/'.$this->twitter_id.'">'.$anchor.'</a>';
                } else {
                    $link = $anchor;
                }
                $link = $pre.$link.$pst;
                return $link;
            }
            /**
            * removes the twitter id when a comment is spammed
            * called by add_action('wp_set_comment_status')
            *                        
            * @param int $id - the id of the comment
            * @param string $status - the status
            */
            function remove_spam_value($id,$status){
                if($status == 'spam'){
                    $tempcomment = get_comment($id);
                    $email = $tempcomment->comment_author_email;
                    global $wpdb;
                    $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}wptwitipid WHERE email = %s",$email);
                    $result = $wpdb->query($query); 
                }
            }
            /**
            * decide how to add the field to the comment form based on settings
            * called by add_action('template_redirect')
            * defaults to after the comment form
            * 
            */
            function setup_comment_form(){
                $options = $this->get_options();
                if($options['field_method'] == 'after_fields' && $options['form_type'] == 'wp3'){
                    add_filter('comment_form_defaults',array(&$this,'add_twitterfield_wp3'));
                } elseif( $options['field_method'] == 'after_fields' && $options['form_type']== 'legacy'){
                    add_action('comment_form_after_fields',array(&$this,'add_twitterfield_legacy'));
                } elseif( $options['field_method'] == 'after_form' ){
                    add_action('comment_form',array(&$this,'add_twitterfield_box'));
                }
            }
            /**
            * ajax handler for processing subscribe to email updates
            * 
            */
            function ab_subscribe(){
                debugbreak();
                $email = strip_tags($_POST['email']);
                if(!is_email($email)){
                    _e('Email address is not valid',$this->plugin_domain);
                    exit;
                }
                $list = $this->aweber_list_name;
                $to = $list.'@aweber.com';
                $subject = 'subscribe';    
                $name = strip_tags(get_bloginfo('name'));
                remove_all_filters('wp_mail_from');
                $success = wp_mail($to,$subject,'subscribe','From: '.$name.'<'.$email.'>'."\r\n");           
                if($success){
                    printf(__('Email %s added to list, please check your inbox for the confirmation link',$this->plugin_domain),$email);
                } else {
                    _e('oh dear! some error happened with sending the subscribe email, you may have to subscribe manually at ComLuv.com',$this->plugin_domain);
                }                                                                                                    
                exit;
            }
            /**
            * converts a string into a php type version number
            * eg. 2.81.2 will become 2.8.1.2
            * used to prepare a number to be used with version_compare
            * 
            * @param mixed $string - the version to be converted to php type version
            * @return string        
            */
            function php_version($string){
                if(empty($string)){
                    return;
                }
                $version = str_replace('.','',$string);
                $std = array();
                for($i=0; $i < strlen($version); $i++){
                    $std[] = $version[$i];
                }
                $php_version = implode('.',$std);
                return $php_version;
            }

        } // end class
    } // end if class not exists

    // start twitlink class engines
    if (class_exists ( 'twitlink' )) :
        $twitlink = new twitlink ( );
        // confirm warp capability
        if (isset ( $twitlink )) {
            // engage
            register_activation_hook ( __FILE__, array (&$twitlink, 'install' ) );
        }
        endif;
?>