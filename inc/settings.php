<?php
/**
 * @package Potpesa
 * @subpackage Admin Settings Page
 * @author Mauko Maunde <mauko@osen.co.ke>
 * @author Brightone Mwasaru <bmwasaru@gmail.com>
 * @author Johnes Mecha <jmecha09@gmail.com>
 * @version 1.8
 * @since 1.8
 * @license See LICENSE
 */

add_action('admin_menu', 'potpesa_options_page');
function potpesa_options_page()
{
  add_menu_page(
    'Configure Potpesa',
    'Configure Potpesa',
    'manage_options',
    'potpesa',
    'potpesa_options_page_html',
    'dashicons-smiley',
    20
  );
}
add_action( 'admin_init', 'potpesa_settings_init' );
function potpesa_settings_init() {
    register_setting( 'potpesa', 'potpesa_options' );
    
    add_settings_section( 'potpesa_section_mpesa', __( 'MPesa Settings Configuration.', 'potpesa' ), 'potpesa_section_potpesa_mpesa_cb', 'potpesa' );

    add_settings_field(
        'env',
        __( 'Environment', 'potpesa' ),
        'potpesa_fields_env_cb',
        'potpesa',
        'potpesa_section_mpesa',
        [
        'label_for' => 'env',
        'class' => 'potpesa_row',
        'potpesa_custom_data' => 'custom',
        ]
    );
    
    add_settings_field(
        'type',
        __( 'Identifier Type', 'potpesa' ),
        'potpesa_fields_potpesa_mpesa_cb',
        'potpesa',
        'potpesa_section_mpesa',
        [
        'label_for' => 'type',
        'class' => 'potpesa_row',
        'potpesa_custom_data' => 'custom',
        ]
    );
    
    add_settings_field(
        'shortcode',
        __( 'Mpesa Shortcode', 'potpesa' ),
        'potpesa_fields_potpesa_mpesa_shortcode_cb',
        'potpesa',
        'potpesa_section_mpesa',
        [
        'label_for' => 'shortcode',
        'class' => 'potpesa_row',
        'potpesa_custom_data' => 'custom',
        ]
    );
    
    add_settings_field(
        'appkey',
        __( 'App Consumer Key', 'potpesa' ),
        'potpesa_fields_potpesa_mpesa_ck_cb',
        'potpesa',
        'potpesa_section_mpesa',
        [
        'label_for' => 'appkey',
        'class' => 'potpesa_row',
        'potpesa_custom_data' => 'custom',
        ]
    );

    add_settings_field(
        'appsecret',
        __( 'App Consumer Secret', 'potpesa' ),
        'potpesa_fields_potpesa_mpesa_cs_cb',
        'potpesa',
        'potpesa_section_mpesa',
        [
        'label_for' => 'appsecret',
        'class' => 'potpesa_row',
        'potpesa_custom_data' => 'custom',
        ]
    );
    
    add_settings_field(
        'passkey',
        __( 'Online Passkey', 'potpesa' ),
        'potpesa_fields_potpesa_mpesa_pk_cb',
        'potpesa',
        'potpesa_section_mpesa',
        [
        'label_for' => 'passkey',
        'class' => 'potpesa_row',
        'potpesa_custom_data' => 'custom',
        ]
    );
    
}

function potpesa_section_potpesa_mpesa_cb( $args ) {
    $options = get_option( 'potpesa_options', ['env'=>'sandbox'] ); ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <h4 style="color: red;">IMPORTANT!</h4><li>Please <a href="https://developer.safaricom.co.ke/" target="_blank" >create an app on Daraja</a> if you haven't. Fill in the app's consumer key and secret below.</li><li>For security purposes, and for the MPesa Instant Payment Notification to work, ensure your site is running over https(SSL).</li>
        <li>You can <a href="https://developer.safaricom.co.ke/test_credentials" target="_blank" >generate sandbox test credentials here</a>.</li>
        <li>Click here to <a href="<?php echo home_url( '/?mpesa_ipn_register='.esc_attr( $options['env'] ) ); ?>" target="_blank">register confirmation & validation URLs for <?php echo esc_attr( $options['env'] ); ?> </a></li>
    </p>
    <p id="<?php echo esc_attr( $args['id'] ); ?>-config"><?php esc_html_e( 'After configuring everything, create a post or page and use the following shortcode to render the form: ', 'potpesa' ); ?><code>[POTPAYER]</code></p>
    <?php
}

