<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'shieldclimbcryptogateway_btc_gateway');

function shieldclimbcryptogateway_btc_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

class shieldclimb_Crypto_Payment_Gateway_Btc extends WC_Payment_Gateway {

protected $btc_wallet_address;
protected $btc_blockchain_fees;
protected $btc_tolerance_percentage;
protected $icon_url;

    public function __construct() {
        $this->id                 = 'shieldclimb-crypto-payment-gateway-btc';
        $this->icon = esc_url(plugin_dir_url(__DIR__) . 'static/btc.png');
        $this->method_title       = esc_html__('ShieldClimb – Crypto Payment Gateway (Bitcoin | Min 0.0001336btc | Auto-hide if below min)', 'shieldclimb-crypto-payment-gateway'); // Escaping title
        $this->method_description = esc_html__('Accept Bitcoin payments directly to your wallet—no KYB or KYC required.', 'shieldclimb-crypto-payment-gateway'); // Escaping description
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'));

        // Use the configured settings for redirect and icon URLs
        $this->btc_wallet_address = sanitize_text_field($this->get_option('btc_wallet_address'));
		$this->btc_tolerance_percentage = sanitize_text_field($this->get_option('btc_tolerance_percentage'));
		$this->btc_blockchain_fees = $this->get_option('btc_blockchain_fees');
        $this->icon_url     = sanitize_url($this->get_option('icon_url'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_before_thankyou', array($this, 'before_thankyou_page'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => esc_html__('Enable/Disable', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable btc payment gateway', 'shieldclimb-crypto-payment-gateway'), // Escaping label
                'default' => 'no',
            ),
            'title' => array(
                'title'       => esc_html__('Title', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Payment method title that users will see during checkout.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('Bitcoin', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'textarea',
                'description' => esc_html__('Payment method description that users will see during checkout.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('Pay via crypto Bitcoin btc', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'btc_wallet_address' => array(
                'title'       => esc_html__('Wallet Address', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert your btc wallet address to receive instant payouts.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
            'btc_tolerance_percentage' => array(
                'title'       => esc_html__('Underpaid Tolerance', 'shieldclimb-crypto-payment-gateway'),
                'type'        => 'select',
                'description' => esc_html__('Select percentage to tolerate underpayment when a customer sends less crypto than the required amount.', 'shieldclimb-crypto-payment-gateway'),
                'desc_tip'    => true,
                'default'     => '1',
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
			'btc_blockchain_fees' => array(
                'title'       => esc_html__('Customer Pays Blockchain Fees', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'checkbox',
                'description' => esc_html__('Add estimated blockchian fees to the order total.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
				'default' => 'no',
            ),
        );
    }
	
	 // Add this method to validate the wallet address in wp-admin
    public function process_admin_options() {
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'woocommerce-settings')) {
    WC_Admin_Settings::add_error(__('Nonce verification failed. Please try again.', 'shieldclimb-crypto-payment-gateway'));
    return false;
}
        $shieldclimbcryptogateway_btc_admin_wallet_address = isset($_POST[$this->plugin_id . $this->id . '_btc_wallet_address']) ? sanitize_text_field( wp_unslash( $_POST[$this->plugin_id . $this->id . '_btc_wallet_address'])) : '';

        // Check if wallet address is empty
        if (empty($shieldclimbcryptogateway_btc_admin_wallet_address)) {
		WC_Admin_Settings::add_error(__('Invalid Wallet Address: Please insert a valid Bitcoin wallet address.', 'shieldclimb-crypto-payment-gateway'));
            return false;
		}

        // Proceed with the default processing if validations pass
        return parent::process_admin_options();
    }
	
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $shieldclimbcryptogateway_btc_currency = get_woocommerce_currency();
		$shieldclimbcryptogateway_btc_total = $order->get_total();
		$shieldclimbcryptogateway_btc_nonce = wp_create_nonce( 'shieldclimbcryptogateway_btc_nonce_' . $order_id );
		$shieldclimbcryptogateway_btc_tolerance_percentage = $this->btc_tolerance_percentage;
		$shieldclimbcryptogateway_btc_callback = add_query_arg(array('order_id' => $order_id, 'nonce' => $shieldclimbcryptogateway_btc_nonce,), rest_url('shieldclimbcryptogateway/v1/shieldclimbcryptogateway-btc/'));
		$shieldclimbcryptogateway_btc_email = urlencode(sanitize_email($order->get_billing_email()));
		$shieldclimbcryptogateway_btc_status_nonce = wp_create_nonce( 'shieldclimbcryptogateway_btc_status_nonce_' . $shieldclimbcryptogateway_btc_email );

		
$shieldclimbcryptogateway_btc_response = wp_remote_get('https://apicrypto.shieldclimb.com/crypto/btc/convert.php?value=' . $shieldclimbcryptogateway_btc_total . '&from=' . strtolower($shieldclimbcryptogateway_btc_currency), array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_btc_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to failed currency conversion process, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_btc_body = wp_remote_retrieve_body($shieldclimbcryptogateway_btc_response);
$shieldclimbcryptogateway_btc_conversion_resp = json_decode($shieldclimbcryptogateway_btc_body, true);

if ($shieldclimbcryptogateway_btc_conversion_resp && isset($shieldclimbcryptogateway_btc_conversion_resp['value_coin'])) {
    // Escape output
    $shieldclimbcryptogateway_btc_final_total	= sanitize_text_field($shieldclimbcryptogateway_btc_conversion_resp['value_coin']);
    $shieldclimbcryptogateway_btc_reference_total = (float)$shieldclimbcryptogateway_btc_final_total;	
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (unsupported store currency)', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}
		
		if ($this->btc_blockchain_fees === 'yes') {
			
			// Get the estimated feed for our crypto coin in USD fiat currency
			
		$shieldclimbcryptogateway_btc_feesest_response = wp_remote_get('https://apicrypto.shieldclimb.com/crypto/btc/fees.php', array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_btc_feesest_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Failed to get estimated fees, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_btc_feesest_body = wp_remote_retrieve_body($shieldclimbcryptogateway_btc_feesest_response);
$shieldclimbcryptogateway_btc_feesest_conversion_resp = json_decode($shieldclimbcryptogateway_btc_feesest_body, true);

if ($shieldclimbcryptogateway_btc_feesest_conversion_resp && isset($shieldclimbcryptogateway_btc_feesest_conversion_resp['estimated_cost_currency']['USD'])) {
    // Escape output
    $shieldclimbcryptogateway_btc_feesest_final_total = sanitize_text_field($shieldclimbcryptogateway_btc_feesest_conversion_resp['estimated_cost_currency']['USD']);
    $shieldclimbcryptogateway_btc_feesest_reference_total = (float)$shieldclimbcryptogateway_btc_feesest_final_total;	
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Failed to get estimated fees, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}

// Convert the estimated fee back to our crypto

$shieldclimbcryptogateway_btc_revfeesest_response = wp_remote_get('https://apicrypto.shieldclimb.com/crypto/btc/convert.php?value=' . $shieldclimbcryptogateway_btc_feesest_reference_total . '&from=usd', array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_btc_revfeesest_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to failed currency conversion process, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_btc_revfeesest_body = wp_remote_retrieve_body($shieldclimbcryptogateway_btc_revfeesest_response);
$shieldclimbcryptogateway_btc_revfeesest_conversion_resp = json_decode($shieldclimbcryptogateway_btc_revfeesest_body, true);

if ($shieldclimbcryptogateway_btc_revfeesest_conversion_resp && isset($shieldclimbcryptogateway_btc_revfeesest_conversion_resp['value_coin'])) {
    // Escape output
    $shieldclimbcryptogateway_btc_revfeesest_final_total = sanitize_text_field($shieldclimbcryptogateway_btc_revfeesest_conversion_resp['value_coin']);
    $shieldclimbcryptogateway_btc_revfeesest_reference_total = (float)$shieldclimbcryptogateway_btc_revfeesest_final_total;
	// Calculating order total after adding the blockchain fees
	$shieldclimbcryptogateway_btc_payin_total = $shieldclimbcryptogateway_btc_reference_total + $shieldclimbcryptogateway_btc_revfeesest_reference_total;
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (unsupported store currency)', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}
		
		} else {
			
		$shieldclimbcryptogateway_btc_payin_total = $shieldclimbcryptogateway_btc_reference_total;	

		}
		
$shieldclimbcryptogateway_btc_response_minimum = wp_remote_get('https://apicrypto.shieldclimb.com/crypto/btc/info.php', array('timeout' => 30));
if (is_wp_error($shieldclimbcryptogateway_btc_response_minimum)) {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to failed minimum retrieval process, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {
    $shieldclimbcryptogateway_btc_body_minimum = wp_remote_retrieve_body($shieldclimbcryptogateway_btc_response_minimum);
    $shieldclimbcryptogateway_btc_conversion_resp_minimum = json_decode($shieldclimbcryptogateway_btc_body_minimum, true);
    if ($shieldclimbcryptogateway_btc_conversion_resp_minimum && isset($shieldclimbcryptogateway_btc_conversion_resp_minimum['minimum'])) {
        $shieldclimbcryptogateway_btc_final_minimum = sanitize_text_field($shieldclimbcryptogateway_btc_conversion_resp_minimum['minimum']);
        if ($shieldclimbcryptogateway_btc_payin_total < $shieldclimbcryptogateway_btc_final_minimum) {
            shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed because the coin amount is below the minimum required', 'shieldclimb-crypto-payment-gateway'), 'error');
            return null;
        }
    } else {
        shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (failed to fetch minimum coin amount)', 'shieldclimb-crypto-payment-gateway'), 'error');
        return null;
    }
}
$shieldclimbcryptogateway_btc_gen_wallet = wp_remote_get('https://apicrypto.shieldclimb.com/crypto/btc/wallet.php?address=' . $this->btc_wallet_address .'&callback=' . urlencode($shieldclimbcryptogateway_btc_callback), array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_btc_gen_wallet)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Wallet error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to incorrect payout wallet settings, please contact website admin', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {
	$shieldclimbcryptogateway_btc_wallet_body = wp_remote_retrieve_body($shieldclimbcryptogateway_btc_gen_wallet);
	$shieldclimbcryptogateway_btc_wallet_decbody = json_decode($shieldclimbcryptogateway_btc_wallet_body, true);

 // Check if decoding was successful
    if ($shieldclimbcryptogateway_btc_wallet_decbody && isset($shieldclimbcryptogateway_btc_wallet_decbody['address_in'])) {
		// Store and sanitize variables
        $shieldclimbcryptogateway_btc_gen_addressIn = wp_kses_post($shieldclimbcryptogateway_btc_wallet_decbody['address_in']);
        $shieldclimbcryptogateway_btc_gen_ipntoken = wp_kses_post($shieldclimbcryptogateway_btc_wallet_decbody['ipn_token']);
		$shieldclimbcryptogateway_btc_gen_callback = sanitize_url($shieldclimbcryptogateway_btc_wallet_decbody['callback_url']);
        
		// Generate QR code Image
		$shieldclimbcryptogateway_btc_genqrcode_response = wp_remote_get('https://apicrypto.shieldclimb.com/crypto/btc/qrcode.php?address=' . $shieldclimbcryptogateway_btc_gen_addressIn, array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_btc_genqrcode_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Unable to generate QR code', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_btc_genqrcode_body = wp_remote_retrieve_body($shieldclimbcryptogateway_btc_genqrcode_response);
$shieldclimbcryptogateway_btc_genqrcode_conversion_resp = json_decode($shieldclimbcryptogateway_btc_genqrcode_body, true);

if ($shieldclimbcryptogateway_btc_genqrcode_conversion_resp && isset($shieldclimbcryptogateway_btc_genqrcode_conversion_resp['qr_code'])) {
    
    $shieldclimbcryptogateway_btc_genqrcode_pngimg = wp_kses_post($shieldclimbcryptogateway_btc_genqrcode_conversion_resp['qr_code']);	
	
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Unable to generate QR code', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}
		
		
		// Save $btcresponse in order meta data
    $order->add_meta_data('shieldclimb_btc_payin_address', $shieldclimbcryptogateway_btc_gen_addressIn, true);
    $order->add_meta_data('shieldclimb_btc_ipntoken', $shieldclimbcryptogateway_btc_gen_ipntoken, true);
    $order->add_meta_data('shieldclimb_btc_callback', $shieldclimbcryptogateway_btc_gen_callback, true);
	$order->add_meta_data('shieldclimb_btc_payin_amount', $shieldclimbcryptogateway_btc_payin_total, true);
	$order->add_meta_data('shieldclimb_btc_tolerance_percentage', $shieldclimbcryptogateway_btc_tolerance_percentage, true);
	$order->add_meta_data('shieldclimb_btc_qrcode', $shieldclimbcryptogateway_btc_genqrcode_pngimg, true);
	$order->add_meta_data('shieldclimb_btc_nonce', $shieldclimbcryptogateway_btc_nonce, true);
	$order->add_meta_data('shieldclimb_btc_status_nonce', $shieldclimbcryptogateway_btc_status_nonce, true);
    $order->save();
    } else {
        shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (wallet address error)', 'shieldclimb-crypto-payment-gateway'), 'error');

        return null;
    }
}

        // Redirect to payment page
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

// Show payment instructions on thankyou page
public function before_thankyou_page($order_id) {
    $order = wc_get_order($order_id);
	// Check if this is the correct payment method
    if ($order->get_payment_method() !== $this->id) {
        return;
    }
    $shieldclimbgateway_crypto_total = $order->get_meta('shieldclimb_btc_payin_amount', true);
    $shieldclimbgateway__crypto_wallet_address = $order->get_meta('shieldclimb_btc_payin_address', true);
    $shieldclimbgateway_crypto_qrcode = $order->get_meta('shieldclimb_btc_qrcode', true);
	$shieldclimbgateway_crypto_qrcode_status_nonce = $order->get_meta('shieldclimb_btc_status_nonce', true);

    // CSS
	wp_enqueue_style('shieldclimbcryptogateway-btc-loader-css', plugin_dir_url( __DIR__ ) . 'static/payment-status.css', array(), '1.0.0');

    // Title
    echo '<div id="shieldclimbcryptogateway-wrapper"><h1 style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">'
        . esc_html__('Please Complete Your Payment', 'shieldclimb-crypto-payment-gateway') 
        . '</h1>';

    // QR Code Image
    echo '<div style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '"><img class="' . esc_attr('shieldclimbqrcodeimg') . '" style="' . esc_attr('text-align:center;max-width:80%;margin:0 auto;') . '" src="data:image/png;base64,' 
        . esc_attr($shieldclimbgateway_crypto_qrcode) . '" alt="' . esc_attr('btc Payment Address') . '"/></div>';

    // Payment Instructions
	/* translators: 1: Amount of cryptocurrency to be sent, 2: Name of the cryptocurrency */
    echo '<p style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">' . sprintf( esc_html__('Please send %1$s %2$s to the following address:', 'shieldclimb-crypto-payment-gateway'), '<br><strong>' . esc_html($shieldclimbgateway_crypto_total) . '</strong>', esc_html__('btc', 'shieldclimb-crypto-payment-gateway') ) . '</p>';


    // Wallet Address
    echo '<p style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">'
        . '<strong>' . esc_html($shieldclimbgateway__crypto_wallet_address) . '</strong>'
        . '</p><br><hr></div>';
		
	echo '<div class="' . esc_attr('shieldclimbcryptogateway-unpaid') . '" id="' . esc_attr('shieldclimb-payment-status-message') . '" style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">'
                . esc_html__('Waiting for payment', 'shieldclimb-crypto-payment-gateway')
                . '</div><br><hr><br>';	

  // Enqueue jQuery and the external script
    wp_enqueue_script('jquery');
    wp_enqueue_script('shieldclimbcryptogateway-check-status', plugin_dir_url(__DIR__) . 'assets/js/shieldclimbcryptogateway-payment-status-check.js?order_id=' . esc_attr($order_id) . '&nonce=' . esc_attr($shieldclimbgateway_crypto_qrcode_status_nonce) . '&tickerstring=btc', array('jquery'), '1.0.0', true);

}



public function shieldclimb_crypto_payment_gateway_get_icon_url() {
        return !empty($this->icon) ? esc_url($this->icon) : '';
    }
}

function shieldclimb_add_instant_payment_gateway_btc($gateways) {
    $gateways[] = 'shieldclimb_Crypto_Payment_Gateway_Btc';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'shieldclimb_add_instant_payment_gateway_btc');
}

// REST API ENDPOINTS SETUP
// Register custom endpoints
add_action('rest_api_init', function() {
    // Order status check endpoint
    register_rest_route('shieldclimbcryptogateway/v1', '/check-order-status-btc/', array(
        'methods'  => 'GET',
        'callback' => 'shieldclimbcryptogateway_check_order_status_callback',
        'permission_callback' => 'shieldclimbcryptogateway_check_status_permission'
    ));

    // Order status change endpoint
    register_rest_route('shieldclimbcryptogateway/v1', '/update-order-status-btc/', array(
        'methods'  => 'GET',
        'callback' => 'shieldclimbcryptogateway_update_order_status_callback',
        'permission_callback' => 'shieldclimbcryptogateway_update_status_permission'
    ));
});

// PERMISSION CALLBACKS
function shieldclimbcryptogateway_check_status_permission($request) {
    $order_id = absint($request->get_param('order_id'));
    $nonce = sanitize_text_field($request->get_param('nonce'));

    // Basic parameter check
    if (!$order_id || !$nonce) return false;

    // Order existence check
    $order = wc_get_order($order_id);
    if (!$order) return false;

    // Nonce validation
    return $order->get_meta('shieldclimb_btc_status_nonce', true) === $nonce;
}

function shieldclimbcryptogateway_update_status_permission($request) {
    $order_id = absint($request->get_param('order_id'));
    $nonce = sanitize_text_field($request->get_param('nonce'));

    // Basic parameter check
    if (!$order_id || !$nonce) return false;

    // Order existence check
    $order = wc_get_order($order_id);
    if (!$order) return false;

    // Nonce validation
    return $order->get_meta('shieldclimb_btc_nonce', true) === $nonce;
}

// CORE FUNCTIONALITY
function shieldclimbcryptogateway_check_order_status_callback($request) {
    $order_id = absint($request->get_param('order_id'));
    $order = wc_get_order($order_id);

    // Secondary safety check
    if (!$order) {
        return new WP_Error(
            'invalid_order',
            __('Order not found.', 'shieldclimb-crypto-payment-gateway'),
            array('status' => 404)
        );
    }

    return rest_ensure_response(array(
        'status' => $order->get_status(),
        'currency' => $order->get_currency(),
        'total' => $order->get_total()
    ));
}

function shieldclimbcryptogateway_update_order_status_callback($request) {
    $order_id = absint($request->get_param('order_id'));
    $order = wc_get_order($order_id);

    // Parameters
    $paid_value = (float)sanitize_text_field($request->get_param('value_coin'));
    $coin_type = sanitize_text_field($request->get_param('coin'));
    $txid = sanitize_text_field($request->get_param('txid_in'));

    // Secondary safety check
    if (!$order) {
        return new WP_Error(
            'invalid_order',
            __('Order not found.', 'shieldclimb-crypto-payment-gateway'),
            array('status' => 404)
        );
    }

    // Verify payment method
    if ('shieldclimb-crypto-payment-gateway-btc' !== $order->get_payment_method()) {
        return new WP_Error(
            'invalid_payment_method',
            __('Invalid payment method for this order.', 'shieldclimb-crypto-payment-gateway'),
            array('status' => 400)
        );
    }

    // Check if already processed
    if (in_array($order->get_status(), ['processing', 'completed'], true)) {
        return new WP_Error(
            'order_already_processed',
            __('Order has already been processed.', 'shieldclimb-crypto-payment-gateway'),
            array('status' => 409)
        );
    }

    // Get expected values
    $expected_amount = (float)$order->get_meta('shieldclimb_btc_payin_amount', true);
    $tolerance = (float)$order->get_meta('shieldclimb_btc_tolerance_percentage', true);
    $minimum_required = $expected_amount * $tolerance;

    // Payment validation
    if ($coin_type !== 'btc' || $paid_value < $minimum_required) {
        /* translators: 1: Paid value in coin, 2: Paid coin name, 3: Expected amount, 4: Transaction ID */
        $order->update_status('failed', sprintf(
            __('Incomplete payment: Received %1$s %2$s (Minimum required: %3$s) | TXID: %4$s', 'shieldclimb-crypto-payment-gateway'),
            number_format($paid_value, 6),
            strtoupper($coin_type), 
            number_format($minimum_required, 6),
            $txid
        ));
        
        return rest_ensure_response(array(
            'status' => 'failed',
            'message' => 'Order status changed to failed due to partial payment or incorrect coin. Please check order notes'
        ));
    }

    // Process successful payment
    $order->payment_complete();
    /* translators: 1: Paid value in coin, 2: Paid coin name, 3: Transaction ID */
    $order->add_order_note(sprintf(
        __('Crypto payment received: %1$s %2$s | TXID: %3$s', 'shieldclimb-crypto-payment-gateway'),
        number_format($paid_value, 6),
        strtoupper($coin_type),
        $txid
    ));

    return rest_ensure_response(array(
        'status' => 'success',
        'order_status' => $order->get_status(),
        'processed_amount' => $paid_value,
        'transaction_id' => $txid
    ));
}
?>