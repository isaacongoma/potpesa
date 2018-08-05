<?php
/**
 * 
 */
/* Setup CORS */
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
/**
 * 
 */
class MpesaSTK
{
  public static $env = 'sandbox';
  public static $parent;
  public static $shortcode;
  public static $type = 4;
  public static $validate;
  public static $confirm;
  public static $reconcile;
  function set( $config )
  {
    foreach ( $config as $key => $value ) {
      self::$$key = $value;
    }
  }
  /**
   * 
   */
  function token()
  {
      $endpoint = ( self::$env == 'live' ) ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
      $credentials = base64_encode( self::$appkey.':'.self::$appsecret );
      $curl = curl_init();
      curl_setopt( $curl, CURLOPT_URL, $endpoint );
      curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Basic '.$credentials ) );
      curl_setopt( $curl, CURLOPT_HEADER, false );
      curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
      $curl_response = curl_exec( $curl );
      
      return json_decode( $curl_response )->access_token;
  }
  /**
   * 
   */
  function validate( $callback )
  {
      $data = file_get_contents('php://input');
      if( is_null( $callback) ){
          return array( 
            'ResponseCode'            => 0, 
            'ResponseDesc'            => 'Success',
            'ThirdPartyTransID'       => $transID
           );
      } else {
          if ( !call_user_func_array( $callback, array( json_decode( $data, true)['Body'] ) ) ) {
              return array( 
                'ResponseCode'        => 1, 
                'ResponseDesc'        => 'Failed',
                'ThirdPartyTransID'   => $transID
               );
          } else {
          return array( 
            'ResponseCode'            => 0, 
            'ResponseDesc'            => 'Success',
            'ThirdPartyTransID'       => $transID
           );
          }
      }
  }
  /**
   * 
   */
  function confirm( $callback )
  {
    $data = file_get_contents('php://input');
    if( is_null( $callback) ){
        return array( 
          'ResponseCode'            => 0, 
          'ResponseDesc'            => 'Success',
          'ThirdPartyTransID'       => $transID
         );
    } else {
        if ( !call_user_func_array( $callback, array( json_decode( $data, true)['Body'] ) ) ) {
            return array( 
              'ResponseCode'        => 1, 
              'ResponseDesc'        => 'Failed',
              'ThirdPartyTransID'   => $transID
             );
        } else {
        return array( 
          'ResponseCode'            => 0, 
          'ResponseDesc'            => 'Success',
          'ThirdPartyTransID'       => $transID
         );
        }
    }
  }
  /**
   * 
   */
  function request( $phone, $amount, $reference, $trxdesc, $remark )
  {
    $protocol = ( ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $phone      = str_replace( "+", "", $phone );
    $phone      = preg_replace('/^0/', '254', $phone);
    $reference  = $reference;
    $endpoint   = ( self::$env == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $timestamp  = date( 'YmdHis' );
    $password   = base64_encode( self::$shortcode.self::$passkey.$timestamp );
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL, $endpoint );
    curl_setopt( $curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer '.self::token() ] );
    $curl_post_data = array( 
        'BusinessShortCode' => self::$parent,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => ( self::$type == 4 ) ? 'CustomerPayBillOnline' : 'BuyGoodsOnline',
        'Amount'            => round( $amount ),
        'PartyA'            => $phone,
        'PartyB'            => self::$shortcode,
        'PhoneNumber'       => $phone,
        'CallBackURL'       => $protocol.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.self::$reconcile,
        'AccountReference'  => $reference,
        'TransactionDesc'   => $trxdesc,
        'Remark'            => $remark
    );
    $data_string = json_encode( $curl_post_data );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_POST, true );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string );
    curl_setopt( $curl, CURLOPT_HEADER, false );
    $requested = curl_exec( $curl );
    return json_decode( $requested, true );
  }
  /**
   * 
   */          
  function reconcile( $callback )
  {
    $response = json_decode( file_get_contents( 'php://input' ), true );
    if( !isset( $response['Body'] ) ){
      return false;
    }
    $payment = $response['Body'];
    if( !isset($payment['CallbackMetadata'])){
      $data = null;
      return false;
    }
    $data = array(
      'receipt' => $payment['CallbackMetadata']['Item'][1]['Value'],
      'amount' => $payment['CallbackMetadata']['Item'][0]['Value']
    );
    if ( is_null( $callback )) {
      return true;
    } else {
      return call_user_func_array( $callback, $data );
    }
  }
  /**
   * 
   */
  function stk_register()
  {
    $protocol = ( ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $endpoint = ( self::$env == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl' : 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL, $endpoint );
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json','Authorization:Bearer '.self::token() ) );
        
    $curl_post_data = array( 
        'ShortCode'         => self::$shortcode,
        'ResponseType'      => 'Cancelled',
        'ConfirmationURL'   => $protocol.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.self::$confirm,
        'ValidationURL'     => $protocol.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.self::$validate
    );
    $data_string = json_encode( $curl_post_data );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_POST, true );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string );
    curl_setopt( $curl, CURLOPT_HEADER, false );
    return json_decode( curl_exec( $curl ), true );
  }
}
### WRAPPER FUNCTIONS
/**
 * Wrapper function to process response data for reconcilliation
 * @param Array $configuration - Key-value pairs of settings
 *   KEY        |   TYPE    |   DESCRIPTION         | POSSIBLE VALUES
 *  env         |   string  | Environment in use    | live/sandbox
 *  parent      |   number  | Head Office Shortcode | 123456
 *  shortcode   |   number  | Business Paybill/Till | 123456
 *  type        |   integer | Identifier Type       | 1(MSISDN)/2(Till)/4(Paybill)
 *  validate    |   string  | Validation URI        | lipia/validate
 *  confirm     |   string  | Confirmation URI      | lipia/confirm
 *  reconcile   |   string  | Reconciliation URI    | lipia/reconcile
 * @return bool
 */ 
function stk_config( $configuration )
{
  MpesaSTK::set( $configuration );
}
/**
 * Wrapper function to process response data for validation
 * @param String $callback - Optional callback function to process the response
 * @return bool
 */ 
function stk_validate( $callback = null )
{
  return MpesaSTK::validate( $callback );
}
/**
 * Wrapper function to process response data for confirmation
 * @param String $callback - Optional callback function to process the response
 * @return bool
 */ 
function stk_confirm( $callback = null )
{
  return MpesaSTK::confirm( $callback );
}
/**
 * Wrapper function to process request for payment
 * @param String $phone     - Phone Number to send STK Prompt Request to
 * @param String $amount    - Amount of money to charge
 * @param String $reference - Account to show in STK Prompt
 * @param String $trxdesc   - Transaction Description(optional)
 * @param String $remark    - Remarks about transaction(optional)
 * @return bool
 */ 
function stk_request( $phone, $amount, $reference, $trxdesc = 'Mpesa Payment', $remark = ' Mpesa Payment' )
{
  return MpesaSTK::request( $phone, $amount, $reference, $trxdesc, $remark );
}
/**
 * Wrapper function to process response data for reconcilliation
 * @param String $callback - Optional callback function to process the response
 * @return bool
 */          
function stk_reconcile( $callback = null )
{
  return MpesaSTK::reconcile( $callback );
}
/**
 * Wrapper function to register URLs
 */
function stk_register()
{
  return MpesaSTK::register();
}
