<?php
    global $wpdb,$twitlink;
    if(!$wpdb){
        die('Please do not load this file directly');
    }    
    $data = $twitlink->get_options();
    //we need the global screen column value to be able to have a sidebar in WordPress 2.8 (if we want to use this)
    global $screen_layout_columns; // will only have value if metabox has been added in queue_options_page_scripts function called by load-$hook action
    // set up main and side metaboxes
    add_meta_box('twitlink_side',__('Plugin Info',$twitlink->plugin_domain),array(&$twitlink,'side_content'),$twitlink->hook,'side');
    if(isset($_GET['tab']) && $_GET['tab'] == 'twitlink_tab'){
        add_meta_box('twitlink_tab',__('Another Tab',$twitlink->plugin_domain),array(&$twitlink,'tab_metabox1_content'),$twitlink->hook,'normal');
    } else {
        // show settings meta boxes (these cannot be removed by user, 
        //if you want that, declare the add_meta_boxes in load-$pagehook action callback)
        add_meta_box('twitlink_settings',__('Adding the Twitter field',$twitlink->plugin_domain),'twitlink_field_content',$twitlink->hook,'normal');
        add_meta_box('twitlink_position',__('Twitter Link Position',$twitlink->plugin_domain),'twitlink_position_content',$twitlink->hook,'normal');
        add_meta_box('twitlink_format',__('Twitter Link Format',$twitlink->plugin_domain),'twitlink_format_content',$twitlink->hook,'normal');
        add_meta_box('twitlink_uninstall',__('Uninstall options',$twitlink->plugin_domain),'twitlink_uninstall_content',$twitlink->hook,'advanced');
    }
    // set up html for options page and tabs 
?>
<div class="wrap twitlink-settings">
    <?php screen_icon('options-general'); ?> 
    <h2 class="nav-tab-wrapper">
        <a href="options-general.php?page=<?php echo $twitlink->slug;?>" class="nav-tab <?php echo $_GET['tab'] != 'twitlink_tab' ? 'nav-tab-active' : '';?>"><?php _e('Plugin Settings',$twitlink->plugin_domain);?></a>
    </h2>
    <p><?php echo sprintf(__('This is the free version of TwitterLink Comments which is a light version of the TwitterLink that you get with %s ',$twitlink->plugin_domain),'<a href="http://ql2.me/upgradetoclpremium" target="_blank" title="go Premium!">CommentLuv Premium</a>');?></p>
    <p><?php _e('The premium version allows you to see the twitter names in the admin comments page and remove them with a single click and more.',$twitlink->plugin_domain);?></p>
    <form method="post" action="options.php">
        <?php 
            // set up hidden fields for options and meta box drag n drop stuff
            settings_fields( 'twitlink_options_group' ); // the name given in admin init
            wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
            wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); 
            // setup containers for meta boxes   
            //debugbreak();
            if($twitlink->show_sidebar != 'yes'){
                $screen_layout_columns = 1;
            } else {
                $screen_layout_columns = 2;
            }
        ?>
        <div id="poststuff">

            <div id="post-body" class="metabox-holder <?php echo 2 == $screen_layout_columns ? ' columns-2' : ''; ?>">

                <div id="post-body-content" class="has-sidebar-content">
                    <?php 
                        // main content
                        do_meta_boxes($twitlink->hook, 'normal', $data); 
                        do_meta_boxes($twitlink->hook, 'advanced', $data); 
                        // save and reset button  
                    ?>
                    <p>
                        <input type="submit" value="Save Settings" class="button-primary" name="Submit"/>    
                    </p>
                    <p>
                        <input type="submit" value="reset" id="reset" name="<?php echo $twitlink->db_option;?>[reset]"/>
                    </p>
                </div> <!-- end post-body-content -->
                <div id="postbox-container-1" class="postbox-container">
                    <?php 
                        // sidebar
                        if($twitlink->show_sidebar == 'yes'){
                            do_meta_boxes($twitlink->hook, 'side', $data); 
                    }?>
                </div> <!-- end side-info-column -->

            </div> <!-- end post-body -->

        </div> <!-- end poststuff -->
    </form>

