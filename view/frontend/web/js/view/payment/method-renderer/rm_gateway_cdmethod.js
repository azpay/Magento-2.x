define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Azpay_Gateway/payment/rm_gateway_cd'
            },

            getCode: function() {
                return 'rm_gateway_cd';
            },

            isActive: function() {
                return true;
            },
            validate: function() {
                var $form = $('#' + this.getCode() + '-form');                
                return $form.validation() && $form.validation('isValid');
            },

            limitvalue: function(data, e) {
                if($(e.currentTarget).val().length == 2 && e.key!=8) {
                    return false;
                }
                return true;
            },

            numbervalidation: function(data, e) {
                var charCode = (e.which) ? e.which : e.keyCode
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                }
                return true;
            },
            lettervalidation: function(data, e) {
                var charCode = (e.which) ? e.which : e.keyCode
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                }
                return true;
            }    
        });
    }
);