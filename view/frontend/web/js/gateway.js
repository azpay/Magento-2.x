/**
 * Gateway Transparente para Magento
 * @author Azpay
 * @link 
 * @version 1.0.0
 */

function RMGateway(config) {        
        console.log('RMGateway has been initialized.');

        this.config = config;
        this.config.maxSenderHashAttempts = 30;
        var methis = this;       

        var parcelsDrop = jQuery('#rm_gateway_cc_cc_installments');        
        parcelsDrop.append('<option value="1">1</option>');
        
}

//formata de forma generica os campos
function formataCampo(campo, Mascara, evento) { 
    var boleanoMascara; 

    var Digitato = evento.keyCode;
    exp = /\:|\-|\.|\/|\(|\)| /g
    campoSoNumeros = campo.val().toString().replace( exp, "" ).replace(/[^0-9]/g, '');; 

    var posicaoCampo = 0;    
    var NovoValorCampo="";
    var TamanhoMascara = campoSoNumeros.length;
    
    if (campoSoNumeros.length == 11) return false;
    if (Digitato != 8) { // backspace 
        for(i=0; i<= TamanhoMascara; i++) { 
            boleanoMascara  = ((Mascara.charAt(i) == ":") || (Mascara.charAt(i) == "-") || (Mascara.charAt(i) == ".") || (Mascara.charAt(i) == "/")) 
            boleanoMascara  = boleanoMascara || ((Mascara.charAt(i) == "(") 
                                                    || (Mascara.charAt(i) == ")") || (Mascara.charAt(i) == " ")) 
            if (boleanoMascara) { 
                NovoValorCampo += Mascara.charAt(i); 
                TamanhoMascara++;
            }else { 
                NovoValorCampo += campoSoNumeros.charAt(posicaoCampo); 
                posicaoCampo++; 
            }              
        }      
        campo.val(NovoValorCampo);
        return true; 
    }else { 
        return true; 
    }
}

function validarCpf(value){
    value = jQuery.trim(value);

    value = value.replace('.','');
    value = value.replace('.','');
    cpf = value.replace('-','');
    
    while(cpf.length < 11) cpf = "0"+ cpf;
    var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
    var a = [];
    var b = new Number;
    var c = 11;
    for (i=0; i<11; i++){
        a[i] = cpf.charAt(i);
        if (i < 9) b += (a[i] * --c);
    }
    if ((x = b % 11) < 2) { a[9] = 0 } else { a[9] = 11-x }
    b = 0;
    c = 11;
    for (y=0; y<10; y++) b += (a[y] * c--);
    if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }

    var retorno = true;
    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)) retorno = false;

    return retorno;
}

function validarCnpj(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, '');
    if (cnpj == '') return false;
    if (cnpj.length != 14)
        return false;
    // Elimina CNPJs invalidos conhecidos
    if (cnpj == "00000000000000" ||
        cnpj == "11111111111111" ||
        cnpj == "22222222222222" ||
        cnpj == "33333333333333" ||
        cnpj == "44444444444444" ||
        cnpj == "55555555555555" ||
        cnpj == "66666666666666" ||
        cnpj == "77777777777777" ||
        cnpj == "88888888888888" ||
        cnpj == "99999999999999")
        return false;

    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0, tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0))
        return false;

    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1))
        return false;

    return true;
}

