var config = {
    map: {
        '*': {
            'Magento_Checkout/js/model/place-order':'Azpay_Gateway/js/model/place-order',
            'Magento_Checkout/js/action/select-payment-method':
                'Azpay_Gateway/js/action/select-payment-method'
        }
    },
    config: {
        mixins: {
            'Azpay_Gateway/js/validation': {
                'Azpay_Gateway/js/validation-mixin': true
            }
        }
    }
};