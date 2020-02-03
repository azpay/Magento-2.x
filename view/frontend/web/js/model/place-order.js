define(
    [
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery', 'jquery/ui',
        'Magento_Checkout/js/checkout-data',
        'Magento_Ui/js/model/messageList'
    ],
    function (storage, errorProcessor, fullScreenLoader,$,checkoutSession, globalMessageList) {
        'use strict';

        return function (serviceUrl, payload, messageContainer) {
            fullScreenLoader.startLoader();

            messageContainer = messageContainer || globalMessageList;
          
            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    $(".items.payment-methods .error").remove();
                    console.log(response);
                   if(!response.responseJSON){
                    $('<div class="error" style="background: #ffd3d3;padding: 10px;color: #535050;margin-bottom: 10px;">Payment Capture Error</div>').prependTo(".items.payment-methods");
                       messageContainer.addErrorMessage({'message': 'Payment Capture error'});

                   }else{
                        $('<div class="error" style="background: #ffd3d3;padding: 10px;color: #535050;margin-bottom: 10px;">'+response.responseJSON.message+'</div>').prependTo(".items.payment-methods");
                        //messageContainer.addErrorMessage({'message': response.responseJSON.message});
                        //errorProcessor.process(response, messageContainer);
                   }

                   //Limpar dados
                    $('input[name="payment[ps_cc_owner]"]').val('');       
                    $('input[name="payment[ps_cc_exp_month]"]').val('');
                    $('input[name="payment[ps_cc_exp_year]"]').val('');
                    $('input[name="payment[ps_cc_number]"]').val('');
                    $("#rm_gateway_cc_cc_number_visible").val('');
                    $('input[name="payment[ps_cc_cid]"]').val('');        
                    $("#rm_gateway_cc_cc_year_visible").val('');
                    $('select[name="payment[ps_cc_installments]"]').val(1);

                    $('#rm_gateway_cd_cd_number_visible').val('');
                    $('input[name="payment[ps_cd_owner]"]').val('');       
                    $('input[name="payment[ps_cd_exp_month]"]').val('');
                    $('input[name="payment[ps_cd_exp_year]"]').val('');
                    $('input[name="payment[ps_cd_number]"]').val('');
                    $('input[name="payment[ps_cd_cid]"]').val('');        

                   $(document).scrollTop(0);
                   fullScreenLoader.stopLoader();


                }
            );
        };
    }
);