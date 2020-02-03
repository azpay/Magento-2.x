<?php
namespace Azpay\Gateway\Model\Method;

foreach (glob(dirname(dirname(dirname(__FILE__)))."/vendor/brunopaz/php-sdk-gateway/src/gateway/API/*.php") as $filename)
{	
	//echo $filename . "<BR>";
    require_once $filename;
}

//require('D:/xampp7/htdocs/magentoce/app/code/Azpay/gateway/vendor/autoload.php');

use \Gateway\API\Credential as Credential;
use \Gateway\API\Environment as Environment;
use \Gateway\API\Gateway as Gateway;
use \Gateway\API\Transaction as Transaction;
use \Gateway\API\Currency as Currency;
use \Gateway\API\Methods as Methods;
use \Gateway\API\Rebill as Rebill;
use \Gateway\API\Acquirers as Acquirers;

/**
 * Class Cc
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Model\Method
 */
class Cc extends \Magento\Payment\Model\Method\Cc
{
    /**
     * @var string
     */
    protected $_formBlockType = \Azpay\Gateway\Block\Form\Cc::class;

    const CODE = 'rm_gateway_cc';

    protected $_code = self::CODE;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_countryFactory;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('BRL');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    /**
     * Gateway Helper
     *
     * @var Azpay\Gateway\Helper\Data;
     */ 
    protected $gatewayHelper;

    /**
     * Gateway Abstract Model
     *
     * @var Azpay\Gateway\Model\Notifications
     */ 
    protected $gatewayAbModel;

    /**
     * Backend Auth Session
     *
     * @var Magento\Backend\Model\Auth\Session $adminSession
     */ 
    protected $adminSession;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Azpay\Gateway\Helper\Data $gatewayHelper,
        \Azpay\Gateway\Model\Notifications $gatewayAbModel,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_countryFactory = $countryFactory;