RMGateway.prototype.addPersonTypeObserver = function(obj){
    var pTypeCCElm = jQuery('select[name="payment[ps_cc_persontype]"]');

    if (pTypeCCElm.val() === 'fisica'){
        jQuery(".person-pf").show();
        jQuery(".person-pj").hide();
    }else if (pTypeCCElm.val() === 'juridica'){
        jQuery(".person-pf").hide();
        jQuery(".person-pj").show();
    }

    pTypeCCElm.change(function(){
        if (this.value === 'fisica'){
            jQuery(".person-pf").show();
            jQuery(".person-pj").hide();
        }else if (this.value === 'juridica'){
            jQuery(".person-pf").hide();
            jQuery(".person-pj").show();
        }
    });

    //Débito
    var pTypeCDElm = jQuery('select[name="payment[ps_cd_persontype]"]');

    if (pTypeCDElm.val() === 'fisica'){
        jQuery(".person-pf").show();
        jQuery(".person-pj").hide();
    }else if (pTypeCDElm.val() === 'juridica'){
        jQuery(".person-pf").hide();
        jQuery(".person-pj").show();
    }
    pTypeCDElm.change(function(){
        if (this.value === 'fisica'){
            jQuery(".person-pf").show();
            jQuery(".person-pj").hide();
        }else if (this.value === 'juridica'){
            jQuery(".person-pf").hide();
            jQuery(".person-pj").show();
        }
    });
}
RMGateway.prototype.addCardFieldsObserver = function(obj){ 
    try {
        var cdNumElm = jQuery('input[name="payment[ps_cd_number]"]');
        var ccNumElm = jQuery('input[name="payment[ps_cc_number]"]');
        var ccExpMoElm = jQuery('input[name="payment[ps_cc_exp_month]"]');
        var ccExpYrElm = jQuery('input[name="payment[ps_cc_exp_year]"]');
        var ccCvvElm = jQuery('input[name="payment[ps_cc_cid]"]');        
        var ccExpYrVisibileElm = jQuery('#rm_gateway_cc_cc_year_visible');
        var ccNumVisibleElm = jQuery('.cc_number_visible');
        var cdNumVisibleElm = jQuery('.cd_number_visible');
        var cdCvvElm = jQuery('input[name="payment[ps_cd_cid]"]');        
        var cdPersonType = jQuery('select[name="payment[ps_cd_persontype]"]');
        var ccPersonType = jQuery('select[name="payment[ps_cc_persontype]"]');
        var gCpf = jQuery('input[name="buyer_cpf"]');
        /*jQuery( "#gateway_tef_method .actions-toolbar .checkout" ).prop('disabled', true);
        jQuery( "#gateway_boleto_method .actions-toolbar .checkout" ).prop('disabled', true);
        jQuery( "#gateway_cd_method .actions-toolbar .checkout" ).prop('disabled', true);
        jQuery( "#gateway_cc_method .actions-toolbar .checkout" ).prop('disabled', true);*/

        jQuery(ccNumElm).keyup(function( event ) {
            obj.updateCreditCardToken();
        });

        jQuery(document).on('keyup paste', 'input[name="payment[ps_cd_owner]"]', function( event ){            
            var arr = [8,9,16,17,20,32, 35,36,37,38,39,40,45,46];

			  // Allow letters
			for(var i = 65; i <= 90; i++){
				arr.push(i);
			}
			this.value = this.value.replace(/[^a-zA-Zç\u00C0-\u00FF\s]/g, "");
			jQuery(this).attr('maxlength', 25);  
			  // Prevent default if not in array
			if(jQuery.inArray(event.which, arr) === -1){
			 //   event.preventDefault();
			}
			
		});

		jQuery(document).on('keyup paste', 'input[name="payment[ps_cc_owner]"]', function( event ){            
            var arr = [8,9,16,17,20,32, 35,36,37,38,39,40,45,46];

			  // Allow letters
			for(var i = 65; i <= 90; i++){
				arr.push(i);
			}
			this.value = this.value.replace(/[^a-zA-Zç\u00C0-\u00FF\s]/g, "");
			jQuery(this).attr('maxlength', 25);  
			// Prevent default if not in array
			if(jQuery.inArray(event.which, arr) === -1){
			   // event.preventDefault();
			}
			
		});

        jQuery(document).on('keyup', 'input[name="buyer_cpf"]', function( event ){
            formataCampo(jQuery(this), '000.000.000-00', event);
            jQuery(this).attr('maxlength', 14);            
        });

        jQuery(document).on('keyup', 'input[name="buyer_cnpj"]', function( event ){                        
            formataCampo(jQuery(this), '00.000.000/0000-00', event);
            jQuery(this).attr('maxlength', 18);            
        });

        jQuery(document).on('keyup', 'input[name="telephone"]', function( event ){
            formataCampo(jQuery(this), '(00) 90000-0000', event);
            jQuery(this).attr('maxlength', 15);            
        });

        jQuery(document).on('keyup', 'input[name="postcode"]', function( event ){            
            formataCampo(jQuery(this), '00000-000', event);
            jQuery(this).attr('maxlength', 9);
        });

        jQuery('input[name="payment[ps_cd_cid]"]').on('keydown keyup', function(){
            if (this.value.length > 4)
            this.value = this.value.substr(0, 4);
        });

        jQuery('input[name="payment[ps_cc_cid]"]').on('keydown keyup', function(){
            if (this.value.length > 4)
            this.value = this.value.substr(0, 4);
        });

        jQuery('input[name="payment[ps_cc_exp_month]"]').attr('min', 1)
        jQuery('input[name="payment[ps_cc_exp_month]"]').attr('max', 12)

        jQuery('input[name="payment[ps_cd_exp_month]"]').attr('min', 1)
        jQuery('input[name="payment[ps_cd_exp_month]"]').attr('max', 12)

        jQuery('input[name="payment[method]"]').change(function(){
            obj.updatePaymentHashes();
            //Limpar dados
            jQuery('input[name="payment[ps_cc_owner]"]').val('');		
	        jQuery('input[name="payment[ps_cc_exp_month]"]').val('');
	        jQuery('input[name="payment[ps_cc_exp_year]"]').val('');
	        jQuery('input[name="payment[ps_cc_number]"]').val('');
	        jQuery('input[name="payment[ps_cc_cid]"]').val('');        
	        jQuery('select[name="payment[ps_cc_installments]"]').val(1);

	        jQuery('input[name="payment[ps_cd_owner]"]').val('');		
	        jQuery('input[name="payment[ps_cd_exp_month]"]').val('');
	        jQuery('input[name="payment[ps_cd_exp_year]"]').val('');
	        jQuery('input[name="payment[ps_cd_number]"]').val('');
	        jQuery('input[name="payment[ps_cd_cid]"]').val('');   

            jQuery('input[name="payment[ps_cc_exp_month]"]').attr('min', 1)
            jQuery('input[name="payment[ps_cc_exp_month]"]').attr('max', 12)

            jQuery('input[name="payment[ps_cd_exp_month]"]').attr('min', 1)
            jQuery('input[name="payment[ps_cd_exp_month]"]').attr('max', 12)
            
            //Mínimo ano corrente
            jQuery('input[name="payment[ps_cd_exp_year]"]').attr('min', parseInt(new Date().getFullYear().toString().substr(2,2)));
            jQuery('input[name="payment[ps_cc_exp_year]"]').attr('min', parseInt(new Date().getFullYear().toString().substr(2,2)));
            
        });

        jQuery(cdCvvElm).keydown(function(event){
            return (event.keyCode == 69 || event.keyCode == 190 || event.keyCode == 188) ? false : true;
        });

        jQuery(ccCvvElm).keydown(function(event){
            return (event.keyCode == 69 || event.keyCode == 190 || event.keyCode == 188) ? false : true;
        });

        jQuery(cdCvvElm).keyup(function(event){
        	let value = this.value;
            if (value != undefined && value.toString().length >= 4) {
		        event.preventDefault();
		    }
        });

        jQuery(ccCvvElm).keyup(function(event){
        	let value = this.value;
            if (value != undefined && value.toString().length >= 4) {
		        event.preventDefault();
		    }
        });

              
        jQuery(cdNumVisibleElm).keyup(function( event ) {
            jQuery(this).attr('maxlength', 19);
            jQuery(this).val(function (index, value) {
                var cc_num;
                var key = event.which || event.keyCode || event.charCode;
                if(key == 8) {
                    cc_num = value.replace(/\s+/g, '');
                    jQuery(cdNumElm).val(cc_num);

                } else {
                    if (value != ' ') {
                        var cc_num_original = value.replace(/\s+/g, '');

                        jQuery(cdNumElm).val(cc_num_original);
                        jQuery(cdNumElm).keyup();
                    }
                }

                cc_num = value.replace(/\W/gi, '').replace(/(.{4})/g, '$1 ');
                cc_num = cc_num.trim();
                return cc_num;
            });
            
        });
        jQuery(ccNumVisibleElm).keyup(function( event ) {
            jQuery(this).attr('maxlength', 19);
            jQuery(this).val(function (index, value) {
                var cc_num;
                var key = event.which || event.keyCode || event.charCode;
                if(key == 8) {
                    cc_num = value.replace(/\s+/g, '');
                    jQuery(ccNumElm).val(cc_num);

                } else {
                    if (value != ' ') {
                        var cc_num_original = value.replace(/\s+/g, '');

                        jQuery(ccNumElm).val(cc_num_original);
                        jQuery(ccNumElm).keyup();
                    }
                }

                cc_num = value.replace(/\W/gi, '').replace(/(.{4})/g, '$1 ');
                cc_num = cc_num.trim();
                return cc_num;
            });
           
        });

        
        jQuery(ccExpYrVisibileElm).keyup(function( event ) {
            var ccExpYr = '';
            if(jQuery(this).val().length == 1) {
                ccExpYr = '200' + jQuery(ccExpYrVisibileElm).val();
            }

            if(jQuery(this).val().length == 2) {
                ccExpYr = '20' + jQuery(ccExpYrVisibileElm).val();
            }
            jQuery(ccExpYrElm).val(ccExpYr);
        });
        
        
        /*
        jQuery( "#gateway_cc_method .actions-toolbar .checkout" ).on("click", function() { 
			if(ccCpf.val()!=''){
				obj.updatePaymentHashes();
			}
        });
        
        jQuery( "#gateway_cd_method .actions-toolbar .checkout" ).on("click", function(e) {
            e.preventDefault();
			if(cdCpf.val()!=''){
				obj.updatePaymentHashes();
			}
		});
		
		jQuery( "#gateway_boleto_method .actions-toolbar .checkout" ).on("click", function() { 
			if(cpf.val()!=''){
				obj.updatePaymentHashes();
			}
        });*/

        var _changeInterval = null;
        jQuery(".payment.fieldset input").keyup(function(){
            clearInterval(_changeInterval)
            _changeInterval = setInterval(function() {
                // Typing finished, now you can Do whatever after 2 sec
                obj.updatePaymentHashes();
                clearInterval(_changeInterval);
            }, 100);
            
        });
        
        jQuery(document).on('change', 'select[name="payment[ps_cc_installments]"]', function(){
            obj.updatePaymentHashes();
        });
        	
		
        
        jQuery("#rm_gateway_cc_cc_installments").change(function( event ) {
            obj.updateInstallments();
        });        
        
        jQuery('#rm_gateway_tef').change(function() {
			if(this.checked) {
			   
			}
		});
		
    }catch(e){
        console.error('Unable to add greeting to cards. ' + e.message);
    }

}

