define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Azpay_Gateway/payment/rm_gateway_boleto'
            },

            getCode: function() {
                return 'rm_gateway_boleto';
            },

            isActive: function() {
                return true;
            }            
        });
    }
);