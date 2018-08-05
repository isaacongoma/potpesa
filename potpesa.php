<?php
/**
 * @package Potpesa
 * @subpackage Main Plugin File
 * @author Mauko Maunde <mauko@ose.co.ke>, Brightone Mwasaru <bmwasaru@gmail.com>, Johnes Mecha <jmecha09@gmail.com>
 * @version 1.8
 *
 * Plugin Name: Potpesa
 * Plugin URI: https://swahilipothub.co.ke/
 * Description: This plugin extends WordPress functionality to integrate MPesa for making and receiving online payments.
 * Author: Osen Concepts Kenya < hi@osen.co.ke >
 * Version: 1.8
 * Author URI: https://swahilipothub.co.ke/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9.8
 */
 
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
 require_once('osen-php-mpesa.php);

 function get_post_id_by_meta_key_and_value($key, $value) {
    global $wpdb;
    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$key."' AND meta_value='".$value."'");
    if (is_array($meta) && !empty($meta) && isset($meta[0])) {
        $meta = $meta[0];
    }
    if (is_object($meta)) {
        return $meta->post_id;
    } else {
        return false;
    }
}

/**
 * Installation hook callback creates plugin settings
 */
register_activation_hook( __FILE__, 'potpesa_install' );
function potpesa_install()
{
	update_option( 'potpesa_version', POTPESA_VERSION );
	update_option( 'potpesa_urls_reg', 0 );
}

/**
 * Uninstallation hook callback deletes plugin settings
 */
register_uninstall_hook( __FILE__, 'potpesa_uninstall' );
function potpesa_uninstall()
{
	delete_option( 'potpesa_version' );
	delete_option( 'potpesa_urls_reg' );
}

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'potpesa_action_links' );
function potpesa_action_links( $links )
{
	return array_merge( $links, [ '<a href="'.admin_url( 'admin.php?page=page=potpesa' ).'">&nbsp;Configure</a>' ] );
}