RMGateway.prototype.updateCreditCardToken = function(){
    var ccNum = jQuery('input[name="payment[ps_cc_number]"]').val().replace(/^\s+|\s+$/g,'');
    var ccExpMo = jQuery('input[name="payment[ps_cc_exp_month]"]').val().replace(/^\s+|\s+$/g,'');
    var ccExpYr = jQuery('input[name="payment[ps_cc_exp_year]"]').val().replace(/^\s+|\s+$/g,'');
    var ccCvv = jQuery('input[name="payment[ps_cc_cid]"]').val().replace(/^\s+|\s+$/g,'');
    var brandName = '';
    var self = this;
        
   
}

RMGateway.prototype.updateBrand = function(){
    var ccNum ='';
	if(jQuery('input[name="payment[ps_cc_number]"]').val()){
		var ccNum = jQuery('input[name="payment[ps_cc_number]"]').val().replace(/^\s+|\s+$/g,'');
	}
    var currentBin = ccNum.substring(0, 6);
    var flag = this.config.flag;
    var debug = this.config.debug;
    var self = this;

    if(ccNum.length >= 6){
        if (typeof this.cardBin != "undefined" && currentBin == this.cardBin) {
            if(typeof this.brand != "undefined"){
                jQuery('.cc_number_visible').attr('style','background-image:url("https://stc.gateway.uol.com.br/public/img/payment-methods-flags/' +flag + '/' + this.brand.name + '.png") !important');
            }
            return;
        }
        this.cardBin = ccNum.substring(0, 6); 
        GatewayDirectPayment.getBrand({
            cardBin: currentBin,
            success: function(psresponse){
                self.brand = psresponse.brand;
                if(flag != ''){
                    jQuery('.cc_number_visible').attr('style','background-image:url("https://stc.gateway.uol.com.br/public/img/payment-methods-flags/' +flag + '/' + psresponse.brand.name + '.png") !important');
                }
            },
            error: function(psresponse){
                console.error('Failed to get card flag.');
                if(debug){
                    console.debug('Check the call to / getBin on df.uol.com on your Network inspector for more details.');
                }
            }
        })
    }
}

