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
        'shieldclimb-adabep20' => array('min' => 1.67, 'coin_id' => 'cardano'),
        'shieldclimb-arbarbitrum' => array('min' => 0.835, 'coin_id' => 'arbitrum'),
        'shieldclimb-arberc20' => array('min' => 25.049999999999997, 'coin_id' => 'arbitrum'),
        'shieldclimb-avaxavaxc' => array('min' => 0.0167, 'coin_id' => 'avalanche-2'),
        'shieldclimb-avaxpolygon' => array('min' => 0.0668, 'coin_id' => 'avalanche-2'),
        'shieldclimb-bch' => array('min' => 0.000835, 'coin_id' => 'bitcoin-cash'),
        'shieldclimb-bnbbep20' => array('min' => 0.00167, 'coin_id' => 'binancecoin'),
        'shieldclimb-bnberc20' => array('min' => 0.1503, 'coin_id' => 'binancecoin'),
        'shieldclimb-btc' => array('min' => 0.0001336, 'coin_id' => 'bitcoin'),
        'shieldclimb-btcbbep20' => array('min' => 0.000167, 'coin_id' => 'binance-bitcoin'),
        'shieldclimb-btctrc20' => array('min' => 0.0001336, 'coin_id' => 'bitcoin'),
        'shieldclimb-cakebep20' => array('min' => 0.167, 'coin_id' => 'pancakeswap-token'),
        'shieldclimb-cbbtcbase' => array('min' => 0.0000501, 'coin_id' => 'coinbase-wrapped-btc'),
        'shieldclimb-cbbtcerc20' => array('min' => 0.0001503, 'coin_id' => 'coinbase-wrapped-btc'),
        'shieldclimb-cbbtcsol' => array('min' => 0.00001503, 'coin_id' => 'coinbase-wrapped-btc'),
        'shieldclimb-daiarbitrum' => array('min' => 0.835, 'coin_id' => 'dai'),
        'shieldclimb-daibase' => array('min' => 5.01, 'coin_id' => 'dai'),
        'shieldclimb-daibep20' => array('min' => 1.67, 'coin_id' => 'dai'),
        'shieldclimb-daierc20' => array('min' => 25.049999999999997, 'coin_id' => 'dai'),
        'shieldclimb-daioptimism' => array('min' => 0.835, 'coin_id' => 'dai'),
        'shieldclimb-doge' => array('min' => 16.7, 'coin_id' => 'dogecoin'),
        'shieldclimb-dogebep20' => array('min' => 16.7, 'coin_id' => 'dogecoin'),
        'shieldclimb-eth' => array('min' => 0.002505, 'coin_id' => 'ethereum'),
        'shieldclimb-etharbitrum' => array('min' => 0.000167, 'coin_id' => 'ethereum'),
        'shieldclimb-ethbase' => array('min' => 0.0005009999999999999, 'coin_id' => 'ethereum'),
        'shieldclimb-ethbep20' => array('min' => 0.00167, 'coin_id' => 'ethereum'),
        'shieldclimb-ethoptimism' => array('min' => 0.000167, 'coin_id' => 'ethereum'),
        'shieldclimb-eurcsol' => array('min' => 1.67, 'coin_id' => 'euro-coin'),
        'shieldclimb-linkarbitrum' => array('min' => 0.0835, 'coin_id' => 'chainlink'),
        'shieldclimb-linkerc20' => array('min' => 1.0855, 'coin_id' => 'chainlink'),
        'shieldclimb-linkoptimism' => array('min' => 0.0835, 'coin_id' => 'chainlink'),
        'shieldclimb-ltc' => array('min' => 0.00334, 'coin_id' => 'litecoin'),
        'shieldclimb-ltcbep20' => array('min' => 0.00334, 'coin_id' => 'litecoin'),
        'shieldclimb-ondoerc20' => array('min' => 16.7, 'coin_id' => 'ondo-finance'),
        'shieldclimb-oneinchbep20' => array('min' => 1.67, 'coin_id' => '1inch'),
        'shieldclimb-oneincherc20' => array('min' => 125.25, 'coin_id' => '1inch'),
        'shieldclimb-opoptimism' => array('min' => 0.501, 'coin_id' => 'optimism'),
        'shieldclimb-pepearbitrum' => array('min' => 167000, 'coin_id' => 'pepe'),
        'shieldclimb-pepeerc20' => array('min' => 1670000, 'coin_id' => 'pepe'),
        'shieldclimb-polerc20' => array('min' => 41.75, 'coin_id' => 'polygon-ecosystem-token'),
        'shieldclimb-polpolygon' => array('min' => 0.835, 'coin_id' => 'polygon-ecosystem-token'),
        'shieldclimb-pyusdarbitrum' => array('min' => 1.67, 'coin_id' => 'paypal-usd'),
        'shieldclimb-pyusderc20' => array('min' => 8.35, 'coin_id' => 'paypal-usd'),
        'shieldclimb-pyusdsol' => array('min' => 1.67, 'coin_id' => 'paypal-usd'),
        'shieldclimb-shibbep20' => array('min' => 66800, 'coin_id' => 'shiba-inu'),
        'shieldclimb-shiberc20' => array('min' => 1670000, 'coin_id' => 'shiba-inu'),
        'shieldclimb-solsol' => array('min' => 0.00668, 'coin_id' => 'solana'),
        'shieldclimb-trumpsol' => array('min' => 0.0835, 'coin_id' => 'official-trump'),
        'shieldclimb-trx' => array('min' => 16.7, 'coin_id' => 'tron'),
        'shieldclimb-tusdtrc20' => array('min' => 8.35, 'coin_id' => 'true-usd'),
        'shieldclimb-usd1bep20' => array('min' => 1.67, 'coin_id' => 'usd1-wlfi'),
        'shieldclimb-usd1erc20' => array('min' => 8.35, 'coin_id' => 'usd1-wlfi'),
        'shieldclimb-usdcarbitrum' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcavaxc' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcbase' => array('min' => 5.01, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcbep20' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcearbitrum' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdceavaxc' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdceoptimism' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcepolygon' => array('min' => 0.835, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcerc20' => array('min' => 8.35, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcoptimism' => array('min' => 1.67, 'coin_id' => 'usd-coin'),
        'shieldclimb-usdcpolygon' => array('min' => 0.835, 'coin_id' => 'usd-coin'),
        'shieldclimb-usddtrc20' => array('min' => 8.35, 'coin_id' => 'usdd'),
        'shieldclimb-usdt0arbitrum' => array('min' => 1.67, 'coin_id' => 'usdt0'),
        'shieldclimb-usdt0optimism' => array('min' => 1.67, 'coin_id' => 'usdt0'),
        'shieldclimb-usdtavaxc' => array('min' => 1.67, 'coin_id' => 'tether'),
        'shieldclimb-usdtbase' => array('min' => 5.01, 'coin_id' => 'tether'),
        'shieldclimb-usdtbep20' => array('min' => 1.67, 'coin_id' => 'tether'),
        'shieldclimb-usdterc20' => array('min' => 8.35, 'coin_id' => 'tether'),
        'shieldclimb-usdtoptimism' => array('min' => 1.67, 'coin_id' => 'tether'),
        'shieldclimb-usdtpolygon' => array('min' => 0.835, 'coin_id' => 'tether'),
        'shieldclimb-usdtsol' => array('min' => 1.67, 'coin_id' => 'tether'),
        'shieldclimb-usdttrc20' => array('min' => 16.7, 'coin_id' => 'tether'),
        'shieldclimb-wavaxavaxc' => array('min' => 0.0167, 'coin_id' => 'wrapped-avax'),
        'shieldclimb-wbtcsol' => array('min' => 0.00001503, 'coin_id' => 'wrapped-bitcoin'),
        'shieldclimb-wetheavaxc' => array('min' => 0.00167, 'coin_id' => 'weth'),
        'shieldclimb-wethpolygon' => array('min' => 0.000835, 'coin_id' => 'weth'),
        'shieldclimb-wethsol' => array('min' => 0.00501, 'coin_id' => 'weth'),
        'shieldclimb-wxrperc20' => array('min' => 10.02, 'coin_id' => 'ripple'),
        'shieldclimb-xrpbep20' => array('min' => 3.34, 'coin_id' => 'ripple')

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