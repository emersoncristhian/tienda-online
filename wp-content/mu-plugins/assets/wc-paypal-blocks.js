(function() {
    'use strict';
    var wc = window.wc || {};
    var wp = window.wp || {};

    if (!wc.wcBlocksRegistry || !wc.wcSettings || !wp.element) {
        return;
    }

    var registry = wc.wcBlocksRegistry;
    var settings = wc.wcSettings;
    var wpElement = wp.element;
    var htmlEntities = wp.htmlEntities || { decodeEntities: function(s) { return s; } };

    var data = settings.getPaymentMethodData('paypal_peru', {});
    var rawTitle = htmlEntities.decodeEntities(data.title || '') || 'PayPal (Tarjetas Internacionales / Saldo)';

    var LabelComponent = function(props) {
        var PaymentMethodLabel = (props && props.components) ? props.components.PaymentMethodLabel : null;
        var badge = wpElement.createElement('span', {
            style: {
                backgroundColor: '#003087',
                color: '#ffffff',
                fontWeight: '800',
                fontSize: '11px',
                padding: '2px 8px',
                borderRadius: '10px',
                marginRight: '8px',
                letterSpacing: '0.5px',
                display: 'inline-block',
                verticalAlign: 'middle'
            }
        }, 'PayPal');

        if (PaymentMethodLabel) {
            return wpElement.createElement('span', { style: { display: 'inline-flex', alignItems: 'center' } },
                badge,
                wpElement.createElement(PaymentMethodLabel, { text: rawTitle })
            );
        }
        return wpElement.createElement('span', null, badge, rawTitle);
    };

    var ContentComponent = function() {
        var htmlContent = data.descriptionHtml || data.description || '';
        return wpElement.createElement(wpElement.RawHTML, null, htmlContent);
    };

    registry.registerPaymentMethod({
        name: 'paypal_peru',
        label: wpElement.createElement(LabelComponent, null),
        content: wpElement.createElement(ContentComponent, null),
        edit: wpElement.createElement(ContentComponent, null),
        canMakePayment: function() { return true; },
        ariaLabel: rawTitle,
        supports: {
            features: data.supports || ['products']
        }
    });
})();