RMGateway.prototype.updatePaymentHashes = function(){
    var self = this;
    var url = self.storeUrl +'gateway/ajax/updatePaymentHashes';    
    var cpf = jQuery('input[name="buyer_cpf"]').val();
    var rg = jQuery('input[name="buyer_rg"]').val();  
    var cnpj = jQuery('input[name="buyer_cnpj"]').val();  
    
    var currentSelectedPayment = jQuery('input[name="payment[method]"]:checked').attr('id');

    if (currentSelectedPayment == 'rm_gateway_boleto') {		
		var paymentHashes = {			
            "ownerdata[cpf]": cpf,
            "ownerdata[rg]": rg,
            "ownerdata[cnpj]": cnpj            		
		};
	}

	if (currentSelectedPayment == 'rm_gateway_tef') {		
		var paymentHashes = {			
            "ownerdata[cpf]": cpf,
            "ownerdata[rg]": rg,
            "ownerdata[cnpj]": cnpj
		};
	}

	if (currentSelectedPayment == 'rm_gateway_cc') {
		var ccOwner = jQuery('input[name="payment[ps_cc_owner]"]').val();		
        var ccOwnerExpMonth = jQuery('input[name="payment[ps_cc_exp_month]"]').val();
        var ccOwnerExpYear = jQuery('input[name="payment[ps_cc_exp_year]"]').val();
        var ccNumber = jQuery('input[name="payment[ps_cc_number]"]').val();
        var ccCvv = jQuery('input[name="payment[ps_cc_cid]"]').val();        
        var installments = jQuery('select[name="payment[ps_cc_installments]"]').val();

		var paymentHashes = {			
            "ownerdata[credit_card_owner]": ccOwner,
            "ownerdata[credit_card_number]": ccNumber,
            "ownerdata[credit_card_cvv]": ccCvv,			
			"ownerdata[credit_card_exp_month]":ccOwnerExpMonth,
            "ownerdata[credit_card_exp_year]":ccOwnerExpYear,
            "ownerdata[credit_card_installments]": installments,
            "ownerdata[cpf]": cpf,
            "ownerdata[rg]": rg,            
            "ownerdata[cnpj]": cnpj
		};
    }

    
    
    if (currentSelectedPayment == 'rm_gateway_cd') {
		var ccOwner = jQuery('input[name="payment[ps_cd_owner]"]').val();		
		var ccOwnerExpMonth = jQuery('input[name="payment[ps_cd_exp_month]"]').val();
        var ccOwnerExpYear = jQuery('input[name="payment[ps_cd_exp_year]"]').val();
        var ccNumber = jQuery('input[name="payment[ps_cd_number]"]').val();
        var ccCvv = jQuery('input[name="payment[ps_cd_cid]"]').val();        
		var paymentHashes = {			
            "ownerdata[credit_card_owner]": ccOwner,
            "ownerdata[credit_card_number]": ccNumber,
            "ownerdata[credit_card_cvv]": ccCvv,			
			"ownerdata[credit_card_exp_month]":ccOwnerExpMonth,
			"ownerdata[credit_card_exp_year]":ccOwnerExpYear,
            "ownerdata[cpf]": cpf,
            "ownerdata[rg]": rg,
            "ownerdata[cnpj]": cnpj
		};	
	}   
    jQuery("button[type=submit]").attr('disabled', true);
    jQuery.ajax({
        url: url,
        type: 'POST',
        data: paymentHashes,
        success: function(response){
            jQuery("button[type=submit]").removeAttr('disabled');
            if(self.config.debug){
                console.debug('Hashes updated successfully.');
                console.debug(paymentHashes);
            }
        },
        error: function(response){
            jQuery("button[type=submit]").removeAttr('disabled');
            if(self.config.debug){
                console.error('Failed to update session hashes.');
                console.error(response);
            }
            return false;
        }
    });
}



