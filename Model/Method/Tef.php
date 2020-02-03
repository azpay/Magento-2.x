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
 * Class Tef
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Model\Method
 */
class Tef extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    const CODE = 'rm_gateway_tef';
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

        //@TODO Remove
        // $this->_minAmount = 1;
        // $this->_maxAmount = 999999999; 
        $this->gatewayHelper = $gatewayHelper;  
        $this->gatewayAbModel = $gatewayAbModel; 
        $this->adminSession = $adminSession;
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
        
        if ($this->gatewayHelper->getCCOwnerData('cpf'))
        $info->setAdditionalInformation('cpf', $this->gatewayHelper->getCCOwnerData('cpf'));
        if ($this->gatewayHelper->getCCOwnerData('cnpj'))
        $info->setAdditionalInformation('cnpj', $this->gatewayHelper->getCCOwnerData('cnpj'));
        if ($this->gatewayHelper->getCCOwnerData('rg'))
        $info->setAdditionalInformation('rg', $this->gatewayHelper->getCCOwnerData('rg'));

        $this->gatewayHelper->writeLog('getData Order: '. var_export($data, true));
      
        
        return $this;
    }

    public function getOrderPlaceRedirectUrl(){
        return "www.evolutap.com.br";
    }
    
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /*@var \Magento\Sales\Model\Order $order */
        $this->gatewayHelper->writeLog('Inside Order');
        
        /*@var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        try {

            //will grab data to be send via POST to API inside $params
            $params = $this->gatewayHelper->getBoletoApiCallParams($order, $payment);
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
            

            $customerId = $billing_cpf;
     
            $gateway = new Gateway($credential);
            $transaction = new Transaction();
            $transaction->Order()
            ->setReference(strval($order->getIncrementId()))
            ->setTotalAmount((int) $order_total);
            
            // Set PAYMENT		
            $transaction->Payment()
            ->setAcquirer($this->getAcquirer());                    

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
            
            // Set URL RETURN
            $transaction->setUrlReturn( $this->gatewayHelper->getStoreUrl().'gateway/notification/index' );
            
            $response = $gateway->OnlineTransfer($transaction);
            
            
            $payment->setAdditionalInformation('tidId', $response->getTransactionID());
            $payment->setAdditionalInformation('transferUrl', $response->getResponse()['processor']['Transfer']['urlTransfer']);
            
            $payment->setSkipOrderProcessing(true);
            $payment->setIsTransactionClosed(false);
            $payment->setIsTransactionPending(true);
            $order->setCanSendNewEmailFlag(false);
            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            
            $responseReport = $gateway->Report($response->getTransactionID());

        } catch (\Exception $e) {

             throw new \Magento\Framework\Exception\LocalizedException(__("<b>O seu pagamento não foi aprovado!</b><br>
            A operadora reportou o seguinte erro: " . $e->getMessage()));
        }
        return $this;
    }

    private function getAcquirer(){
        $pm = $this->gatewayHelper->getTefAcquirer();
        
		$name = Acquirers::ITAU_SHOPLINE;;
		if ($pm == 'bradesco') $name = Acquirers::BRADESCO_SHOPFACIL;		
		if ($pm == 'cielo') $name = Acquirers::CIELO_V3;		
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
        if($this->adminSession->getUser()){
            return false;
        }
        $isAvailable =  $this->getConfigData('active', $quote ? $quote->getStoreId() : null);
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
