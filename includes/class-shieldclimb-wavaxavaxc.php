<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'init_shieldclimbcryptogateway_wavaxavaxc_gateway');

function init_shieldclimbcryptogateway_wavaxavaxc_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

class shieldclimb_Crypto_Payment_Gateway_Wavaxavaxc extends WC_Payment_Gateway {

protected $wavaxavaxc_wallet_address;
protected $wavaxavaxc_blockchain_fees;
protected $wavaxavaxc_tolerance_percentage;
protected $icon_url;

    public function __construct() {
        $this->id                 = 'shieldclimb-wavaxavaxc';
        $this->icon = esc_url(plugin_dir_url(__DIR__) . 'static/wavaxavaxc.png');
        $this->method_title       = esc_html__('Wrapped AVAX avax-c  - ShieldClimb Crypto Payment Gateway (Auto-hide at checkout if below min)', 'shieldclimb-crypto-payment-gateway'); // Escaping title
        $this->method_description = esc_html__('Wrapped AVAX avax-c crypto payment gateway with instant payouts, no sign-up, no KYC, and on-site checkout, sending funds directly to your avax-c_wavax wallet.', 'shieldclimb-crypto-payment-gateway'); // Escaping description
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'));

        // Use the configured settings for redirect and icon URLs
        $this->wavaxavaxc_wallet_address = sanitize_text_field($this->get_option('wavaxavaxc_wallet_address'));
		$this->wavaxavaxc_tolerance_percentage = sanitize_text_field($this->get_option('wavaxavaxc_tolerance_percentage'));
		$this->wavaxavaxc_blockchain_fees = $this->get_option('wavaxavaxc_blockchain_fees');
        $this->icon_url     = sanitize_url($this->get_option('icon_url'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_before_thankyou', array($this, 'before_thankyou_page'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => esc_html__('Enable/Disable', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable avax-c_wavax payment gateway', 'shieldclimb-crypto-payment-gateway'), // Escaping label
                'default' => 'no',
            ),
            'title' => array(
                'title'       => esc_html__('Title', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Payment method title that users will see during checkout.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('Wrapped AVAX avax-c', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'textarea',
                'description' => esc_html__('Payment method description that users will see during checkout.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'default'     => esc_html__('Pay via crypto Wrapped AVAX avax-c avax-c_wavax', 'shieldclimb-crypto-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'wavaxavaxc_wallet_address' => array(
                'title'       => esc_html__('Wallet Address', 'shieldclimb-crypto-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert your avax-c/wavax wallet address to receive instant payouts.', 'shieldclimb-crypto-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
            'wavaxavaxc_tolerance_percentage' => array(
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
			'wavaxavaxc_blockchain_fees' => array(
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
        $shieldclimbcryptogateway_wavaxavaxc_admin_wallet_address = isset($_POST[$this->plugin_id . $this->id . '_wavaxavaxc_wallet_address']) ? sanitize_text_field( wp_unslash( $_POST[$this->plugin_id . $this->id . '_wavaxavaxc_wallet_address'])) : '';

        // Check if wallet address is empty
        if (empty($shieldclimbcryptogateway_wavaxavaxc_admin_wallet_address)) {
		WC_Admin_Settings::add_error(__('Invalid Wallet Address: Please insert a valid Wrapped AVAX avax-c wallet address.', 'shieldclimb-crypto-payment-gateway'));
            return false;
		}

        // Proceed with the default processing if validations pass
        return parent::process_admin_options();
    }
	
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $shieldclimbcryptogateway_wavaxavaxc_currency = get_woocommerce_currency();
		$shieldclimbcryptogateway_wavaxavaxc_total = $order->get_total();
		$shieldclimbcryptogateway_wavaxavaxc_nonce = wp_create_nonce( 'shieldclimbcryptogateway_wavaxavaxc_nonce_' . $order_id );
		$shieldclimbcryptogateway_wavaxavaxc_tolerance_percentage = $this->wavaxavaxc_tolerance_percentage;
		$shieldclimbcryptogateway_wavaxavaxc_callback = add_query_arg(array('order_id' => $order_id, 'nonce' => $shieldclimbcryptogateway_wavaxavaxc_nonce,), rest_url('shieldclimbcryptogateway/v1/shieldclimbcryptogateway-wavaxavaxc/'));
		$shieldclimbcryptogateway_wavaxavaxc_email = urlencode(sanitize_email($order->get_billing_email()));
		$shieldclimbcryptogateway_wavaxavaxc_status_nonce = wp_create_nonce( 'shieldclimbcryptogateway_wavaxavaxc_status_nonce_' . $shieldclimbcryptogateway_wavaxavaxc_email );

		
$shieldclimbcryptogateway_wavaxavaxc_response = wp_remote_get('https://api.shieldclimb.com/crypto/avax-c/wavax/convert.php?value=' . $shieldclimbcryptogateway_wavaxavaxc_total . '&from=' . strtolower($shieldclimbcryptogateway_wavaxavaxc_currency), array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_wavaxavaxc_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to failed currency conversion process, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_wavaxavaxc_body = wp_remote_retrieve_body($shieldclimbcryptogateway_wavaxavaxc_response);
$shieldclimbcryptogateway_wavaxavaxc_conversion_resp = json_decode($shieldclimbcryptogateway_wavaxavaxc_body, true);

if ($shieldclimbcryptogateway_wavaxavaxc_conversion_resp && isset($shieldclimbcryptogateway_wavaxavaxc_conversion_resp['value_coin'])) {
    // Escape output
    $shieldclimbcryptogateway_wavaxavaxc_final_total	= sanitize_text_field($shieldclimbcryptogateway_wavaxavaxc_conversion_resp['value_coin']);
    $shieldclimbcryptogateway_wavaxavaxc_reference_total = (float)$shieldclimbcryptogateway_wavaxavaxc_final_total;	
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (unsupported store currency)', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}
		
		if ($this->wavaxavaxc_blockchain_fees === 'yes') {
			
			// Get the estimated feed for our crypto coin in USD fiat currency
			
		$shieldclimbcryptogateway_wavaxavaxc_feesest_response = wp_remote_get('https://api.shieldclimb.com/crypto/avax-c/wavax/aff-fees.php', array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_wavaxavaxc_feesest_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Failed to get estimated fees, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_wavaxavaxc_feesest_body = wp_remote_retrieve_body($shieldclimbcryptogateway_wavaxavaxc_feesest_response);
$shieldclimbcryptogateway_wavaxavaxc_feesest_conversion_resp = json_decode($shieldclimbcryptogateway_wavaxavaxc_feesest_body, true);

if ($shieldclimbcryptogateway_wavaxavaxc_feesest_conversion_resp && isset($shieldclimbcryptogateway_wavaxavaxc_feesest_conversion_resp['estimated_cost_currency']['USD'])) {
    // Escape output
    $shieldclimbcryptogateway_wavaxavaxc_feesest_final_total = sanitize_text_field($shieldclimbcryptogateway_wavaxavaxc_feesest_conversion_resp['estimated_cost_currency']['USD']);
    $shieldclimbcryptogateway_wavaxavaxc_feesest_reference_total = (float)$shieldclimbcryptogateway_wavaxavaxc_feesest_final_total;	
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Failed to get estimated fees, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}

// Convert the estimated fee back to our crypto

$shieldclimbcryptogateway_wavaxavaxc_revfeesest_response = wp_remote_get('https://api.shieldclimb.com/crypto/avax-c/wavax/convert.php?value=' . $shieldclimbcryptogateway_wavaxavaxc_feesest_reference_total . '&from=usd', array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_wavaxavaxc_revfeesest_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to failed currency conversion process, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_wavaxavaxc_revfeesest_body = wp_remote_retrieve_body($shieldclimbcryptogateway_wavaxavaxc_revfeesest_response);
$shieldclimbcryptogateway_wavaxavaxc_revfeesest_conversion_resp = json_decode($shieldclimbcryptogateway_wavaxavaxc_revfeesest_body, true);

if ($shieldclimbcryptogateway_wavaxavaxc_revfeesest_conversion_resp && isset($shieldclimbcryptogateway_wavaxavaxc_revfeesest_conversion_resp['value_coin'])) {
    // Escape output
    $shieldclimbcryptogateway_wavaxavaxc_revfeesest_final_total = sanitize_text_field($shieldclimbcryptogateway_wavaxavaxc_revfeesest_conversion_resp['value_coin']);
    $shieldclimbcryptogateway_wavaxavaxc_revfeesest_reference_total = (float)$shieldclimbcryptogateway_wavaxavaxc_revfeesest_final_total;
	// Calculating order total after adding the blockchain fees
	$shieldclimbcryptogateway_wavaxavaxc_payin_total = $shieldclimbcryptogateway_wavaxavaxc_reference_total + $shieldclimbcryptogateway_wavaxavaxc_revfeesest_reference_total;
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (unsupported store currency)', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}
		
		} else {
			
		$shieldclimbcryptogateway_wavaxavaxc_payin_total = $shieldclimbcryptogateway_wavaxavaxc_reference_total;	

		}
		
$shieldclimbcryptogateway_wavaxavaxc_response_minimum = wp_remote_get('https://api.shieldclimb.com/crypto/avax-c/wavax/aff-info.php', array('timeout' => 30));
if (is_wp_error($shieldclimbcryptogateway_wavaxavaxc_response_minimum)) {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to failed minimum retrieval process, please try again', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {
    $shieldclimbcryptogateway_wavaxavaxc_body_minimum = wp_remote_retrieve_body($shieldclimbcryptogateway_wavaxavaxc_response_minimum);
    $shieldclimbcryptogateway_wavaxavaxc_conversion_resp_minimum = json_decode($shieldclimbcryptogateway_wavaxavaxc_body_minimum, true);
    if ($shieldclimbcryptogateway_wavaxavaxc_conversion_resp_minimum && isset($shieldclimbcryptogateway_wavaxavaxc_conversion_resp_minimum['minimum'])) {
        $shieldclimbcryptogateway_wavaxavaxc_final_minimum = sanitize_text_field($shieldclimbcryptogateway_wavaxavaxc_conversion_resp_minimum['minimum']);
        if ($shieldclimbcryptogateway_wavaxavaxc_payin_total < $shieldclimbcryptogateway_wavaxavaxc_final_minimum) {
            shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed because the coin amount is below the minimum required', 'shieldclimb-crypto-payment-gateway'), 'error');
            return null;
        }
    } else {
        shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed, please try again (failed to fetch minimum coin amount)', 'shieldclimb-crypto-payment-gateway'), 'error');
        return null;
    }
}
$shieldclimbcryptogateway_wavaxavaxc_gen_wallet = wp_remote_get('https://api.shieldclimb.com/crypto/avax-c/wavax/wallet.php?address=' . $this->wavaxavaxc_wallet_address .'&callback=' . urlencode($shieldclimbcryptogateway_wavaxavaxc_callback), array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_wavaxavaxc_gen_wallet)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Wallet error:', 'shieldclimb-crypto-payment-gateway') . __('Payment could not be processed due to incorrect payout wallet settings, please contact website admin', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {
	$shieldclimbcryptogateway_wavaxavaxc_wallet_body = wp_remote_retrieve_body($shieldclimbcryptogateway_wavaxavaxc_gen_wallet);
	$shieldclimbcryptogateway_wavaxavaxc_wallet_decbody = json_decode($shieldclimbcryptogateway_wavaxavaxc_wallet_body, true);

 // Check if decoding was successful
    if ($shieldclimbcryptogateway_wavaxavaxc_wallet_decbody && isset($shieldclimbcryptogateway_wavaxavaxc_wallet_decbody['address_in'])) {
		// Store and sanitize variables
        $shieldclimbcryptogateway_wavaxavaxc_gen_addressIn = wp_kses_post($shieldclimbcryptogateway_wavaxavaxc_wallet_decbody['address_in']);
        $shieldclimbcryptogateway_wavaxavaxc_gen_ipntoken = wp_kses_post($shieldclimbcryptogateway_wavaxavaxc_wallet_decbody['ipn_token']);
		$shieldclimbcryptogateway_wavaxavaxc_gen_callback = sanitize_url($shieldclimbcryptogateway_wavaxavaxc_wallet_decbody['callback_url']);
        
		// Generate QR code Image
		$shieldclimbcryptogateway_wavaxavaxc_genqrcode_response = wp_remote_get('https://api.shieldclimb.com/crypto/avax-c/wavax/qrcode.php?address=' . $shieldclimbcryptogateway_wavaxavaxc_gen_addressIn, array('timeout' => 30));

if (is_wp_error($shieldclimbcryptogateway_wavaxavaxc_genqrcode_response)) {
    // Handle error
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Unable to generate QR code', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbcryptogateway_wavaxavaxc_genqrcode_body = wp_remote_retrieve_body($shieldclimbcryptogateway_wavaxavaxc_genqrcode_response);
$shieldclimbcryptogateway_wavaxavaxc_genqrcode_conversion_resp = json_decode($shieldclimbcryptogateway_wavaxavaxc_genqrcode_body, true);

if ($shieldclimbcryptogateway_wavaxavaxc_genqrcode_conversion_resp && isset($shieldclimbcryptogateway_wavaxavaxc_genqrcode_conversion_resp['qr_code'])) {
    
    $shieldclimbcryptogateway_wavaxavaxc_genqrcode_pngimg = wp_kses_post($shieldclimbcryptogateway_wavaxavaxc_genqrcode_conversion_resp['qr_code']);	
	
} else {
    shieldclimbcryptogateway_add_notice(__('Payment error:', 'shieldclimb-crypto-payment-gateway') . __('Unable to generate QR code', 'shieldclimb-crypto-payment-gateway'), 'error');
    return null;
}	
		}
		
		
		// Save $wavaxavaxcresponse in order meta data
    $order->add_meta_data('shieldclimb_wavaxavaxc_payin_address', $shieldclimbcryptogateway_wavaxavaxc_gen_addressIn, true);
    $order->add_meta_data('shieldclimb_wavaxavaxc_ipntoken', $shieldclimbcryptogateway_wavaxavaxc_gen_ipntoken, true);
    $order->add_meta_data('shieldclimb_wavaxavaxc_callback', $shieldclimbcryptogateway_wavaxavaxc_gen_callback, true);
	$order->add_meta_data('shieldclimb_wavaxavaxc_payin_amount', $shieldclimbcryptogateway_wavaxavaxc_payin_total, true);
	$order->add_meta_data('shieldclimb_wavaxavaxc_tolerance_percentage', $shieldclimbcryptogateway_wavaxavaxc_tolerance_percentage, true);
	$order->add_meta_data('shieldclimb_wavaxavaxc_qrcode', $shieldclimbcryptogateway_wavaxavaxc_genqrcode_pngimg, true);
	$order->add_meta_data('shieldclimb_wavaxavaxc_nonce', $shieldclimbcryptogateway_wavaxavaxc_nonce, true);
	$order->add_meta_data('shieldclimb_wavaxavaxc_status_nonce', $shieldclimbcryptogateway_wavaxavaxc_status_nonce, true);
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
    $shieldclimbgateway_crypto_total = $order->get_meta('shieldclimb_wavaxavaxc_payin_amount', true);
    $shieldclimbgateway__crypto_wallet_address = $order->get_meta('shieldclimb_wavaxavaxc_payin_address', true);
    $shieldclimbgateway_crypto_qrcode = $order->get_meta('shieldclimb_wavaxavaxc_qrcode', true);
	$shieldclimbgateway_crypto_qrcode_status_nonce = $order->get_meta('shieldclimb_wavaxavaxc_status_nonce', true);

    // CSS
	wp_enqueue_style('shieldclimbcryptogateway-wavaxavaxc-loader-css', plugin_dir_url( __DIR__ ) . 'static/payment-status.css', array(), '1.0.0');

    // Title
    echo '<div id="shieldclimbcryptogateway-wrapper"><h1 style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">'
        . esc_html__('Please Complete Your Payment', 'shieldclimb-crypto-payment-gateway') 
        . '</h1>';

    // QR Code Image
    echo '<div style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '"><img class="' . esc_attr('shieldclimbqrcodeimg') . '" style="' . esc_attr('text-align:center;max-width:80%;margin:0 auto;') . '" src="data:image/png;base64,' 
        . esc_attr($shieldclimbgateway_crypto_qrcode) . '" alt="' . esc_attr('avax-c/wavax Payment Address') . '"/></div>';

    // Payment Instructions
	/* translators: 1: Amount of cryptocurrency to be sent, 2: Name of the cryptocurrency */
    echo '<p style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">' . sprintf( esc_html__('Please send %1$s %2$s to the following address:', 'shieldclimb-crypto-payment-gateway'), '<br><strong>' . esc_html($shieldclimbgateway_crypto_total) . '</strong>', esc_html__('avax-c/wavax', 'shieldclimb-crypto-payment-gateway') ) . '</p>';


    // Wallet Address
    echo '<p style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">'
        . '<strong>' . esc_html($shieldclimbgateway__crypto_wallet_address) . '</strong>'
        . '</p><br><hr></div>';
		
	echo '<div class="' . esc_attr('shieldclimbcryptogateway-unpaid') . '" id="' . esc_attr('shieldclimb-payment-status-message') . '" style="' . esc_attr('text-align:center;max-width:100%;margin:0 auto;') . '">'
                . esc_html__('Waiting for payment', 'shieldclimb-crypto-payment-gateway')
                . '</div><br><hr><br>';	

  // Enqueue jQuery and the external script
    wp_enqueue_script('jquery');
    wp_enqueue_script('shieldclimbcryptogateway-check-status', plugin_dir_url(__DIR__) . 'assets/js/shieldclimbcryptogateway-payment-status-check.js?order_id=' . esc_attr($order_id) . '&nonce=' . esc_attr($shieldclimbgateway_crypto_qrcode_status_nonce) . '&tickerstring=wavaxavaxc', array('jquery'), '1.0.0', true);

}



public function shieldclimb_crypto_payment_gateway_get_icon_url() {
        return !empty($this->icon) ? esc_url($this->icon) : '';
    }
}

function shieldclimbcryptogateway_add_instant_payment_gateway_wavaxavaxc($gateways) {
    $gateways[] = 'shieldclimb_Crypto_Payment_Gateway_Wavaxavaxc';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'shieldclimbcryptogateway_add_instant_payment_gateway_wavaxavaxc');
}

// Add custom endpoint for reading crypto payment status

   function shieldclimbcryptogateway_wavaxavaxc_check_order_status_rest_endpoint() {
        register_rest_route('shieldclimbcryptogateway/v1', '/shieldclimbcryptogateway-check-order-status-wavaxavaxc/', array(
            'methods'  => 'GET',
            'callback' => 'shieldclimbcryptogateway_wavaxavaxc_check_order_status_callback',
            'permission_callback' => '__return_true',
        ));
    }

    add_action('rest_api_init', 'shieldclimbcryptogateway_wavaxavaxc_check_order_status_rest_endpoint');

    function shieldclimbcryptogateway_wavaxavaxc_check_order_status_callback($request) {
        $order_id = absint($request->get_param('order_id'));
		$shieldclimbcryptogateway_wavaxavaxc_live_status_nonce = sanitize_text_field($request->get_param('nonce'));

        if (empty($order_id)) {
            return new WP_Error('missing_order_id', __('Order ID parameter is missing.', 'shieldclimb-crypto-payment-gateway'), array('status' => 400));
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return new WP_Error('invalid_order', __('Invalid order ID.', 'shieldclimb-crypto-payment-gateway'), array('status' => 404));
        }
		
		// Verify stored status nonce

        if ( empty( $shieldclimbcryptogateway_wavaxavaxc_live_status_nonce ) || $order->get_meta('shieldclimb_wavaxavaxc_status_nonce', true) !== $shieldclimbcryptogateway_wavaxavaxc_live_status_nonce ) {
        return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 403 ) );
    }
        return array('status' => $order->get_status());
    }

// Add custom endpoint for changing order status
function shieldclimbcryptogateway_wavaxavaxc_change_order_status_rest_endpoint() {
    // Register custom route
    register_rest_route( 'shieldclimbcryptogateway/v1', '/shieldclimbcryptogateway-wavaxavaxc/', array(
        'methods'  => 'GET',
        'callback' => 'shieldclimbcryptogateway_wavaxavaxc_change_order_status_callback',
        'permission_callback' => '__return_true',
    ));
}
add_action( 'rest_api_init', 'shieldclimbcryptogateway_wavaxavaxc_change_order_status_rest_endpoint' );

// Callback function to change order status
function shieldclimbcryptogateway_wavaxavaxc_change_order_status_callback( $request ) {
    $order_id = absint($request->get_param( 'order_id' ));
	$shieldclimbcryptogateway_wavaxavaxcgetnonce = sanitize_text_field($request->get_param( 'nonce' ));
	$shieldclimbcryptogateway_wavaxavaxcpaid_value_coin = sanitize_text_field($request->get_param('value_coin'));
	$shieldclimbcryptogateway_wavaxavaxc_paid_coin_name = sanitize_text_field($request->get_param('coin'));
	$shieldclimbcryptogateway_wavaxavaxc_paid_txid_in = sanitize_text_field($request->get_param('txid_in'));

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
    if ( empty( $shieldclimbcryptogateway_wavaxavaxcgetnonce ) || $order->get_meta('shieldclimb_wavaxavaxc_nonce', true) !== $shieldclimbcryptogateway_wavaxavaxcgetnonce ) {
        return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 403 ) );
    }

    // Check if the order is pending and payment method is 'shieldclimb-wavaxavaxc'
    if ( $order && !in_array($order->get_status(), ['processing', 'completed'], true) && 'shieldclimb-wavaxavaxc' === $order->get_payment_method() ) {
		
		// Get the expected amount and coin
	$shieldclimbcryptogateway_wavaxavaxcexpected_amount = $order->get_meta('shieldclimb_wavaxavaxc_payin_amount', true) * $order->get_meta('shieldclimb_wavaxavaxc_tolerance_percentage', true);

	
		if ( $shieldclimbcryptogateway_wavaxavaxcpaid_value_coin < $shieldclimbcryptogateway_wavaxavaxcexpected_amount || $shieldclimbcryptogateway_wavaxavaxc_paid_coin_name !== 'avax-c_wavax') {
			// Mark the order as failed and add an order note
/* translators: 1: Paid value in coin, 2: Paid coin name, 3: Expected amount, 4: Transaction ID */			
$order->update_status('failed', sprintf(__( '[Order Failed] Customer sent %1$s %2$s instead of %3$s avax-c_wavax. TXID: %4$s', 'shieldclimb-crypto-payment-gateway' ), $shieldclimbcryptogateway_wavaxavaxcpaid_value_coin, $shieldclimbcryptogateway_wavaxavaxc_paid_coin_name, $shieldclimbcryptogateway_wavaxavaxcexpected_amount, $shieldclimbcryptogateway_wavaxavaxc_paid_txid_in));
/* translators: 1: Paid value in coin, 2: Paid coin name, 3: Expected amount, 4: Transaction ID */
$order->add_order_note(sprintf( __( '[Order Failed] Customer sent %1$s %2$s instead of %3$s avax-c_wavax. TXID: %4$s', 'shieldclimb-crypto-payment-gateway' ), $shieldclimbcryptogateway_wavaxavaxcpaid_value_coin, $shieldclimbcryptogateway_wavaxavaxc_paid_coin_name, $shieldclimbcryptogateway_wavaxavaxcexpected_amount, $shieldclimbcryptogateway_wavaxavaxc_paid_txid_in));
            return array( 'message' => 'Order status changed to failed due to partial payment or incorrect coin. Please check order notes' );
			
		} else {
        // Change order status to processing
		$order->payment_complete();


// Return success response
/* translators: 1: Paid value in coin, 2: Paid coin name, 3: Transaction ID */
$order->add_order_note(sprintf( __( '[Payment completed] Customer sent %1$s %2$s TXID:%3$s', 'shieldclimb-crypto-payment-gateway' ), $shieldclimbcryptogateway_wavaxavaxcpaid_value_coin, $shieldclimbcryptogateway_wavaxavaxc_paid_coin_name, $shieldclimbcryptogateway_wavaxavaxc_paid_txid_in));
        return array( 'message' => 'Payment confirmed and order status changed.' );
		}
    } else {
        // Return error response if conditions are not met
        return new WP_Error( 'order_not_eligible', __( 'Order is not eligible for status change.', 'shieldclimb-crypto-payment-gateway' ), array( 'status' => 400 ) );
    }
}
?>