</div> <!-- end wrap -->
<!-- script stuff for draggable and droppable meta boxes -->
<script type="text/javascript">
    //<![CDATA[
    jQuery(document).ready( function($) {
        // close postboxes that should be closed
        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        // postboxes setup
        postboxes.add_postbox_toggles('<?php echo $twitlink->hook; ?>');
    });
    //]]>
</script>

<?php
    /**
    * main settings meta box
    * 
    * @param mixed $data - the options array
    */
    function twitlink_field_content($data){
        global $twitlink;
        echo '<p>'.__('Choose the method you want to use to add the twitter field to your comment form',$twitlink->plugin_domain).'</p>';
    ?>
    <table class="widefat field">
        <tr>
            <td>
                <input type="radio" value="after_fields" name="<?php echo $twitlink->db_option;?>[field_method]" <?php checked($data['field_method'],'after_fields',true);?>/> <label for="<?php echo $twitlink->db_option;?>[field_method]"><?php _e('Add field after name,email,url fields',$twitlink->plugin_domain);?></label>
                <br /><small><?php _e('Only works with themes that use comment_form() to display form',$twitlink->plugin_domain);?></small>
            </td>
            <td>
                <label for="<?php echo $twitlink->db_option;?>[form_type]"><?php _e('Form type',$twitlink->plugin_domain);?></label>     
                <p>
                    <select name="<?php echo $twitlink->db_option;?>[form_type]">
                        <option value="wp3" <?php selected($data['form_type'],'wp3',true);?>><?php _e('3.0 Theme',$twitlink->plugin_domain);?></option>
                        <option value="legacy" <?php selected($data['form_type'],'legacy',true);?>><?php _e('Alternate',$twitlink->plugin_domain);?></option>
                    </select>                                                                                                            
                </p>

            </td>
            <td>
                <label for="<?php echo $twitlink->db_option;?>[input_class]"><?php printf(__('Class for enclosing %s tag',$twitlink->plugin_domain),'&lt;p&gt;');?></label>
                <p>
                    <input type="text" name="<?php echo $twitlink->db_option;?>[input_class]" value="<?php echo $data['input_class'];?>"/>
                </p>
                <label for="<?php echo $twitlink->db_option;?>[input_field_class]"><?php printf(__('Class for input field',$twitlink->plugin_domain),'&lt;p&gt;');?></label>
                <p>
                    <input type="text" name="<?php echo $twitlink->db_option;?>[input_field_class]" value="<?php echo $data['input_field_class'];?>"/>
                </p>
            </td>
            <td>
                <label for="<?php echo $twitlink->db_option;?>[input_label]"><?php _e('Input label',$twitlink->plugin_domain);?></label>
                <p>
                    <input type="text" name="<?php echo $twitlink->db_option;?>[input_label]" value="<?php echo $data['input_label'];?>"/>
                </p>
            </td>
            <td>
                <label for="<?php echo $twitlink->db_option;?>[input_label_position]"><?php _e('Label position',$twitlink->plugin_domain);?></label>
                <p>
                    <select name="<?php echo $twitlink->db_option;?>[input_label_position]">
                        <option value="after" <?php selected($data['input_label_position'],'after',true);?>><?php _e('After input',$twitlink->plugin_domain);?></option>
                        <option value="before" <?php selected($data['input_label_position'],'before',true);?>><?php _e('Before input',$twitlink->plugin_domain);?></option>
                    </select>
                </p>                            
            </td>
        </tr>
        <tr>
            <td>
                <input type="radio" value="after_form" name="<?php echo $twitlink->db_option;?>[field_method]" <?php checked($data['field_method'],'after_form',true);?>/> 
                <label for="<?php echo $twitlink->db_option;?>[field_method]"><?php _e('Add box after all comment form fields',$twitlink->plugin_domain);?></label>
                <br /><small><?php _e('Use this if above option does not work with 3.0 theme or legacy',$twitlink->plugin_domain);?></small>
            </td>
            <td colspan="4">
                <label for="<?php echo $twitlink->db_option;?>[field_description]"><?php _e('Text to display inside box (html allowed)',$twitlink->plugin_domain);?></label>
                <p>
                    <textarea rows="3" style="width: 90%" name="<?php echo $twitlink->db_option;?>[field_description]"><?php echo $data['field_description'];?></textarea>
                </p>
                <p>
                    <label for="<?php echo $twitlink->db_option;?>[div_class]"><?php _e('Div class',$twitlink->plugin_domain);?></label>
                    <input type="text" name="<?php echo $twitlink->db_option;?>[div_class]" value="<?php echo $data['div_class'];?>"/>
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <input type="radio" value="no_add" name="<?php echo $twitlink->db_option;?>[field_method]" <?php checked($data['field_method'],'no_add',true);?>/> 
                <label for="<?php echo $twitlink->db_option;?>[field_method]"><?php _e('Do not add a field. I will do it manually',$twitlink->plugin_domain);?></label>
            </td>
            <td colspan="4">
                <?php _e('Please use name="atf_twitter_id" in the field that you add',$twitlink->plugin_domain);?>
            </td>
        </tr>
    </table>
    <?php

    }
    /**
    * stats meta box
    * 
    * @param mixed $data  (not used as passed var)
    */
    function twitlink_position_content($data){ 
        global $twitlink;                                                           
    ?>
    <table class="widefat"> 
        <tr>
            <td style="width: 200px;">
                <input type="radio" name="<?php echo $twitlink->db_option;?>[position]" value="under_name" <?php checked($data['position'],'under_name',true);?> />
                <?php _e('Under name link',$twitlink->plugin_domain);?>
            </td>
            <td style="width: 200px;">
                <input type="radio" name="<?php echo $twitlink->db_option;?>[position]" value="start_comment" <?php checked($data['position'],'start_comment',true);?> />
            <?php _e('Start of comment text',$twitlink->plugin_domain);?></td>
            <td>
                <input type="radio" name="<?php echo $twitlink->db_option;?>[position]" value="end_comment" <?php checked($data['position'],'end_comment',true);?> />
            <?php _e('End of comment text',$twitlink->plugin_domain);?></td>
        </tr>   
    </table>
    <?php 
    }
    function twitlink_format_content($data){
        global $twitlink;
    ?>
    <table class="widefat">
        <tr>
            <td colspan="3">
                <table width="100%">
                    <tr>
                        <td>
                            <?php _e('HTML before',$twitlink->plugin_domain);?>
                            <p><input type="text" name="<?php echo $twitlink->db_option;?>[pre_link_html]" value="<?php echo htmlspecialchars($data['pre_link_html']);?>"/></p>
                        </td>
                        <td>
                            <?php _e('Anchor text',$twitlink->plugin_domain);?><br /><strong>[username]</strong> <?php _e('will be replaced by users twitter ID',$twitlink->plugin_domain);?>
                            <p><input type="text" name="<?php echo $twitlink->db_option;?>[anchor_text]" value="<?php echo $data['anchor_text'];?>"/></p>
                        </td>
                        <td>
                            <?php _e('HTML after',$twitlink->plugin_domain);?>
                            <p><input type="text" name="<?php echo $twitlink->db_option;?>[post_link_html]" value="<?php echo htmlspecialchars($data['post_link_html']);?>"/></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background-color: #dfdfdf; text-align: center; font-weight: bolder;" colspan="3"><?php _e('Link attributes',$twitlink->plugin_domain);?></td>
        </tr>
        <tr>
            <td>
                <label for="<?php echo $twitlink->db_option;?>[clickable]"><?php _e('Make [username] clickable?',$twitlink->plugin_domain);?></label>
                <p>
                    <select name="<?php echo $twitlink->db_option;?>[clickable]">
                        <option value="yes"><?php _e('Yes',$twitlink->plugin_domain);?></option>
                        <option value="no"><?php _e('No',$twitlink->plugin_domain);?></option>
                    </select>
                </p>
                <?php _e('If you are loading the twitter anywhere library then you might want to set this to No',$twitlink->plugin_domain);?>
            </td>
            <td>
                <p>
                    <input type="checkbox" name="<?php echo $twitlink->db_option;?>[dofollow]" value="on" <?php checked($data['dofollow'],'on',true);?>>
                    <label for="<?php echo $twitlink->db_option;?>[dofollow]"><?php _e('Dofollow the link?',$twitlink->plugin_domain);?></label>
                </p><p>
                    <input type="checkbox" name="<?php echo $twitlink->db_option;?>[newwindow]" value="on" <?php checked($data['newwindow'],'on',true);?>>
                    <label for="<?php echo $twitlink->db_option;?>[newwindow]"><?php _e('Open in new window?',$twitlink->plugin_domain);?></label>
                </p><p>
                    <input type="text" name="<?php echo $twitlink->db_option;?>[link_class]" value="<?php echo $data['link_class'];?>"/>
                    <label for="<?php echo $twitlink->db_option;?>[link_class]"><?php _e('Link class',$twitlink->plugin_domain);?></label>
                </p>
            </td>
            <td></td>
        </tr>
    </table>
    <?php
    }
    function twitlink_uninstall_content($data){
        //debugbreak();    
        global $twitlink;
        _e('You can remove all options and the database table that this plugin created when you delete this plugin by ticking the boxes below',$twitlink->plugin_domain);
    ?>
    <p>
        <input type="checkbox" name="<?php echo $twitlink->db_option;?>[del_options]" value="on" <?php checked($data['del_options'],'on',true);?>>
        <label for="<?php echo $twitlink->db_option;?>[del_options]"><?php _e('Delete options?',$twitlink->plugin_domain);?></label>
    </p><p>
        <input type="checkbox" name="<?php echo $twitlink->db_option;?>[del_table]" value="on" <?php checked($data['del_table'],'on',true);?>>
        <label for="<?php echo $twitlink->db_option;?>[del_table]"><?php _e('Delete the twitlink database table?',$twitlink->plugin_domain);?></label>
    </p>
    <p class="description">
    <?php
        _e('If you select to delete the table then all the twitter usernames that have been added from comments will no longer be available',$twitlink->plugin_domain);
        echo '<br>';
        printf(__('If you are installing %s then you might want to keep the table so it can use it too and remember all the twitter names',$twitlink->plugin_domain),'<b><a href="http://www.commentluv.com/?utm_source=twitlinksettingspage&utm_medium=plugin&utm_content=textlink&utm_campaign=freeplugin" target="_blank">CommentLuv Premium</a></b>');
    }
    function twitlink_removeable_metabox($data){
        global $twitlink;
        // removable
        include_once(ABSPATH . WPINC . '/feed.php');
        if(function_exists('fetch_feed')){
            //debugBreak();
            $uri = 'http://comluv.com/category/ads/feed/';
            $feed = fetch_feed($uri);
            if(!is_wp_error($feed)){
                $rss_items = $feed->get_items(0,3);
                if($rss_items){
                    foreach($rss_items as $item){
                    ?>
                    <ul>
                        <li>
                            <a href='<?php echo esc_url( $item->get_permalink() ); ?>'
                                title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
                            <?php echo esc_html( $item->get_title() ); ?></a>
                            <?php echo $item->get_description(); ?>
                        </li>
                    </ul>
                    <?php
                    }
                }
            }
        }
}