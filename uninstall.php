<?php
    if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
        exit();
     //debugbreak();
    // check which things to delete (if any)
    $options = get_option('twitlink');
    if($options['del_options'] != 'off'){
        // delete twitlink options
        delete_option('twitlink');
    }
    if($options['del_table'] != 'off'){
        // remove db table
        global $wpdb;
        global $prefix;
        $query = $wpdb->prepare('DROP TABLE IF EXISTS '.$wpdb->prefix.'wptwitipid');
        $wpdb->query($query);
    }

?>