<?php
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();
// delete twitlink options
delete_option('twitlink');
// remove db table
global $wpdb;
global $prefix;
$query = $wpdb->prepare('DROP TABLE IF EXISTS '.$wpdb->prefix.'wptwitipid');
$wpdb->query($query);
?>