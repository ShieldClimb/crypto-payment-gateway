<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'init_shieldclimbcryptogateway_multicoin_gateway');

function init_shieldclimbcryptogateway_multicoin_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

class shieldclimb_Crypto_Payment_Gateway_Multicoin extends WC_Payment_Gateway {

protected $multicoin_wallet_address;
protected $multicoin_blockchain_fees;
protected $multicoin_tolerance_percentage;
protected $multicoin_custom_domain;
protected $icon_url;
protected $background_color;
protected $button_color;
protected $theme_color;
protected $logo_url;

    public function __construct() {
        $this->id                 = 'shieldclimb-multicoin';
        $this->icon = esc_url(plugin_dir_url(__DIR__) . 'static/multicoin.png');
        $this->method_title       = esc_html__('Multicoin  - ShieldClimb Crypto Payment Gateway (Auto-hide at checkout if below min)', 'shieldclimb-crypto-payment-gateway'); // Escaping title
        $this->method_description = esc_html__('Multicoin crypto payment gateway with instant payouts, no sign-up, no KYC, and on-site checkout, sending funds directly to your cryptocurrency wallet.', 'shieldclimb-crypto-payment-gateway'); // Escaping description
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'));
		$this->logo_url     = sanitize_url($this->get_option('logo_url'));
		$this->background_color       = sanitize_text_field($this->get_option('background_color'));
		$this->button_color       = sanitize_text_field($this->get_option('button_color'));
		$this->theme_color       = sanitize_text_field($this->get_option('theme_color'));

        // Use the configured settings for redirect and icon URLs
        $this->multicoin_wallet_address = array(
    'evm'          => sanitize_text_field($this->get_option('multicoin_wallet_evm')),
    'btc'          => sanitize_text_field($this->get_option('multicoin_wallet_btc')),
    'bitcoincash'  => sanitize_text_field($this->get_option('multicoin_wallet_bitcoincash')),
    'ltc'          => sanitize_text_field($this->get_option('multicoin_wallet_ltc')),
    'doge'         => sanitize_text_field($this->get_option('multicoin_wallet_doge')),
    'solana'       => sanitize_text_field($this->get_option('multicoin_wallet_solana')),
    'trc20'        => sanitize_text_field($this->get_option('multicoin_wallet_trc20')),
);
		$this->multicoin_tolerance_percentage = sanitize_text_field($this->get_option('multicoin_tolerance_percentage'));
		$this->multicoin_custom_domain = rtrim(str_replace(['https://','http://'], '', sanitize_text_field($this->get_option('multicoin_custom_domain'))), '/');
		$this->multicoin_blockchain_fees = $this->get_option('multicoin_blockchain_fees');
        $this->icon_url     = sanitize_url($this->get_option('icon_url'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => esc_html__('Enable/Disable', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable cryptocurrency payment gateway', 'shieldclimb-crypto-payment-gateway'), // Escaping label
                'default' => 'no',
            ),
            'title' => array(
                'title'       => esc_html__('Title', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Payment method title that users will see during checkout.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('Multicoin', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
			'multicoin_custom_domain' => array(
                'title'       => esc_html__('Custom Domain', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Follow the custom domain guide to use your own domain name for the checkout pages and links.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('payment.shieldclimb.com', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'textarea',
                'description' => esc_html__('Payment method description that users will see during checkout.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('Pay via crypto Multicoin cryptocurrency', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
'multicoin_wallet_evm' => array(
    'title'       => esc_html__('EVM Wallet Address', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your EVM-compatible wallet address (ERC20/ETH/BEP20/Polygon/Optimism/Arbitrum/Base/Avax-C).', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
'multicoin_wallet_btc' => array(
    'title'       => esc_html__('Bitcoin Wallet Address (BTC)', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your Bitcoin wallet address.', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
'multicoin_wallet_bitcoincash' => array(
    'title'       => esc_html__('Bitcoin Cash Wallet Address (BCH)', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your Bitcoin Cash wallet address.', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
'multicoin_wallet_ltc' => array(
    'title'       => esc_html__('Litecoin Wallet Address (LTC)', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your Litecoin wallet address.', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
'multicoin_wallet_doge' => array(
    'title'       => esc_html__('Dogecoin Wallet Address (DOGE)', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your Dogecoin wallet address.', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
'multicoin_wallet_solana' => array(
    'title'       => esc_html__('Solana Wallet Address (SOL)', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your Solana wallet address.', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
'multicoin_wallet_trc20' => array(
    'title'       => esc_html__('TRC20 Wallet Address (USDT-TRON)', 'shieldclimb-crypto-payment-gateway'),
    'type'        => 'text',
    'description' => esc_html__('Insert your TRC20 (USDT on Tron) wallet address.', 'shieldclimb-crypto-payment-gateway'),
    'desc_tip'    => true,
),
            'multicoin_tolerance_percentage' => array(
                'title'       => esc_html__('Underpaid Tolerance', 'shieldclimb-crypto-payment-gateway'),
                'type'        => 'select',
                'description' => esc_html__('Select percentage to tolerate underpayment when a customer sends less crypto than the required amount. Recommended is 1% or more due to volatile crypto rates.', 'shieldclimb-crypto-payment-gateway'),
                'desc_tip'    => true,
                'default'     => '0.99',
                'options'     => array(
                    '1'    => '0%',
                    '0.99' => '1%',
                    '0.98' => '2%',
                    '0.97' => '3%',
                    '0.96' => '4%',
                    '0.95' => '5%',
                    '0.94' => '6%',
                    '0.93' => '7%',
                    '0.92' => '8%',
                    '0.91' => '9%',
                    '0.90' => '10%'
                ),
            ),
			'multicoin_blockchain_fees' => array(
                'title'       => esc_html__('Customer Pays Blockchain Fees', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'checkbox',
                'description' => esc_html__('Add estimated blockchian fees to the order total.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
				'default' => 'no',
            ),
			'logo_url' => array(
                'title'       => esc_html__('Custom Logo URL', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'url',
                'description' => esc_html__('Add your own brand or website logo to the hosted checkout page.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
			'background_color' => array(
                'title'       => esc_html__('Background Color', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert HEX color code for the hosted page background color.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
			'theme_color' => array(
                'title'       => esc_html__('Theme Color', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert HEX color code for the hosted page theme color.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
			'button_color' => array(
                'title'       => esc_html__('Button Color', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert HEX color code for the hosted page pay button color.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
        );
    }
	
	 // Add this method to validate the wallet address in wp-admin
public function process_admin_options() {
    // Verify nonce
    if (
        ! isset($_POST['_wpnonce']) ||
        ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['_wpnonce'])),
            'woocommerce-settings'
        )
    ) {
        WC_Admin_Settings::add_error(
            esc_html__('Nonce verification failed. Please try again.', 'shieldclimb-crypto-payment-gateway')
        );
        return false;
    }

    $wallet_keys = [
        'multicoin_wallet_evm',
        'multicoin_wallet_btc',
        'multicoin_wallet_bitcoincash',
        'multicoin_wallet_ltc',
        'multicoin_wallet_doge',
        'multicoin_wallet_solana',
        'multicoin_wallet_trc20',
    ];

    $has_one_filled = false;

    foreach ( $wallet_keys as $key ) {
        $sanitized_key = sanitize_key($key);

        $field_name = $this->plugin_id . $this->id . '_' . $sanitized_key;

        $value = isset($_POST[$field_name])
            ? sanitize_text_field(wp_unslash($_POST[$field_name]))
            : '';

        if (! empty($value)) {
            $has_one_filled = true;
            break;
        }
    }

    if ( ! $has_one_filled ) {
        WC_Admin_Settings::add_error(
            esc_html__('Please insert at least one wallet address before saving.', 'shieldclimb-crypto-payment-gateway')
        );
        return false;
    }

    return parent::process_admin_options();
}
	
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $shieldclimbcryptogateway_multicoinmulticoin_currency = get_woocommerce_currency();
		$shieldclimbcryptogateway_multicoinmulticoin_total = $order->get_total();
		$shieldclimbcryptogateway_multicoinmulticoin_nonce = wp_create_nonce( 'shieldclimbcryptogateway_multicoin_nonce_' . $order_id );
		$shieldclimbcryptogateway_multicoinmulticoin_tolerance_percentage = $this->multicoin_tolerance_percentage;
		$shieldclimbcryptogateway_multicoinmulticoin_callback = add_query_arg(array('order_id' => $order_id, 'nonce' => $shieldclimbcryptogateway_multicoinmulticoin_nonce,), rest_url('shieldclimbcryptogateway/v1/shieldclimbcryptogateway-multicoin/'));
		$shieldclimbcryptogateway_multicoinmulticoin_email = urlencode(sanitize_email($order->get_billing_email()));
		   $shieldclimbcryptogateway_multicoindecoded_payload = array(
		'fiat_amount' => $shieldclimbcryptogateway_multicoinmulticoin_total,
		'fiat_currency' => $shieldclimbcryptogateway_multicoinmulticoin_currency,
        'callback' => $shieldclimbcryptogateway_multicoinmulticoin_callback,
            );
 if ( isset( $this->multicoin_wallet_address['evm'] ) && '' !== $this->multicoin_wallet_address['evm'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['evm'] =  $this->multicoin_wallet_address['evm'];
    }

    if ( isset( $this->multicoin_wallet_address['btc'] ) && '' !== $this->multicoin_wallet_address['btc'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['btc'] = $this->multicoin_wallet_address['btc'];
    }

    if ( isset( $this->multicoin_wallet_address['bitcoincash'] ) && '' !== $this->multicoin_wallet_address['bitcoincash'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['bitcoincash'] = $this->multicoin_wallet_address['bitcoincash'];
    }

    if ( isset( $this->multicoin_wallet_address['ltc'] ) && '' !== $this->multicoin_wallet_address['ltc'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['ltc'] = $this->multicoin_wallet_address['ltc'];
    }

    if ( isset( $this->multicoin_wallet_address['doge'] ) && '' !== $this->multicoin_wallet_address['doge'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['doge'] = $this->multicoin_wallet_address['doge'];
    }

    if ( isset( $this->multicoin_wallet_address['solana'] ) && '' !== $this->multicoin_wallet_address['solana'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['solana'] = $this->multicoin_wallet_address['solana'];
    }

    if ( isset( $this->multicoin_wallet_address['trc20'] ) && '' !== $this->multicoin_wallet_address['trc20'] ) {
        $shieldclimbcryptogateway_multicoindecoded_payload['trc20'] = $this->multicoin_wallet_address['trc20'];
    }
		
			$shieldclimbcryptogateway_multicoinjson_payload = json_encode($shieldclimbcryptogateway_multicoindecoded_payload);

		
		if ($this->multicoin_blockchain_fees === 'yes') {
			
		$shieldclimbcryptogateway_multicoinmulticoin_fees_value = '1';
		
		} else {
			
		$shieldclimbcryptogateway_multicoinmulticoin_fees_value = '0';	

		}
		
$shieldclimbcryptogateway_multicoinmulticoin_gen_wallet = wp_remote_post(
    'https://api.shieldclimb.com/crypto/multi-hosted-wallet.php',
    array(
        'timeout' => 30,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => $shieldclimbcryptogateway_multicoinjson_payload, // JSON string directly
    )
);

if (is_wp_error($shieldclimbcryptogateway_multicoinmulticoin_gen_wallet)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Wallet error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to incorrect payout wallet settings, please contact website admin', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {
	$shieldclimbcryptogateway_multicoinmulticoin_wallet_body = wp_remote_retrieve_body($shieldclimbcryptogateway_multicoinmulticoin_gen_wallet);
	$shieldclimbcryptogateway_multicoinmulticoin_wallet_decbody = json_decode($shieldclimbcryptogateway_multicoinmulticoin_wallet_body, true);

 // Check if decoding was successful
    if ($shieldclimbcryptogateway_multicoinmulticoin_wallet_decbody && isset($shieldclimbcryptogateway_multicoinmulticoin_wallet_decbody['payment_token'])) {
		// Store and sanitize variables
        $shieldclimbcryptogateway_multicoinmulticoin_gen_addressIn = wp_kses_post($shieldclimbcryptogateway_multicoinmulticoin_wallet_decbody['payment_token']);
        $shieldclimbcryptogateway_multicoinmulticoin_gen_ipntoken = wp_kses_post($shieldclimbcryptogateway_multicoinmulticoin_wallet_decbody['ipn_token']);
		$shieldclimbcryptogateway_multicoinmulticoin_gen_callback = sanitize_url($shieldclimbcryptogateway_multicoinmulticoin_wallet_decbody['callback_url']);
        
		
		
		// Save $multicoinresponse in order meta data
    $order->add_meta_data('shieldclimb_multicoin_payin_address', $shieldclimbcryptogateway_multicoinmulticoin_gen_addressIn, true);
    $order->add_meta_data('shieldclimb_multicoin_ipntoken', $shieldclimbcryptogateway_multicoinmulticoin_gen_ipntoken, true);
    $order->add_meta_data('shieldclimb_multicoin_callback', $shieldclimbcryptogateway_multicoinmulticoin_gen_callback, true);
	$order->add_meta_data('shieldclimb_multicoin_payin_amount', $shieldclimbcryptogateway_multicoinmulticoin_total, true);
	$order->add_meta_data('shieldclimb_multicoin_tolerance_percentage', $shieldclimbcryptogateway_multicoinmulticoin_tolerance_percentage, true);
	$order->add_meta_data('shieldclimb_multicoin_currency', $shieldclimbcryptogateway_multicoinmulticoin_currency, true);
	$order->add_meta_data('shieldclimb_multicoin_nonce', $shieldclimbcryptogateway_multicoinmulticoin_nonce, true);
	$order->add_meta_data('shieldclimb_multicoin_fees_value_settings', $shieldclimbcryptogateway_multicoinmulticoin_fees_value, true);
    $order->save();
    } else {
        shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (wallet address error)', 'shieldclimb-crypto-payment-gateway'), 'error');

        return null;
    }
}

        // Redirect to payment page
        return array(
            'result'   => 'success',
            'redirect' => 'https://' . $this->multicoin_custom_domain . '/crypto/hosted.php?payment_token=' . $shieldclimbcryptogateway_multicoinmulticoin_gen_addressIn . '&add_fees=' . $shieldclimbcryptogateway_multicoinmulticoin_fees_value . (isset($this->logo_url) && $this->logo_url ? '&logo=' . urlencode($this->logo_url) : '') . (isset($this->background_color) && $this->background_color ? '&background=' . urlencode($this->background_color) : '') . (isset($this->theme_color) && $this->theme_color ? '&theme=' . urlencode($this->theme_color) : '') . (isset($this->button_color) && $this->button_color ? '&button=' . urlencode($this->button_color) : ''),);
    }


public function shieldclimb_crypto_payment_gateway_get_icon_url() {
        return !empty($this->icon) ? esc_url($this->icon) : '';
    }
}

function shieldclimbcryptogateway_add_instant_payment_gateway_multicoin($gateways) {
    $gateways[] = 'shieldclimb_Crypto_Payment_Gateway_Multicoin';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'shieldclimbcryptogateway_add_instant_payment_gateway_multicoin');
}

// Add custom endpoint for reading crypto payment status

   function shieldclimbcryptogateway_multicoin_check_order_status_rest_endpoint() {
        register_rest_route('shieldclimbcryptogateway/v1', '/shieldclimbcryptogateway-check-order-status-multicoin/', array(
            'methods'  => 'GET',
            'callback' => 'shieldclimbcryptogateway_multicoin_check_order_status_callback',
            'permission_callback' => '__return_true',
        ));
    }

    add_action('rest_api_init', 'shieldclimbcryptogateway_multicoin_check_order_status_rest_endpoint');

    function shieldclimbcryptogateway_multicoin_check_order_status_callback($request) {
        $order_id = absint($request->get_param('order_id'));
		$shieldclimbcryptogateway_multicoinmulticoin_live_status_nonce = sanitize_text_field($request->get_param('nonce'));

        if (empty($order_id)) {
            return new WP_Error('missing_order_id', __('Order ID parameter is missing.', 'shieldclimb-crypto-payment-gateway'), array('status' => 400));
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return new WP_Error('invalid_order', __('Invalid order ID.', 'shieldclimb-crypto-payment-gateway'), array('status' => 404));
        }
		
		// Verify stored status nonce

        if ( empty( $shieldclimbcryptogateway_multicoinmulticoin_live_status_nonce ) || $order->get_meta('shieldclimb_multicoin_status_nonce', true) !== $shieldclimbcryptogateway_multicoinmulticoin_live_status_nonce ) {
        return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 403 ) );
    }
        return array('status' => $order->get_status());
    }

// Add custom endpoint for changing order status
function shieldclimbcryptogateway_multicoin_change_order_status_rest_endpoint() {
    // Register custom route
    register_rest_route( 'shieldclimbcryptogateway/v1', '/shieldclimbcryptogateway-multicoin/', array(
        'methods'  => 'GET',
        'callback' => 'shieldclimbcryptogateway_multicoin_change_order_status_callback',
        'permission_callback' => '__return_true',
    ));
}
add_action( 'rest_api_init', 'shieldclimbcryptogateway_multicoin_change_order_status_rest_endpoint' );

// Callback function to change order status
function shieldclimbcryptogateway_multicoin_change_order_status_callback( $request ) {
    $order_id = absint($request->get_param( 'order_id' ));
	$shieldclimbcryptogateway_multicoingetnonce = sanitize_text_field($request->get_param( 'nonce' ));
	$shieldclimbcryptogateway_multicoinpaid_value_coin = sanitize_text_field($request->get_param('value_coin'));
	$shieldclimbcryptogateway_multicoin_paid_coin_name = sanitize_text_field($request->get_param('coin'));
	$shieldclimbcryptogateway_multicoin_paid_txid_in = sanitize_text_field($request->get_param('txid_in'));

$shieldclimbcryptogateway_multicoincoin_label = str_replace( '_', '/', strtoupper( $shieldclimbcryptogateway_multicoin_paid_coin_name ) );

    // Check if order ID parameter exists
    if ( empty( $order_id ) ) {
        return new WP_Error( 'missing_order_id', __( 'Order ID parameter is missing.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 400 ) );
    }

    // Get order object
    $order = wc_get_order( $order_id );

    // Check if order exists
    if ( ! $order ) {
        return new WP_Error( 'invalid_order', __( 'Invalid order ID.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 404 ) );
    }
	
	// Verify nonce
    if ( empty( $shieldclimbcryptogateway_multicoingetnonce ) || $order->get_meta('shieldclimb_multicoin_nonce', true) !== $shieldclimbcryptogateway_multicoingetnonce ) {
        return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 403 ) );
    }

    // Check if the order is pending and payment method is 'shieldclimb-bch'
    if ( $order && !in_array($order->get_status(), ['processing', 'completed'], true) && 'shieldclimb-multicoin' === $order->get_payment_method() ) {
	$shieldclimbcryptogateway_multicoincurrency      = $order->get_meta( 'shieldclimb_multicoin_currency', true );	
 // Fetch coin pricing from ShieldClimb
    $shieldclimbcryptogateway_multicoininfo_url = 'https://api.shieldclimb.com/crypto/' . strtolower($shieldclimbcryptogateway_multicoincoin_label) . '/info.php';
    $shieldclimbcryptogateway_multicoinresponse = wp_remote_get( $shieldclimbcryptogateway_multicoininfo_url, array( 'timeout' => 30 ) );

    if ( is_wp_error( $shieldclimbcryptogateway_multicoinresponse ) ) {
        return new WP_Error(
            'shieldclimbcryptogateway_api_error',
            __( 'Failed to fetch coin data.', 'shieldclimb-crypto-payment-gateway' ),
            array( 'status' => 500 )
        );
    }

    $shieldclimbcryptogateway_multicoinbody      = wp_remote_retrieve_body( $shieldclimbcryptogateway_multicoinresponse );
    $shieldclimbcryptogateway_multicoincoin_data = json_decode( $shieldclimbcryptogateway_multicoinbody, true );

    if ( ! is_array( $shieldclimbcryptogateway_multicoincoin_data ) || ! isset( $shieldclimbcryptogateway_multicoincoin_data['prices'][ $shieldclimbcryptogateway_multicoincurrency ] ) ) {
        return new WP_Error(
            'shieldclimbcryptogateway_invalid_coin_data',
            __( 'Invalid coin data received from ShieldClimb.', 'shieldclimb-crypto-payment-gateway' ),
            array( 'status' => 500 )
        );
    }

    // Get fiat price for order currency
    $shieldclimbcryptogateway_multicoincoin_price    = floatval( $shieldclimbcryptogateway_multicoincoin_data['prices'][ $shieldclimbcryptogateway_multicoincurrency ] );

    // Convert crypto amount to fiat
    $shieldclimbcryptogateway_multicoinreceived_coin = $shieldclimbcryptogateway_multicoinpaid_value_coin;
    $shieldclimbcryptogateway_multicoinreceived_fiat = $shieldclimbcryptogateway_multicoinreceived_coin * $shieldclimbcryptogateway_multicoincoin_price;

    // Get expected fiat and tolerance
    $shieldclimbcryptogateway_multicoinexpected_fiat     = floatval( $order->get_meta( 'shieldclimb_multicoin_payin_amount', true ) );
    $shieldclimbcryptogateway_multicointolerance_percent = floatval( $order->get_meta( 'shieldclimb_multicoin_tolerance_percentage', true ) );
	$shieldclimbcryptogateway_multicoin_fee_read_settings = $order->get_meta( 'shieldclimb_multicoin_fees_value_settings', true );
    $shieldclimbcryptogateway_multicoinminimum_initial_required  = $shieldclimbcryptogateway_multicoinexpected_fiat * $shieldclimbcryptogateway_multicointolerance_percent;


if ($shieldclimbcryptogateway_multicoin_fee_read_settings === '1') {
			
		 // Fetch coin fees from ShieldClimb
    $shieldclimbcryptogateway_multicoinfeesinfo_url = 'https://api.shieldclimb.com/crypto/' . strtolower($shieldclimbcryptogateway_multicoincoin_label) . '/fees.php';
    $shieldclimbcryptogateway_multicoinfeesresponse = wp_remote_get( $shieldclimbcryptogateway_multicoinfeesinfo_url, array( 'timeout' => 30 ) );
	
	
	$shieldclimbcryptogateway_multicoinfeesbody      = wp_remote_retrieve_body( $shieldclimbcryptogateway_multicoinfeesresponse );
    $shieldclimbcryptogateway_multicoinfeescoin_data = json_decode( $shieldclimbcryptogateway_multicoinfeesbody, true );

    if ( ! is_array( $shieldclimbcryptogateway_multicoinfeescoin_data ) || ! isset( $shieldclimbcryptogateway_multicoinfeescoin_data['estimated_cost_currency'][$shieldclimbcryptogateway_multicoincurrency] ) ) {
        return new WP_Error(
            'shieldclimbcryptogateway_invalid_coin_data',
            __( 'Invalid coin fee data received from ShieldClimb.', 'shieldclimb-crypto-payment-gateway' ),
            array( 'status' => 500 )
        );
    }
	
	$shieldclimbcryptogateway_multicoinfeescoin_price    = floatval( $shieldclimbcryptogateway_multicoinfeescoin_data['estimated_cost_currency'][ $shieldclimbcryptogateway_multicoincurrency ] );
	
	$shieldclimbcryptogateway_multicoinminimum_required = $shieldclimbcryptogateway_multicoinminimum_initial_required + $shieldclimbcryptogateway_multicoinfeescoin_price;
		
		} else {
			
		$shieldclimbcryptogateway_multicoinminimum_required = $shieldclimbcryptogateway_multicoinminimum_initial_required;

		}
	
		if ( $shieldclimbcryptogateway_multicoinreceived_fiat < $shieldclimbcryptogateway_multicoinminimum_required) {
	
		// Mark the order as failed and add an order note
/* translators: 1: amount received, 2: coin ticker, 3: fiat amount received, 4: fiat currency, 5: minimum required fiat, 6: transaction ID */
$order->update_status('failed', sprintf(__( '[Order Failed] Received %1$s %2$s (~%3$.2f %4$s), required minimum: %5$.2f %4$s. TXID: %6$s', 'shieldclimb-crypto-payment-gateway' ),
        $shieldclimbcryptogateway_multicoinreceived_coin,
        esc_html( strtoupper( $shieldclimbcryptogateway_multicoin_paid_coin_name ) ),
        $shieldclimbcryptogateway_multicoinreceived_fiat,
        esc_html( $shieldclimbcryptogateway_multicoincurrency ),
        $shieldclimbcryptogateway_multicoinminimum_required,
        esc_html( $shieldclimbcryptogateway_multicoin_paid_txid_in )
    )
);

/* translators: 1: amount received, 2: coin ticker, 3: fiat amount received, 4: fiat currency, 5: minimum required fiat, 6: transaction ID */
$order->add_order_note( sprintf(__( '[Order Failed] Received %1$s %2$s (~%3$.2f %4$s), required minimum: %5$.2f %4$s. TXID: %6$s', 'shieldclimb-crypto-payment-gateway' ),
        $shieldclimbcryptogateway_multicoinreceived_coin,
        esc_html( strtoupper( $shieldclimbcryptogateway_multicoin_paid_coin_name ) ),
        $shieldclimbcryptogateway_multicoinreceived_fiat,
        esc_html( $shieldclimbcryptogateway_multicoincurrency ),
        $shieldclimbcryptogateway_multicoinminimum_required,
        esc_html( $shieldclimbcryptogateway_multicoin_paid_txid_in )
    )
);
return array( 'message' => 'Order status changed to failed due to partial payment. Please check order notes' );			
	
		} else {
        // Change order status to processing
		$order->payment_complete();


// Return success response
/* translators: 1: Paid value in coin, 2: Paid coin name, 3: Transaction ID */
$order->add_order_note(sprintf( __( '[Payment completed] Customer sent %1$s %2$s TXID:%3$s', 'shieldclimb-crypto-payment-gateway' ), $shieldclimbcryptogateway_multicoinpaid_value_coin, $shieldclimbcryptogateway_multicoin_paid_coin_name, $shieldclimbcryptogateway_multicoin_paid_txid_in));
        return array( 'message' => 'Payment confirmed and order status changed.' );
		}
    } else {
        // Return error response if conditions are not met
        return new WP_Error( 'order_not_eligible', __( 'Order is not eligible for status change.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 400 ) );
    }
}

?>