// Function to get URL parameter from the current script's URL
function shieldclimbcryptogateway_getScriptParameter(name) {
    let shieldclimbcryptogateway_scripts = document.getElementsByTagName('script');
    for (let shieldclimbcryptogateway_script of shieldclimbcryptogateway_scripts) {
        if (shieldclimbcryptogateway_script.src.includes('shieldclimbcryptogateway-payment-status-check.js')) {
            let shieldclimbcryptogateway_params = new URL(shieldclimbcryptogateway_script.src).searchParams;
            return shieldclimbcryptogateway_params.get(name);
        }
    }
    return null;
}

jQuery(document).ready(function($) {
    function shieldclimbcryptogateway_payment_status() {
        let shieldclimbcryptogateway_order_id = shieldclimbcryptogateway_getScriptParameter('order_id');
        let shieldclimbcryptogateway_nonce = shieldclimbcryptogateway_getScriptParameter('nonce');
        let shieldclimbcryptogateway_tickerstring = shieldclimbcryptogateway_getScriptParameter('tickerstring');

        $.ajax({
            url: '/wp-json/shieldclimbcryptogateway/v1/shieldclimbcryptogateway-check-order-status-' + shieldclimbcryptogateway_tickerstring + '/',
            method: 'GET',
            data: {
                order_id: shieldclimbcryptogateway_order_id,
                nonce: shieldclimbcryptogateway_nonce
            },
            success: function(response) {
                if (response.status === 'processing' || response.status === 'completed') {
                    $('#shieldclimb-payment-status-message').text('Payment received')
                        .removeClass('shieldclimbcryptogateway-unpaid')
                        .addClass('shieldclimbcryptogateway-paid');
                    $('#shieldclimbcryptogateway-wrapper').remove();
                } else if (response.status === 'failed') {
                    $('#shieldclimb-payment-status-message').text('Payment failed, you may have sent incorrect amount or token. Contact support')
                        .removeClass('shieldclimbcryptogateway-unpaid')
                        .addClass('shieldclimbcryptogateway-failed');
                    $('#shieldclimbcryptogateway-wrapper').remove();
                } else {
                    $('#shieldclimb-payment-status-message').text('Waiting for payment');
                }
            },
            error: function() {
                $('#shieldclimb-payment-status-message').text('Error checking payment status. Please refresh the page.');
            }
        });
    }

    setInterval(shieldclimbcryptogateway_payment_status, 60000);
});
