( function( blocks, i18n, element, components, editor ) {
    const { registerPaymentMethod } = wc.wcBlocksRegistry;
    // Use the localized data from PHP
    const shieldclimbcryptogateways = shieldclimbcryptogatewayData || [];
    shieldclimbcryptogateways.forEach( ( shieldclimbcryptogateway ) => {
        registerPaymentMethod({
            name: shieldclimbcryptogateway.id,
            label: shieldclimbcryptogateway.label,
            ariaLabel: shieldclimbcryptogateway.label,
            content: element.createElement(
                'div',
                { className: 'shieldclimbcryptogateway-method-wrapper' },
                element.createElement( 
                    'div', 
                    { className: 'shieldclimbcryptogateway-method-label' },
                    '' + shieldclimbcryptogateway.description 
                ),
                shieldclimbcryptogateway.icon_url ? element.createElement(
                    'img', 
                    { 
                        src: shieldclimbcryptogateway.icon_url,
                        alt: shieldclimbcryptogateway.label,
                        className: 'shieldclimbcryptogateway-method-icon'
                    }
                ) : null
            ),
            edit: element.createElement(
                'div',
                { className: 'shieldclimbcryptogateway-method-wrapper' },
                element.createElement( 
                    'div', 
                    { className: 'shieldclimbcryptogateway-method-label' },
                    '' + shieldclimbcryptogateway.description 
                ),
                shieldclimbcryptogateway.icon_url ? element.createElement(
                    'img', 
                    { 
                        src: shieldclimbcryptogateway.icon_url,
                        alt: shieldclimbcryptogateway.label,
                        className: 'shieldclimbcryptogateway-method-icon'
                    }
                ) : null
            ),
            canMakePayment: () => true,
        });
    });
} )(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);