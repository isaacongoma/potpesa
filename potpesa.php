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
 * @author Mauko Maunde <mauko@osen.co.ke>
 * @author Brightone Mwasaru <bmwasaru@gmail.com>
 * @author Johnes Mecha <jmecha09@gmail.com>
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

add_filter( 'plugin_row_meta', 'potpesa_row_meta', 10, 2 );
function potpesa_row_meta( $links, $file )
{
  $plugin = plugin_basename( __FILE__ );

  if ( $plugin == $file ) {
    $row_meta = array( 
      'apidocs' => '<a href="' . esc_url( 'https://developer.safaricom.co.ke/docs/' ) . '" target="_blank" aria-label="' . esc_attr__( 'MPesa API Docs ( Daraja )', 'woocommerce' ) . '">' . esc_html__( 'API docs', 'woocommerce' ) . '</a>',
      'live' => '<a href="' . esc_url( 'https://peternjeru.co.ke/safdaraja/ui/#go_live_tutorial' ) . '" target="_blank" aria-label="' . esc_attr__( 'Going live tutorial', 'woocommerce' ) . '">' . esc_html__( 'Going live tutorial', 'woocommerce' ) . '</a>'
     );

    return array_merge( $links, $row_meta );
  }

  return ( array ) $links;
}

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'potpesa_action_links' );
function potpesa_action_links( $links )
{
	return array_merge( $links, [ '<a href="'.admin_url( 'admin.php?page=potpesa' ).'">&nbsp;Configure</a>' ] );
}

add_action('wp_enqueue_scripts', 'potpesa_enqueue_script');
function potpesa_enqueue_script()
{   
    wp_enqueue_script( 'form_script', plugin_dir_url( __FILE__ ) . 'js/form.js', array('jquery'), '1.0.0', true );
}

$potpesaonfig = get_option( 'potpesa_options', array() );
$potpesaonfig['validate'] = '?mpesa_ipn_listener=validate';
$potpesaonfig['confirm'] = '?mpesa_ipn_listener=confirm';
$potpesaonfig['reconcile'] = '?mpesa_ipn_listener=reconcile';
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

add_action( 'wp_ajax_process_mc_form', 'process_mc_form' );
add_action( 'wp_ajax_nopriv_process_mc_form', 'process_mc_form' );
function process_mc_form() {
  $mconfig = get_option( 'mc_options' );
  if ( ! isset( $_POST['mc_form_nonce'] ) || ! wp_verify_nonce( $_POST['mc_form_nonce'], 'process_mc_form_nonce') 
    ) {
    exit('The form is not valid');
  }

  $response = array( 'error' => false );

  if (trim($_POST['mc-phone']) == '') {
    exit('Phone is required');
  }

    $Amount   = trim( $_POST['mc-amount'] );
    $phone    = trim( $_POST['mc-phone'] );

    $PhoneNumber = str_replace( "+", "", $phone );
    $PhoneNumber = preg_replace('/^0/', '254', $phone);

    $result =  stk_request( $PhoneNumber, $Amount, $Ref );

    if ( !isset( $result['errorMessage'] ) ) {
      $author = is_user_logged_in() ? get_current_user_id() : 1;
   
      // Insert the payment into the database
      $post_id = wp_insert_post( 
        array( 
          'post_title'  => 'Checkout',
          'post_content'  => "Response: ".$result['Message'],
          'post_status' => 'publish',
          'post_type'   => 'potpesa_payment',
          'post_author' => $author,
        ) 
      );

      update_post_meta( $post_id, '_phone', $PhoneNumber );
      update_post_meta( $post_id, '_request_id', $result['MerchantRequestID'] );
      update_post_meta( $post_id, '_amount', $Amount );
      update_post_meta( $post_id, '_receipt', '' );
      update_post_meta( $post_id, '_order_status', 'on-hold' );
    }
    wp_send_json( json_decode( $result, true ) );
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
add_action( 'init', 'potpesa_reconcile' );
function potpesa_reconcile()
{
  if ( ! isset( $_GET['mc_ipn_listener'] ) ){ return; }
  if ( $_GET['mc_ipn_listener'] !== 'reconcile' ){ return; }

  $response = json_decode( file_get_contents( 'php://input' ), true );
  stk_reconcile( 'cb_reconcile', $response );
}

function cb_reconcile( $response )
{
  $resultCode                 = $response['Body']['stkCallback']['ResultCode'];
  $resultDesc                 = $response['Body']['stkCallback']['ResultDesc'];
  $merchantRequestID          = $response['Body']['stkCallback']['MerchantRequestID'];
  $checkoutRequestID          = $response['Body']['stkCallback']['CheckoutRequestID'];

  $post = get_post_id_by_meta_key_and_value( '_request_id', $merchantRequestID );
  wp_update_post( [ 'post_content' => file_get_contents( 'php://input' ), 'ID' => $post ] );

  if( isset( $response['Body']['stkCallback']['CallbackMetadata'] ) ){
    $amount             = $response['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
    $mpesaReceiptNumber       = $response['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
    $balance            = $response['Body']['stkCallback']['CallbackMetadata']['Item'][2]['Value'];
    $transactionDate        = $response['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
    $phone              = $response['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

    update_post_meta( $post, '_order_status', 'complete' );
    update_post_meta( $post, '_amount', $amount );
    update_post_meta( $post, '_phone', $phone );
    update_post_meta( $post, '_receipt', $mpesaReceiptNumber );
  }
}