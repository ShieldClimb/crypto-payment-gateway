<?php
add_filter('woocommerce_available_payment_gateways', 'shieldclimbcryptogateway_hide_crypto_payment_methods');
function shieldclimbcryptogateway_hide_crypto_payment_methods($available_gateways) {
    // Run only on checkout or order pay page
    if (!(is_checkout() || is_checkout_pay_page())) {
        return $available_gateways;
    }
    
    // Initialize variables
    $cart_total = 0;
    $currency = 'USD';

    // Check if we're on the order pay page
    if (is_checkout_pay_page()) {
        global $wp;
        $order_id = absint($wp->query_vars['order-pay']);
        $order = wc_get_order($order_id);
        if (!$order) {
            return $available_gateways;
        }
        $cart_total = $order->get_total();
        $currency = $order->get_currency();
    } else {
        // Regular checkout page
        if (is_null(WC()->cart)) {
            return $available_gateways;
        }
        $cart_total = WC()->cart->total;
        $currency = get_woocommerce_currency();
    }

    // Convert cart total to USD if necessary
    if ($currency !== 'USD') {
        $cart_total = shieldclimbcryptogateway_convert_currency_to_usd($cart_total, $currency);
    }

    // Define minimum crypto amounts and their CoinGecko IDs
    $gateway_minimums = array(
        'shieldclimb-crypto-payment-gateway-adabep20' => array('min' => 1.33, 'coin_id' => 'cardano'),
        'shieldclimb-crypto-payment-gateway-bch' => array('min' => 0.000665, 'coin_id' => 'bitcoin-cash'),
        'shieldclimb-crypto-payment-gateway-bnbbep20' => array('min' => 0.00133, 'coin_id' => 'binancecoin'),
        'shieldclimb-crypto-payment-gateway-btc' => array('min' => 0.0001064, 'coin_id' => 'bitcoin'),
        'shieldclimb-crypto-payment-gateway-cakebep20' => array('min' => 0.133, 'coin_id' => 'pancakeswap-token'),    
        'shieldclimb-crypto-payment-gateway-daibep20' => array('min' => 1.33, 'coin_id' => 'dai'),
        'shieldclimb-crypto-payment-gateway-doge' => array('min' => 13.3, 'coin_id' => 'dogecoin'),
        'shieldclimb-crypto-payment-gateway-dogebep20' => array('min' => 13.3, 'coin_id' => 'dogecoin'),
        'shieldclimb-crypto-payment-gateway-eth' => array('min' => 0.005985, 'coin_id' => 'ethereum'),
        'shieldclimb-crypto-payment-gateway-ethbep20' => array('min' => 0.00133, 'coin_id' => 'ethereum'),
        'shieldclimb-crypto-payment-gateway-ethoptimism' => array('min' => 0.000133, 'coin_id' => 'ethereum'),
        'shieldclimb-crypto-payment-gateway-injbep20' => array('min' => 0.0665, 'coin_id' => 'injective-protocol'),
        'shieldclimb-crypto-payment-gateway-ltc' => array('min' => 0.00266, 'coin_id' => 'litecoin'),
        'shieldclimb-crypto-payment-gateway-ltcbep20' => array('min' => 0.00266, 'coin_id' => 'litecoin'),
        'shieldclimb-crypto-payment-gateway-oneinchbep20' => array('min' => 1.33, 'coin_id' => '1inch'),
        'shieldclimb-crypto-payment-gateway-shibbep20' => array('min' => 53200, 'coin_id' => 'shiba-inu'),
        'shieldclimb-crypto-payment-gateway-usdcbep20' => array('min' => 1.33, 'coin_id' => 'usd-coin'),
        'shieldclimb-crypto-payment-gateway-usdtbep20' => array('min' => 1.33, 'coin_id' => 'tether'),
        'shieldclimb-crypto-payment-gateway-xrpbep20' => array('min' => 2.66, 'coin_id' => 'ripple')
    );

    // Get exchange rates with caching
    $coin_ids = array_unique(array_column($gateway_minimums, 'coin_id'));
    $exchange_rates = shieldclimbcryptogateway_get_exchange_rates($coin_ids);

    foreach ($gateway_minimums as $gateway_id => $settings) {
        if (!isset($available_gateways[$gateway_id])) continue;

        $rate = $exchange_rates[$settings['coin_id']] ?? 0;
        if ($rate <= 0) {
            unset($available_gateways[$gateway_id]);
            continue;
        }

        $crypto_amount = $cart_total / $rate;
        if ($crypto_amount < $settings['min']) {
            unset($available_gateways[$gateway_id]);
        }
    }

    return $available_gateways;
}

function shieldclimbcryptogateway_convert_currency_to_usd($amount, $currency) {
    $transient_key = 'shieldclimbcryptogateway_currency_rate_' . $currency;
    $rate = get_transient($transient_key);

    if (false === $rate) {
        $response = wp_remote_get('https://api.frankfurter.dev/latest?from=' . $currency . '&to=USD');
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($data['rates']['USD'])) {
                $rate = $data['rates']['USD'];
                set_transient($transient_key, $rate, 5 * MINUTE_IN_SECONDS);
            }
        }
    }

    return ($rate > 0) ? $amount * $rate : $amount;
}

function shieldclimbcryptogateway_get_exchange_rates($coin_ids) {
    $transient_key = 'shieldclimbcryptogateway_crypto_rates_' . md5(implode(',', $coin_ids));
    $rates = get_transient($transient_key);

    if (false === $rates) {
        $api_url = add_query_arg(array(
            'ids' => implode(',', $coin_ids),
            'vs_currencies' => 'usd'
        ), 'https://api.coingecko.com/api/v3/simple/price');

        $response = wp_remote_get($api_url);
        
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $rates = array();
            foreach ($body as $coin_id => $data) {
                $rates[$coin_id] = $data['usd'] ?? 0;
            }
            set_transient($transient_key, $rates, 5 * MINUTE_IN_SECONDS);
        } else {
            $rates = array();
        }
    }

    return $rates;
}
?>