<?php
/*
 * Plugin Name: Potpesa
 * Plugin URI: https://swahilipothub.co.ke/
 * Description: This plugin extends WordPress functionality to integrate MPesa for making and receiving online payments.
 * Author: Swahilipot Hub
 * Version: 1.8
 * Author URI: https://swahilipothub.co.ke/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9.8
 */
 
/**
 * @package Potpesa
 * @subpackage Main Plugin File
 * @author Mauko Maunde <mauko@osen.co.ke>, Brightone Mwasaru <bmwasaru@gmail.com>, Johnes Mecha <jmecha09@gmail.com>
 * @version 1.8
 * @since 1.8
 * @license See LICENSE
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define( 'POTPESA_DIR', plugin_dir_path( __FILE__ ) );

require_once( POTPESA_DIR.'inc/osen-php.mpesa.php' );

require_once( POTPESA_DIR.'inc/settings.php' );

require_once( POTPESA_DIR.'inc/payments.php' );

require_once( POTPESA_DIR.'inc/metaboxes.php' );

require_once( POTPESA_DIR.'inc/analytics.php' );

 function potpesa_get_post_id_by_meta_key_and_value($key, $value) {
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
	return array_merge( $links, [ '<a href="'.admin_url( 'admin.php?page=potpesa' ).'">&nbsp;Configure</a>' ] );
}

$potpesaonfig = get_option( 'potpesa_options', array() );
stk_config( $potpesaonfig );

add_shortcode('POTPAYER', 'potpesa_form_callback');
function potpesa_form_callback( $atts = array(), $content = null ) {
  return '<form id="potpesa-contribution-form" method="POST" action="" class="potpesa_contribution_form">
  	<input type="hidden" name="action" value="process_potpesa_form">
    
  	<label for="potpesa-phone">Phone Number</label>
  	<input id="potpesa-phone" type="text" name="potpesa-phone" placeholder="Phone Number" class="potpesa_phone"><br>
  	<label for="potpesa-amount">Amount to contribute</label>
  	<input id="potpesa-amount" type="text" name="potpesa-amount" value="200" class="potpesa_amount"><br>
  	<button type="submit" name="potpesa-contribute" class="potpesa_contribute">CONTRIBUTE</button>
  </form>';
}

add_action( 'init', 'potpesa_process_form_data' );
function potpesa_process_form_data() {
  if ( isset( $_POST['potpesa-contribute'] ) ) {
    $amount   = trim( $_POST['potpesa-amount'] );
  	$phone 		= trim( $_POST['potpesa-phone'] );
  	$response = stk_request( $phone, $amount, 'Contributions' );
  	$status 	= json_decode( $response );
  }
}

/**
 * Register Validation and Confirmation URLs
 * Outputs registration status
 */
add_action( 'init', 'potpesa_mpesa_do_register' );
function potpesa_mpesa_do_register()
{
	header( "Access-Control-Allow-Origin: *" );
	header( 'Content-Type:Application/json' );
	if ( ! isset( $_GET['mpesa_ipn_register'] ) ){ return; }
    
	wp_send_json( stk_register() );
}

/**
 * 
 */
add_action( 'init', 'potpesa_mpesa_confirm' );
function potpesa_mpesa_confirm()
{
	if ( ! isset( $_GET['mpesa_ipn_listener'] ) ) return;
    if ( $_GET['mpesa_ipn_listener'] !== 'confirm' ) return;
	$response = json_decode( file_get_contents( 'php://input' ), true );
	if( ! isset( $response['Body'] ) ){
    	return;
    }
	header( "Access-Control-Allow-Origin: *" );
	header( 'Content-Type:Application/json' );
	wp_send_json( stk_confirm( null, $response['Body'] ) );
}
/**
 * 
 */
add_action( 'init', 'potpesa_mpesa_validate' );
function potpesa_mpesa_validate()
{
	if ( ! isset( $_GET['mpesa_ipn_listener'] ) ){ return; }
    if ( $_GET['mpesa_ipn_listener'] !== 'validate' ){ return; }
	$response = json_decode( file_get_contents( 'php://input' ), true );
	if( ! isset( $response['Body'] ) ){
    	return;
    }
	header( "Access-Control-Allow-Origin: *" );
	header( 'Content-Type:Application/json' );
	wp_send_json( stk_validate( null, $response['Body'] ) );
}