RMGateway.prototype.setStoreUrl = function(storeUrl){
    this.storeUrl = storeUrl;
}

RMGateway.prototype.setInstallmentsQty = function(qty){
    this.installmentsQty = qty;
}

RMGateway.prototype.setGrandTotal = function(total){
    this.grandTotal = total;
}

RMGateway.prototype.getGrandTotal = function(){
 
    var url = this.storeUrl + 'gateway/ajax/getGrandTotal';
    var self = this;
    jQuery.ajax({
        url: url,
        success: function(response){
            self.setGrandTotal(response.total);            
        },
        error: function(response){
            return false;
        }
    });
}

RMGateway.prototype.updateSessionId = function(){
    var url = this.setStoreUrl + 'gateway/ajax/getSessionId';
    jQuery.ajax({
        url: url,
        onSuccess: function (response) {
            var session_id = response.session_id;
            if(!session_id){
                console.log('Não foi possível obter a session id do Gateway. Verifique suas configurações.');
            }
            GatewayDirectPayment.setSessionId(session_id);
        }
    });
}

RMGateway.prototype.getInstallments = function(grandTotal, selectedInstallment){
    var brandName = "";
    var self = this;
    
    if(typeof grandTotal == "undefined"){
       this.getGrandTotal();
    }

    this.grandTotal = grandTotal;
    
    var url = this.storeUrl + 'gateway/ajax/getInstallments';
    var self = this;
    jQuery.ajax({
        url: url,
        success: function(response){            
            var instElm = jQuery('select[name="payment[ps_cc_installments]"]');
            instElm.children().remove();
            instElm.append(response.installments);
            /*for (var i = 1; i<=response.installments; i++){
                console.log(i);
                instElm.append('<option value="'+i+'">'+i+'</option>');
            }*/
        },
        error: function(response){
            return false;
        }
    });
}

RMGateway.prototype.updateInstallments = function(){
    var url = this.storeUrl + 'gateway/ajax/updateInstallments';
    ccInstallment = jQuery('select[name="payment[ps_cc_installments]"] option:selected').val();
    var arr = ccInstallment.split("|");
    this.setInstallmentsQty(arr[0]);
    var self = this;
    var installmentsData = {
        "installment[cc_installment]": ccInstallment,
    };
    jQuery.ajax({
        url: url,
        type: 'POST',
        data: installmentsData,
        success: function(response){
            if(self.config.debug){
                console.debug('Installments Data updated successfully.');
                console.debug(installmentsData);
            }
        },
        error: function(response){
            if(self.config.debug){
                console.error('Failed to update Installments Data.');
                console.error(response);
            }
            return false;
        }
    });
}

RMGateway.prototype.setCardPlaceHolderImage = function(ccPlaceholderImage){
        
}