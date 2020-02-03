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
                type: 'rm_gateway_cc',
                component: 'Azpay_Gateway/js/view/payment/method-renderer/rm_gateway_ccmethod'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