        //@TODO Remove
        // $this->_minAmount = 1;
        // $this->_maxAmount = 999999999; 
        $this->gatewayHelper = $gatewayHelper;  
        $this->gatewayAbModel = $gatewayAbModel; 
        $this->adminSession = $adminSession;
        $this->messageManager = $messageManager;
        $this->_minAmount = $this->getConfigData('show_min_value');
    }

    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //@TODO Review. Necessary?
          /*@var \Magento\Sales\Model\Order $order */
          $this->gatewayHelper->writeLog('Inside Order');
          $order = $payment->getOrder();
          
          return $this;
    }

     public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /*@var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        try {

             //will grab data to be send via POST to API inside $params
             $params = $this->gatewayHelper->getCreditCardHolderParams($order, $payment);
             $this->gatewayHelper->writeLog($params);
 
             // if ( 'production' == $this->gateway->environment ) {
                 //   $credential = new Credential($this->gateway->merchant_id, $this->gateway->merchant_key, Environment::PRODUCTION);
             //}else
             $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->getMerchantKey(), $this->gatewayHelper->getEnvironment());
 
             $order_total = $order->getGrandTotal() * 100;
 
             $billing_name = sprintf('%s %s', $order->getCustomerFirstname(), $order->getCustomerLastname());
             $billing_rg = $this->gatewayHelper->getRg($order, $payment);
             $billing_cpf = $this->gatewayHelper->getCpf($order, $payment);                
             $billing_cnpj = $this->gatewayHelper->getCnpj($order, $payment);             
 
 
            //Verificar valor com parcelas, atualizar venda com juros
            $order_total_interest = $this->gatewayHelper->getValueInstallments($order, $payment);
            if ($order_total_interest > 0){
                $order->setTaxAmount( $order_total_interest - $order->getGrandTotal() );
                $order->setGrandTotal( $order_total_interest );  
                $order->setTotalPaid( $order_total_interest );  
                $order_total = $order_total_interest * 100;
            }

            $subscription = false;
            foreach ($order->getAllItems() as $item) {
                                    
                $name = $item->getName(); // Get the product name            
                $quantity = $item->getQtyOrdered(); // Get the item quantity            
                $price = $item->getPrice(); // Get the item line total

                $product = $item->getProduct();
                if ($product->getTypeId() == 'subscription_product_type'){
                    $subscription = true;
                    $period = $product->getData('subscription_todo');
                    $days = $product->getData('subscription_expire');
                    $endDate = date('Y-m-d', strtotime(sprintf("+ %d $period", $days)));
                    $frequency = $product->getData('subscription_frequencia');
                    if ($period == 'day'){
                        $period = Rebill::DAILY;
                    }else if ($period == 'week'){
                        $period = Rebill::WEEKLY;
                    }else if ($period == 'month'){
                        $period = Rebill::MONTHLY;
                    }else if ($period == 'year'){
                        $period = Rebill::YEARLY;
                    }
                }

                $items[] = array("productName" => $name, "quantity" => $quantity, "price" => $price);
            }


            $gateway = new Gateway($credential);

            $transaction = new Transaction();
            if ($subscription){
                $transaction->Order()
                    ->setReference(strval($order->getIncrementId()))
                    ->setTotalAmount((int) $order_total)
                    ->setDateStart(date('Y-m-d') )
                    ->setDateEnd($endDate)
                    ->setPeriod($period)
                    ->setFrequency((int)$frequency);
            }else{
                $transaction->Order()
                 ->setReference(strval($order->getIncrementId()))
                 ->setTotalAmount((int) $order_total);
            }
             
             $customerId = $order->getCustomerId();
             if (!$customerId)
                $customerId = $billing_cpf;

             $installments = $this->gatewayHelper->getInstallments($order, $payment);
             $method = Methods::CREDIT_CARD_INTEREST_BY_MERCHANT;
             if ($installments <= 1)
                $method = Methods::CREDIT_CARD_NO_INTEREST;

             // Set PAYMENT     
             $transaction->Payment()
                ->setAcquirer($this->getAcquirer())
                ->setMethod($method)
                ->setCurrency(Currency::BRAZIL_BRAZILIAN_REAL_BRL)
                ->setCountry("BRA")
                ->setNumberOfPayments($this->gatewayHelper->getInstallments($order, $payment))
                ->setSoftDescriptor($this->gatewayHelper->getCcSoftDescriptor())
                ->Card()
                    ->setBrand('visa')
                    ->setCardHolder($params['holder_name'])
                    ->setCardNumber(preg_replace( '([^0-9])', '',  $params['card_number'] ))
                    ->setCardSecurityCode($params['card_cvv'])
                    ->setCardExpirationDate($params['card_expiry_date']);
 
             // SET CUSTOMER
             if (isset($billing_cnpj) && !empty($billing_cnpj)){
                 $customerId = $billing_cnpj;
                 $transaction->Customer()
                     ->setCustomerIdentity(substr(strval($customerId), 0, 11))
                     ->setName($billing_name)   
                     ->setCnpj($billing_cnpj)   
                     ->setEmail(trim($order->getCustomerEmail()))
                     ->setAddress($this->gatewayHelper->getAddress($order, $payment))
                     ->setAddress2($this->gatewayHelper->getAddress($order, $payment))            
                     ->setPostalCode($this->gatewayHelper->getPostcode($order, $payment))
                     ->setCity($this->gatewayHelper->getCity($order, $payment))
                     ->setState($this->gatewayHelper->getState($order, $payment))
                     ->setCountry("BR");                
             }else{
                 $transaction->Customer()
                     ->setCustomerIdentity(strval($customerId))
                     ->setName($billing_name)   
                     ->setCpf($billing_cpf)     
                     ->setCnpj("11111111111111")    
                     ->setEmail(trim($order->getCustomerEmail()))
                     ->setAddress($this->gatewayHelper->getAddress($order, $payment))
                     ->setAddress2($this->gatewayHelper->getAddress($order, $payment))            
                     ->setPostalCode($this->gatewayHelper->getPostcode($order, $payment))
                     ->setCity($this->gatewayHelper->getCity($order, $payment))
                     ->setState($this->gatewayHelper->getState($order, $payment))
                     ->setCountry("BR");    
             }

            if ( $this->gatewayHelper->getCcCanAntifraud() ){
                $items = [];
                foreach ($order->getAllItems() as $item) {
                                        
                    $name = $item->getName(); // Get the product name
                
                    $quantity = $item->getQtyOrdered(); // Get the item quantity
                
                    $price = $item->getPrice(); // Get the item line total
                
                    $items[] = array("productName" => $name, "quantity" => $quantity, "price" => $price);
                    
                }

                if (!isset($billing_rg) || empty($billing_rg)){
                    $billing_rg = $billing_cpf;
                }
                 if (!isset($billing_rg) || empty($billing_rg)){
                    $billing_rg = $billing_cnpj;
                 }
                // SET FRAUD DATA OBJECT
                $transaction->FraudData()
                    ->setMethod($this->gatewayHelper->getCcFraudMethod())
                    ->setOperator($this->gatewayHelper->getCcFraudOperator())
                    ->setName($billing_name)
                    ->setDocument($billing_rg)
                    ->setEmail(trim($order->getCustomerEmail()))
                    ->setAddressNumber("")
                    ->setAddress($this->gatewayHelper->getAddress($order, $payment))
                    ->setAddress2($this->gatewayHelper->getAddress($order, $payment))            
                    ->setPostalCode($this->gatewayHelper->getPostcode($order, $payment))
                    ->setCity($this->gatewayHelper->getCity($order, $payment))
                    ->setState($this->gatewayHelper->getState($order, $payment))
                    ->setCountry("BR")    
                    ->setPhonePrefix("")
                    ->setPhoneNumber($order->getShippingAddress()->getTelephone())
                    ->setDevice($_SERVER['HTTP_USER_AGENT'])
                    ->setCostumerIP($_SERVER['REMOTE_ADDR'])
                    ->setItems($items);
            }
             
            // Set URL RETURN
            $transaction->setUrlReturn( $this->gatewayHelper->getStoreUrl().'gateway/notification/index' );
             
            if ($subscription){
                $response = $gateway->rebill($transaction);
            }else{
                $response = $gateway->Authorize($transaction);
            }
            
             
            $responseReport = $gateway->Report($response->getTransactionID());
             
            if (!$response->isAuthorized()){     
                $this->messageManager->addErrorMessage('O pagamento não foi autorizado, tente novamente');
                throw new \Magento\Framework\Exception\LocalizedException(__('O pagamento não foi autorizado, tente novamente'));
            }

            $payment->setAdditionalInformation('tidId', $response->getTransactionID());
             
            $payment->setSkipOrderProcessing(true);

            $payment
                ->setTransactionId($response->getTransactionID())
                ->setIsTransactionClosed(0);


        } catch (\Exception $e) {

           throw new \Magento\Framework\Exception\LocalizedException(__("<b>O seu pagamento não foi aprovado!</b><br>
            A operadora reportou o seguinte erro: " . $e->getMessage()));
        }
        return $this;
    }

    private function getAcquirer(){
        $pm = $this->gatewayHelper->getCcAcquirer();
        
		if ($pm == 'adiq') $name = Acquirers::ADIQ;
		if ($pm == 'cielo_loja') $name = Acquirers::CIELO_BUY_PAGE_LOJA;
		if ($pm == 'cielo') $name = Acquirers::CIELO_BUY_PAGE_CIELO;
		if ($pm == 'cielo_api') $name = Acquirers::CIELO_V3;
		if ($pm == 'granito') $name = Acquirers::GRANITO;		
		if ($pm == 'global_payments') $name = Acquirers::GLOBAL_PAYMENT;
		if ($pm == 'getnet') $name = Acquirers::GETNET;
		if ($pm == 'erede') $name = Acquirers::REDE_E_REDE;
		if ($pm == 'firstdata') $name = Acquirers::FIRSTDATA;		
		if ($pm == 'komerci_webservice') $name = Acquirers::REDE_KOMERCI_WEBSERVICE;
		if ($pm == 'komerci_integrado') $name = Acquirers::REDE_KOMERCI_INTEGRADO;
		if ($pm == 'privatelabel') $name = Acquirers::VERANCARD;
		if ($pm == 'stone') $name = Acquirers::STONE;
		if ($pm == 'worldpay') $name = Acquirers::WORLDPAY;
        if ($pm == 'gateway') $name = Acquirers::AZPAY;
        
        return $name;
    }

    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /*@var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        try {
            $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->  getMerchantKey(), $this->gatewayHelper->getEnvironment());

            $gateway = new Gateway($credential);

             $ctid = $payment->getAdditionalInformation('tidId');
             if (isset($ctid)){
                $response = $gateway->Report($ctid);
        
                if ($response->canCapture()){
                    $response = $gateway->Capture($ctid, $amount * 100);  

                    $payment->setSkipOrderProcessing(true);

                    $payment
                        ->setTransactionId($response->getTransactionID())
                        ->setIsTransactionClosed(0);


                }
                return $this;
             }
             //will grab data to be send via POST to API inside $params
             $params = $this->gatewayHelper->getCreditCardHolderParams($order, $payment);
             $this->gatewayHelper->writeLog($params);
 
             // if ( 'production' == $this->gateway->environment ) {
                 //   $credential = new Credential($this->gateway->merchant_id, $this->gateway->merchant_key, Environment::PRODUCTION);
             //}else
             
 
             $order_total = $order->getGrandTotal() * 100;
 
             $billing_name = sprintf('%s %s', $order->getCustomerFirstname(), $order->getCustomerLastname());
             $billing_rg = $this->gatewayHelper->getRg($order, $payment);
             $billing_cpf = $this->gatewayHelper->getCpf($order, $payment);                
             $billing_cnpj = $this->gatewayHelper->getCnpj($order, $payment);             
           
            //Verificar valor com parcelas, atualizar venda com juros
            $order_total_interest = $this->gatewayHelper->getValueInstallments($order, $payment);
            if ($order_total_interest > 0){
                $order->setTaxAmount( $order_total_interest - $order->getGrandTotal() );
                $order->setGrandTotal( $order_total_interest );  
                $order->setTotalPaid( $order_total_interest );  
                $order_total = $order_total_interest * 100;
            }


            $items = [];
            $subscription = false;
            foreach ($order->getAllItems() as $item) {
                                    
                $name = $item->getName(); // Get the product name            
                $quantity = $item->getQtyOrdered(); // Get the item quantity            
                $price = $item->getPrice(); // Get the item line total

                $product = $item->getProduct();
                if ($product->getTypeId() == 'subscription_product_type'){
                    $subscription = true;
                    $period = $product->getData('subscription_todo');
                    $days = $product->getData('subscription_expire');
                    $endDate = date('Y-m-d', strtotime(sprintf("+ %d $period", $days)));
                    $frequency = $product->getData('subscription_frequencia');
                    if ($period == 'day'){
                        $period = Rebill::DAILY;
                    }else if ($period == 'week'){
                        $period = Rebill::WEEKLY;
                    }else if ($period == 'month'){
                        $period = Rebill::MONTHLY;
                    }else if ($period == 'year'){
                        $period = Rebill::YEARLY;
                    }
                }

                $items[] = array("productName" => $name, "quantity" => $quantity, "price" => $price);
            }

          


            $transaction = new Transaction();
            if ($subscription){
                $transaction->Order()
                    ->setReference(strval($order->getIncrementId()))
                    ->setTotalAmount((int) $order_total)
                    ->setDateStart(date('Y-m-d') )
                    ->setDateEnd($endDate)
                    ->setPeriod($period)
                    ->setFrequency((int)$frequency);
            }else{
                $transaction->Order()
                 ->setReference(strval($order->getIncrementId()))
                 ->setTotalAmount((int) $order_total);
             }

             
             $customerId = $order->getCustomerId();
             if (!$customerId)
                $customerId = $billing_cpf;
            
             
             $installments = $this->gatewayHelper->getInstallments($order, $payment);
             $method = Methods::CREDIT_CARD_INTEREST_BY_MERCHANT;
             if ($installments <= 1)
                $method = Methods::CREDIT_CARD_NO_INTEREST;

             // Set PAYMENT		
             $transaction->Payment()
                ->setAcquirer($this->getAcquirer())
                ->setMethod($method)
                ->setCurrency(Currency::BRAZIL_BRAZILIAN_REAL_BRL)
                ->setCountry("BRA")
                ->setNumberOfPayments($installments)
                ->setSoftDescriptor($this->gatewayHelper->getCcSoftDescriptor())
                ->Card()
                    ->setBrand('visa')
                    ->setCardHolder($params['holder_name'])
                    ->setCardNumber(preg_replace( '([^0-9])', '',  $params['card_number'] ))
                    ->setCardSecurityCode($params['card_cvv'])
                    ->setCardExpirationDate($params['card_expiry_date']);
 
             // SET CUSTOMER
             if (isset($billing_cnpj) && !empty($billing_cnpj)){
                 $customerId = $billing_cnpj;
                 $transaction->Customer()
                     ->setCustomerIdentity(strval($customerId))
                     ->setName($billing_name)	
                     ->setCnpj($billing_cnpj)	
                     ->setEmail(trim($order->getCustomerEmail()))
                     ->setAddress($this->gatewayHelper->getAddress($order, $payment))
                     ->setAddress2($this->gatewayHelper->getAddress($order, $payment))			
                     ->setPostalCode($this->gatewayHelper->getPostcode($order, $payment))
                     ->setCity($this->gatewayHelper->getCity($order, $payment))
                     ->setState($this->gatewayHelper->getState($order, $payment))
                     ->setCountry("BR");				
             }else{
                 $transaction->Customer()
                     ->setCustomerIdentity(strval($customerId))
                     ->setName($billing_name)	
                     ->setCpf($billing_cpf)		
                     ->setCnpj("11111111111111")	
                     ->setEmail(trim($order->getCustomerEmail()))
                     ->setAddress($this->gatewayHelper->getAddress($order, $payment))
                     ->setAddress2($this->gatewayHelper->getAddress($order, $payment))			
                     ->setPostalCode($this->gatewayHelper->getPostcode($order, $payment))
                     ->setCity($this->gatewayHelper->getCity($order, $payment))
                     ->setState($this->gatewayHelper->getState($order, $payment))
                     ->setCountry("BR");	
             }

            if ( $this->gatewayHelper->getCcCanAntifraud() ){
               

                if (!isset($billing_rg) || empty($billing_rg)){
                    $billing_rg = $billing_cpf;
                }
                // SET FRAUD DATA OBJECT
                $transaction->FraudData()
                    ->setMethod($this->gatewayHelper->getCcFraudMethod())
                    ->setOperator($this->gatewayHelper->getCcFraudOperator())
                    ->setName($billing_name)
                    ->setDocument($billing_rg)
                    ->setEmail(trim($order->getCustomerEmail()))
                    ->setAddressNumber("")
                    ->setAddress($this->gatewayHelper->getAddress($order, $payment))
                    ->setAddress2($this->gatewayHelper->getAddress($order, $payment))            
                    ->setPostalCode($this->gatewayHelper->getPostcode($order, $payment))
                    ->setCity($this->gatewayHelper->getCity($order, $payment))
                    ->setState($this->gatewayHelper->getState($order, $payment))
                    ->setCountry("BR")    
                    ->setPhonePrefix("")
                    ->setPhoneNumber($order->getShippingAddress()->getTelephone())
                    ->setDevice($_SERVER['HTTP_USER_AGENT'])
                    ->setCostumerIP($_SERVER['REMOTE_ADDR'])
                    ->setItems($items);
            }
             
            // Set URL RETURN
            $transaction->setUrlReturn( $this->gatewayHelper->getStoreUrl().'gateway/notification/index' );
             
            if ($subscription){
                $response = $gateway->rebill($transaction);
            }else{
                $response = $gateway->Sale($transaction);
            }
            
                         
            $responseReport = $gateway->Report($response->getTransactionID());
             
            if (!$response->isAuthorized()){     
                $this->messageManager->addErrorMessage('O pagamento não foi autorizado, tente novamente');
                throw new \Magento\Framework\Exception\LocalizedException(__('O pagamento não foi autorizado, tente novamente'));
            }

            $payment->setAdditionalInformation('tidId', $response->getTransactionID());
             
            $payment->setSkipOrderProcessing(true);

            $payment
                ->setTransactionId($response->getTransactionID())
                ->setIsTransactionClosed(0);


        } catch (\Exception $e) {

              throw new \Magento\Framework\Exception\LocalizedException(__("<b>O seu pagamento não foi aprovado!</b><br>
            A operadora reportou o seguinte erro: " . $e->getMessage()));
        }
        return $this;
    }

    /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // recupera a informação adicional do Gateway
        $info           = $this->getInfoInstance();
        $transactionId = $info->getAdditionalInformation('tidId');
    
    
        try {

            $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->getMerchantKey(), $this->gatewayHelper->getEnvironment());
            $gateway = new Gateway($credential);         

            $amount = number_format($amount, 2, '.', '');
            $response = $gateway->Cancel($transactionId, $amount * 100);
           

            if ($response === null) {
                $errorMsg = $this->_getHelper()->__('Erro ao solicitar o reembolso.\n');
                throw new \Magento\Framework\Validator\Exception($errorMsg);
            }
        } catch (\Exception $e) {
            $this->debugData(['transaction_id' => $transactionId, 'exception' => $e->getMessage()]);            
            throw new \Magento\Framework\Validator\Exception(__('Payment refunding error.' . $e->getMessage()));
        }

        $payment
            ->setTransactionId($transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);

        return $this;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  object
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }
        
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('sender_hash', $this->gatewayHelper->getPaymentHash('sender_hash'))
            ->setAdditionalInformation('credit_card_token', $this->gatewayHelper->getPaymentHash('credit_card_token'))
            ->setAdditionalInformation('credit_card_owner', $this->gatewayHelper->getCCOwnerData('credit_card_owner'))
            ->setAdditionalInformation('credit_card_number', $data['additional_data']['cc_number'])
            ->setAdditionalInformation('credit_card_cvv', $data['additional_data']['cc_cid'])
            ->setAdditionalInformation('cc_exp_year', $data['additional_data']['cc_exp_year'])
            ->setAdditionalInformation('cc_exp_month', $data['additional_data']['cc_exp_month'])
            ->setAdditionalInformation('credit_card_installments', $this->gatewayHelper->getCCOwnerData('credit_card_installments'))
            ->setAdditionalInformation('credit_card_exp_month', $this->gatewayHelper->getCCOwnerData('credit_card_exp_month'))            
            ->setAdditionalInformation('credit_card_exp_year', $this->gatewayHelper->getCCOwnerData('credit_card_exp_year'))            
            ->setCcType($this->gatewayHelper->getPaymentHash('cc_type'))
            ->setCcLast4(substr($data['additional_data']['cc_number'], -4))
            ->setCcExpYear($data['additional_data']['cc_exp_year'])
            ->setCcExpMonth($data['additional_data']['cc_exp_month']);

        if ($this->gatewayHelper->getCCOwnerData('cpf'))
        $info->setAdditionalInformation('cpf', $this->gatewayHelper->getCCOwnerData('cpf'));
        if ($this->gatewayHelper->getCCOwnerData('cnpj'))
        $info->setAdditionalInformation('cnpj', $this->gatewayHelper->getCCOwnerData('cnpj'));
        if ($this->gatewayHelper->getCCOwnerData('rg'))
        $info->setAdditionalInformation('rg', $this->gatewayHelper->getCCOwnerData('rg'));
        

        //Installments value
        /*if ($this->gatewayHelper->getInstallments('cc_installment')) {
            $installments = explode('|', $this->gatewayHelper->getInstallments('cc_installment'));
            if (false !== $installments && count($installments)==2) {
                $info->setAdditionalInformation('installment_quantity', (int)$installments[0]);
                $info->setAdditionalInformation('installment_value', $installments[1]);
            }
        }*/
        return $this;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {   
        if($this->adminSession->getUser()){
            return false;
        }
        $isAvailable =  $this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        if (empty($quote)) {
            return $isAvailable;
        }
        if ($this->getConfigData("group_restriction") == false) {
            return $isAvailable;
        }

        if ($quote && (
            $quote->getBaseGrandTotal() < $this->_minAmount
            || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        } 

        return parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * Validate payment method information object
     *
     * @return Payment Model
     */
    public function validate()
    {
        $this->gatewayHelper->writeLog(__('CC validate method'));

        return $this;
    }
}