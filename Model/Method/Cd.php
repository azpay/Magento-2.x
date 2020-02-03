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
class Cd extends \Magento\Payment\Model\Method\AbstractMethod
{    
    /**
     * @var string
     */
    const CODE = 'rm_gateway_cd';
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
    protected $_infoBlockType = \Azpay\Gateway\Block\Payment\Info::class;

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    protected $_orderFactory;
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
        \Azpay\Gateway\Helper\Data $gatewayHelper,
        \Azpay\Gateway\Model\Notifications $gatewayAbModel,
        \Magento\Backend\Model\Auth\Session $adminSession,  
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,  
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
            null,
            null,
            $data
        );

        $this->gatewayHelper = $gatewayHelper;  
        $this->gatewayAbModel = $gatewayAbModel; 
        $this->adminSession = $adminSession;
        $this->_customer = $customer;
        $this->orderRepository = $orderRepository;
        //$this->messageManager = $messageManager;
        $this->_minAmount = $this->getConfigData('show_min_value');
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
            ->setAdditionalInformation('credit_card_number', $this->gatewayHelper->getCCOwnerData('credit_card_number'))
            ->setAdditionalInformation('credit_card_cvv', $this->gatewayHelper->getCCOwnerData('credit_card_cvv'))            
            ->setAdditionalInformation('credit_card_exp_month', $this->gatewayHelper->getCCOwnerData('credit_card_exp_month'))            
            ->setAdditionalInformation('credit_card_exp_year', $this->gatewayHelper->getCCOwnerData('credit_card_exp_year'))            
            ->setCcType($this->gatewayHelper->getPaymentHash('cc_type'))
            ->setCcLast4(substr($this->gatewayHelper->getCCOwnerData('credit_card_number'), -4));            

        if ($this->gatewayHelper->getCCOwnerData('cpf'))
        $info->setAdditionalInformation('cpf', $this->gatewayHelper->getCCOwnerData('cpf'));
        if ($this->gatewayHelper->getCCOwnerData('cnpj'))
        $info->setAdditionalInformation('cnpj', $this->gatewayHelper->getCCOwnerData('cnpj'));
        if ($this->gatewayHelper->getCCOwnerData('rg'))
        $info->setAdditionalInformation('rg', $this->gatewayHelper->getCCOwnerData('rg'));
        
        return $this;
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
        /*$this->gatewayHelper->writeLog('Inside Order');
        
       
        $order = $payment->getOrder();

        try {

            file_put_contents('D:\\xampp7\\htdocs\\magentoce\\log_cd_'.date("j.n.Y").'.log', "LOG " . json_encode($order->toArray()), FILE_APPEND);

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

            $customerId = $order->getCustomerId();
            if (!$customerId)
                $customerId = $billing_cpf;

            file_put_contents('D:\\xampp7\\htdocs\\magentoce\\log_'.date("j.n.Y").'.log', "LOG " . $billing_cpf, FILE_APPEND);

            
            $gateway = new Gateway($credential);
            $transaction = new Transaction();
            $transaction->Order()
            ->setReference(strval($order->getIncrementId()))
            ->setTotalAmount((int) $order_total);
            
            // Set PAYMENT		
            $transaction->Payment()
            ->setAcquirer($this->getAcquirer())
            ->setMethod(Methods::DEBIT_CARD)
            ->setCurrency(Currency::BRAZIL_BRAZILIAN_REAL_BRL)
            ->setCountry("BRA")
            ->setNumberOfPayments(1)
            ->setSoftDescriptor('SOFTSCRIPTOR')
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
            
            if ( $this->gatewayHelper->getCdCanAntifraud() ){
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
                // SET FRAUD DATA OBJECT
                $transaction->FraudData()
                    ->setMethod($this->gatewayHelper->getCdFraudMethod())
                    ->setOperator($this->gatewayHelper->getCdFraudOperator())
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
            // Set URL RETURN
            $transaction->setUrlReturn( $this->gatewayHelper->getStoreUrl().'gateway/notification/index' );
             
            $response = $gateway->authorize($transaction);
                      
            $status = $response->getStatus();
            if ( 0 == $status ){
                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            }else
            if ( 8 != $status ) {            
                //$this->messageManager->addErrorMessage(__('O pagamento não foi autorizado, tente novamente'));           
                throw new \Magento\Framework\Exception\LocalizedException(__('O pagamento não foi autorizado, tente novamente'));
            }
            file_put_contents('D:\\xampp7\\htdocs\\magentoce\\log_debit_'.date("j.n.Y").'.log', "LOG " . $response->getResponseJson(), FILE_APPEND);


            $payment->setAdditionalInformation('transferUrl', (string) $response->getRedirectUrl());
                     
        } catch (\Exception $e) {

            throw new \Magento\Framework\Exception\LocalizedException(__("<b>O seu pagamento não foi aprovado!</b><br>
            A operadora reportou o seguinte erro: " . $e->getMessage()));
        }*/
        return $this;
    }
    
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /*@var \Magento\Sales\Model\Order $order */
        $this->gatewayHelper->writeLog('Inside Order');
        
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

            $customerId = $order->getCustomerId();
            if (!$customerId)
                $customerId = $billing_cpf;

           
            $gateway = new Gateway($credential);
            $transaction = new Transaction();
            $transaction->Order()
            ->setReference(strval($order->getIncrementId()))
            ->setTotalAmount((int) $order_total);
            
            // Set PAYMENT      
            $transaction->Payment()
            ->setAcquirer($this->getAcquirer())
            ->setMethod(Methods::DEBIT_CARD)
            ->setCurrency(Currency::BRAZIL_BRAZILIAN_REAL_BRL)
            ->setCountry("BRA")
            ->setNumberOfPayments(1)
            ->setSoftDescriptor('SOFTSCRIPTOR')
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
            
            if ( $this->gatewayHelper->getCdCanAntifraud() ){
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
                // SET FRAUD DATA OBJECT
                $transaction->FraudData()
                    ->setMethod($this->gatewayHelper->getCdFraudMethod())
                    ->setOperator($this->gatewayHelper->getCdFraudOperator())
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
            // Set URL RETURN
            $transaction->setUrlReturn( $this->gatewayHelper->getStoreUrl().'gateway/notification/index' );
             
            $response = $gateway->authorize($transaction);
                      
            $status = $response->getResponse()['status'];
            if ( 0 == $status || 'WAITING FOR PAYMENT' == $status ){
                $payment->setSkipOrderProcessing(true);
                $payment->setIsTransactionClosed(false);
                $payment->setIsTransactionPending(true);
                $order->setCanSendNewEmailFlag(false);
                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                
            }else
            if ( 8 != $status ) {            
                //$this->messageManager->addErrorMessage(__('O pagamento não foi autorizado, tente novamente'));           
                throw new \Magento\Framework\Exception\LocalizedException(__('O pagamento não foi autorizado, tente novamente'));
            }
           
            $payment->setAdditionalInformation('tidId', $response->getTransactionID());
            $payment->setAdditionalInformation('transferUrl', (string) $response->getRedirectUrl());
                     
        } catch (\Exception $e) {

            throw new \Magento\Framework\Exception\LocalizedException(__("<b>O seu pagamento não foi aprovado!</b><br>
            A operadora reportou o seguinte erro: " . $e->getMessage()));
        }
        return $this;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // recupera a informação adicional do Gateway
        $info           = $this->getInfoInstance();
        $transactionId = $info->getAdditionalInformation('tidId');
    
        $params['token'] = $this->gatewayHelper->getToken();
        $params['email'] = $this->gatewayHelper->getMerchantEmail();

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

    private function getAcquirer(){
        $pm = $this->gatewayHelper->getCdAcquirer();
        
		if ($pm == 'cielo_loja') $name = Acquirers::CIELO_BUY_PAGE_LOJA;		
		if ($pm == 'cielo_api') $name = Acquirers::CIELO_V3;		
		if ($pm == 'global_payments') $name = Acquirers::GLOBAL_PAYMENT;
		if ($pm == 'getnet') $name = Acquirers::GETNET_V1;
		if ($pm == 'erede') $name = Acquirers::REDE_E_REDE;
		if ($pm == 'firstdata') $name = Acquirers::FIRSTDATA;				
		if ($pm == 'gateway') $name = Acquirers::AZPAY;
        
        return $name;
    }

	/**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $count = 0;
        $items = $quote->getAllItems();
        foreach ($items as $item) {
           //logic for item that you can not buy together
           if ($item->getProduct()->getTypeId() == 'subscription_product_type') {
                $count++;
            }
        }

        if ($count > 0){
            return false;
        }

        if ($this->adminSession->getUser()) {
            return false;
        }
        $isAvailable = $this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        if (empty($quote)) {
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
}