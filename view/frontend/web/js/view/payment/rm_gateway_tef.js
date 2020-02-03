define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'rm_gateway_tef',
                component: 'Azpay_Gateway/js/view/payment/method-renderer/rm_gateway_tefmethod'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
