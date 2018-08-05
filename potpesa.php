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
define( 'POTPESA_DIR', plugin_dir_path( __FILE__ ) );

require_once( POTPESA_DIR.'/osen-php-mpesa.php' );

require_once( POTPESA_DIR.'/settings.php' );

require_once( POTPESA_DIR.'/payments.php' );

require_once( POTPESA_DIR.'/metaboxes.php' );

require_once( POTPESA_DIR.'/analytics.php' );

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

$mconfig = get_option( 'mc_options' );
$mconfig['mc_conf_callback_url']     = rtrim( home_url(), '/').':443/?mpesa_ipn_listener=reconcile';
$mconfig['mc_conf_timeout_url']      = rtrim( home_url(), '/').':443/?mpesa_ipn_listener=timeout';
$mconfig['mc_conf_result_url'] 		  = rtrim( home_url(), '/').':443/?mpesa_ipn_listener=reconcile';
$mconfig['mc_conf_confirmation_url'] = rtrim( home_url(), '/').':443/?mpesa_ipn_listener=confirm';
$mconfig['mc_conf_validation_url'] 	= rtrim( home_url(), '/').':443/?mpesa_ipn_listener=validate';
stk_config( $mconfig );

add_shortcode('POTPAYER', 'mc_form_callback');
function mc_form_callback( $atts = array(), $content = null ) {
	$mconfig = get_option( 'mc_options' );
	$status = isset( $_SESSION['mc_trx_status'] ) ? $mconfig['mc_mpesa_conf_msg'].'<br>'.$_SESSION['mc_trx_status'] : '';
  return '<form id="mc-contribution-form" method="POST" action="" class="mc_contribution_form">
  	<p>'.$status.'</p>
  	<input type="hidden" name="action" value="process_mc_form">
    
  	<label for="mc-phone">Phone Number</label>
  	<input id="mc-phone" type="text" name="mc-phone" placeholder="Phone Number" class="mc_phone"><br>
  	<label for="mc-amount">Amount to contribute</label>
  	<input id="mc-amount" type="text" name="mc-amount" value="200" class="mc_amount"><br>
  	<button type="submit" name="mc-contribute" class="mc_contribute">CONTRIBUTE</button>
  </form>';
}
add_action( 'init', 'mc_process_form_data' );
function mc_process_form_data() {
  if ( isset( $_POST['mc-contribute'] ) ) {
    $amount   = trim( $_POST['mc-amount'] );
  	$phone 		= trim( $_POST['mc-phone'] );
  	$response 	= mc_mpesa_checkout( $amount, $phone, 'Contributions' );
  	$status 	= json_decode( $response );
	$s 			= '';  }
}
/**
 * Register Validation and Confirmation URLs
 * Outputs registration status
 */
add_action( 'init', 'mc_mpesa_do_register' );
function mc_mpesa_do_register()
{
	header( "Access-Control-Allow-Origin: *" );
	header( 'Content-Type:Application/json' );
	if ( ! isset( $_GET['mpesa_ipn_register'] ) ){ return; }
    
	wp_send_json( stk_register() );
}
/**
 * 
 */
add_action( 'init', 'mc_mpesa_confirm' );
function mc_mpesa_confirm()
{
	if ( ! isset( $_GET['mpesa_ipn_listener'] ) ) return;
    if ( $_GET['mpesa_ipn_listener'] !== 'confirm' ) return;
	$response = json_decode( file_get_contents( 'php://input' ), true );
	if( ! isset( $response['Body'] ) ){
    	return;
    }
	header( "Access-Control-Allow-Origin: *" );
	header( 'Content-Type:Application/json' );
	wp_send_json( stk_confirm() );
}
/**
 * 
 */
add_action( 'init', 'mc_mpesa_validate' );
function mc_mpesa_validate()
{
	if ( ! isset( $_GET['mpesa_ipn_listener'] ) ){ return; }
    if ( $_GET['mpesa_ipn_listener'] !== 'validate' ){ return; }
	$response = json_decode( file_get_contents( 'php://input' ), true );
	if( ! isset( $response['Body'] ) ){
    	return;
    }
	header( "Access-Control-Allow-Origin: *" );
	header( 'Content-Type:Application/json' );
	wp_send_json( stk_validate() );
}
