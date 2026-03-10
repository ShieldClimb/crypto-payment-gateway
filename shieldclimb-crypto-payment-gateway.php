<?php
/**
 * Plugin Name: ShieldClimb – Crypto Payment Gateway for WooCommerce
 * Plugin URI: https://shieldclimb.com/shieldclimb-crypto-payment-gateway/
 * Description: Crypto Payment Gateway with instant payouts—accept cryptocurrency with no registration, no KYC, and no delays. Your crypto, your control (For setting up go to > Woocommerce > Setting > Payments tab).
 * Version: 1.0.6
 * Requires Plugins: woocommerce
 * Requires at least: 5.8
 * Tested up to: 6.9
 * WC requires at least: 5.8
 * WC tested up to: 10.5.3
 * Requires PHP: 7.2
 * Author: shieldclimb.com
 * Author URI: https://shieldclimb.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

/**
 * Enqueue block assets for the gateway.
 */
function shieldclimbcryptogateway_enqueue_block_assets() {
    // Fetch all enabled WooCommerce payment gateways
    $shieldclimbcryptogateway_available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    $shieldclimbcryptogateway_gateways_data = array();

    foreach ($shieldclimbcryptogateway_available_gateways as $gateway_id => $gateway) {
		if (strpos($gateway_id, 'shieldclimb') === 0) {
        $icon_url = method_exists($gateway, 'shieldclimb_crypto_payment_gateway_get_icon_url') ? $gateway->shieldclimb_crypto_payment_gateway_get_icon_url() : '';
        $shieldclimbcryptogateway_gateways_data[] = array(
            'id' => sanitize_key($gateway_id),
            'label' => sanitize_text_field($gateway->get_title()),
            'description' => wp_kses_post($gateway->get_description()),
            'icon_url' => sanitize_url($icon_url),
        );
		}
    }

    wp_enqueue_script(
        'shieldclimbcryptogateway-block-support',
        plugin_dir_url(__FILE__) . 'assets/js/shieldclimbcryptogateway-block-checkout-support.js',
        array('wc-blocks-registry', 'wp-element', 'wp-i18n', 'wp-components', 'wp-blocks', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/shieldclimbcryptogateway-block-checkout-support.js'),
        true
    );

    // Localize script with gateway data
    wp_localize_script(
        'shieldclimbcryptogateway-block-support',
        'shieldclimbcryptogatewayData',
        $shieldclimbcryptogateway_gateways_data
    );
}
add_action('enqueue_block_assets', 'shieldclimbcryptogateway_enqueue_block_assets');

/**
 * Enqueue styles for the gateway on checkout page.
 */
function shieldclimbcryptogateway_enqueue_styles() {
    if (is_checkout()) {
        wp_enqueue_style(
            'shieldclimbcryptogateway-styles',
            plugin_dir_url(__FILE__) . 'assets/css/shieldclimbcryptogateway-payment-gateway-styles.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/shieldclimbcryptogateway-payment-gateway-styles.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'shieldclimbcryptogateway_enqueue_styles');

		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-btc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-bch.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-ltc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-doge.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-oneinchbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-adabep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-bnbbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-btcbbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-cakebep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-daibep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-dogebep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-ethbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-ltcbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-phptbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-shibbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usd1bep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdtbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-xrpbep20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-oneincherc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-arberc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-bnberc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-daierc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-linkerc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-pepeerc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-shiberc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-tusdtrc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcerc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdterc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-cbbtcerc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-ondoerc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-polerc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usd1erc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-wxrperc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-arbarbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-daiarbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-etharbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-linkarbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-pepearbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcarbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcearbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-pyusdarbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdt0arbitrum.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-avaxpolygon.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-polpolygon.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcpolygon.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcepolygon.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdtpolygon.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-wethpolygon.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-avaxavaxc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcavaxc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdceavaxc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdtavaxc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-wavaxavaxc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-wetheavaxc.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-daibase.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-ethbase.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcbase.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-cbbtcbase.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdtbase.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-daioptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-ethoptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-linkoptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-opoptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcoptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdceoptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdtoptimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdt0optimism.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-eth.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-btctrc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-inrttrc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdttrc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usddtrc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-trx.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-pyusderc20.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-solsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdcsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-usdtsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-eurcsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-wbtcsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-wethsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-cbbtcsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-pyusdsol.php'); // Include the payment gateway class
		include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-trumpsol.php'); // Include the payment gateway class

	// Conditional function that check if Checkout page use Checkout Blocks
function shieldclimbcryptogateway_is_checkout_block() {
    return WC_Blocks_Utils::has_block_in_page( wc_get_page_id('checkout'), 'woocommerce/checkout' );
}

function shieldclimbcryptogateway_add_notice($shieldclimbcryptogateway_message, $shieldclimbcryptogateway_notice_type = 'error') {
    // Check if the Checkout page is using Checkout Blocks
    if (shieldclimbcryptogateway_is_checkout_block()) {
        // For blocks, throw a WooCommerce exception
        if ($shieldclimbcryptogateway_notice_type === 'error') {
            throw new \WC_Data_Exception('checkout_error', esc_html($shieldclimbcryptogateway_message)); 
        }
        // Handle other notice types if needed
    } else {
        // Default WooCommerce behavior
        wc_add_notice(esc_html($shieldclimbcryptogateway_message), $shieldclimbcryptogateway_notice_type); 
    }
}		

include_once(plugin_dir_path(__FILE__) . 'includes/shieldclimb-crypto-payment-functions.php'); 

?>