function potpesa_fields_env_cb( $args ) {
    $options = get_option( 'potpesa_options' );
    ?>
    <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
    data-custom="<?php echo esc_attr( $args['potpesa_custom_data'] ); ?>"
    name="potpesa_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
    >
        <option value="sandbox" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'sandbox', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Sandbox', 'potpesa' ); ?>
        </option>
        <option value="live" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'live', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'Live', 'potpesa' ); ?>
        </option>
    </select>
    <p class="description">
    <?php esc_html_e( 'Environment', 'potpesa' ); ?>
    </p>
    <?php
}

function potpesa_fields_potpesa_mpesa_shortcode_cb( $args ) {
    $options = get_option( 'potpesa_options' );
    ?>
    <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
    data-custom="<?php echo esc_attr( $args['potpesa_custom_data'] ); ?>"
    name="potpesa_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
    value="<?php echo esc_attr( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?>"
    class="regular-text"
    >
    <p class="description">
    <?php esc_html_e( 'Paybill/Till or phone number', 'potpesa' ); ?>
    </p>
    <?php
}

function potpesa_fields_potpesa_mpesa_cb( $args ) {
    $options = get_option( 'potpesa_options' );
    ?>
    <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
    data-custom="<?php echo esc_attr( $args['potpesa_custom_data'] ); ?>"
    name="potpesa_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
    >
    <option value="1" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1', false ) ) : ( '' ); ?>>
    <?php esc_html_e( 'MSISDN', 'potpesa' ); ?>
    </option>
    <option value="2" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '2', false ) ) : ( '' ); ?>>
    <?php esc_html_e( 'Till Number', 'potpesa' ); ?>
    </option>
    <option value="4" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '4', false ) ) : ( '' ); ?>>
    <?php esc_html_e( 'Shortcode', 'potpesa' ); ?>
    </option>
    </select>
    <p class="description">
    <?php esc_html_e( 'Business identifier type', 'potpesa' ); ?>
    </p>
    <?php
}

function potpesa_fields_potpesa_mpesa_ck_cb( $args ) {
    $options = get_option( 'potpesa_options' );
    ?>
    <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
    data-custom="<?php echo esc_attr( $args['potpesa_custom_data'] ); ?>"
    name="potpesa_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
    value="<?php echo esc_attr( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?>"
    class="regular-text"
    >
    <p class="description">
    <?php esc_html_e( 'Daraja application consumer key.', 'potpesa' ); ?>
    </p>
    <?php
}

function potpesa_fields_potpesa_mpesa_cs_cb( $args ) {
    $options = get_option( 'potpesa_options' );
    ?>
    <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
    data-custom="<?php echo esc_attr( $args['potpesa_custom_data'] ); ?>"
    name="potpesa_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
    value="<?php echo esc_attr( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?>"
    class="regular-text"
    >
    <p class="description">
    <?php esc_html_e( 'Daraja application consumer secret', 'potpesa' ); ?>
    </p>
    <?php
}

function potpesa_fields_potpesa_mpesa_pk_cb( $args ) {
    $options = get_option( 'potpesa_options' );
    ?>
    <textarea id="<?php echo esc_attr( $args['label_for'] ); ?>" 
        name='potpesa_options[<?php echo esc_attr( $args['label_for'] ); ?>]' 
        rows='1' 
        cols='50' 
        type='textarea'
        class="large-text code"><?php echo esc_attr( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
    <p class="description">
    <?php esc_html_e( 'Online Pass Key', 'potpesa' ); ?>
    </p>
    <?php
}
 
/**
 * top level menu:
 * callback functions
 */
function potpesa_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
    return;
    }
    
    // add error/update messages
    
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
    // add settings saved message with the class of "updated"
    add_settings_error( 'potpesa_messages', 'potpesa_message', __( 'Potpesa Settings Updated', 'potpesa' ), 'updated' );
    }
    
    // show error/update messages
    settings_errors( 'potpesa_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "potpesa"
            settings_fields( 'potpesa' );
            // output setting sections and their fields
            // (sections are registered for "potpesa", each field is registered to a specific section)
            do_settings_sections( 'potpesa' );
            // output save settings button
            submit_button( 'Save Potpesa Settings' );
            ?>
        </form>
    </div>
    <?php
}