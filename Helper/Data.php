<?php
namespace Azpay\Gateway\Helper;

use Gateway\API\Environment;

/**
 * Class Data Helper
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const PAYMENT_CONFIG_MERCHANT_ID                    = 'payment/rm_gateway/merchant_id';
    const PAYMENT_CONFIG_MERCHANT_KEY                   = 'payment/rm_gateway/merchant_key';
    const PAYMENT_CONFIG_GATEWAY_DEBUG                = 'payment/rm_gateway/debug';
    
    const XML_PATH_PAYMENT_GATEWAY_RG_REQUIRED              = 'payment/rm_gateway/rg_required';
    const XML_PATH_PAYMENT_GATEWAY_TELEFONE_REQUIRED              = 'payment/rm_gateway/telefone_required';
	const XML_PATH_PAYMENT_GATEWAY_EMAIL              = 'payment/rm_gateway/telefone_equired';
    const XML_PATH_PAYMENT_GATEWAY_TOKEN              = 'payment/rm_gateway/token';
    const XML_PATH_PAYMENT_GATEWAY_AMBIENTE              = 'payment/rm_gateway/ambiente';
    const XML_PATH_PAYMENT_GATEWAY_DEBUG              = 'payment/rm_gateway/debug';
    const XML_PATH_PAUMENT_GATEWAY_SANDBOX            = 'payment/rm_gateway/sandbox';
    const XML_PATH_PAYMENT_GATEWAY_SANDBOX_EMAIL      = 'payment/rm_gateway/sandbox_merchant_email';
    const XML_PATH_PAYMENT_GATEWAY_SANDBOX_TOKEN      = 'payment/rm_gateway/sandbox_token';
    //@TODO Remove hardcoded value in constant and move to config.xml defaults
    const XML_PATH_PAYMENT_GATEWAY_WS_URL             = 'https://ws.Azpay.net.br/pspro/v6/wsgateway/v2/';
    const XML_PATH_PAYMENT_GATEWAY_WS_URL_APP         = 'payment/rm_gateway/ws_url_app';
    const XML_PATH_PAYMENT_GATEWAY_JS_URL             = 'payment/rm_gateway/js_url';
    const XML_PATH_PAYMENT_GATEWAY_SANDBOX_WS_URL     = 'payment/rm_gateway/sandbox_ws_url';
    const XML_PATH_PAYMENT_GATEWAY_SANDBOX_WS_URL_APP = 'payment/rm_gateway/sandbox_ws_url_app';
    const XML_PATH_PAYMENT_GATEWAY_SANDBOX_JS_URL     = 'payment/rm_gateway/sandbox_js_url';
    const XML_PATH_PAYMENT_GATEWAY_CC_ACTIVE          = 'payment/rm_gateway_cc/active';
    const XML_PATH_PAYMENT_GATEWAY_CC_FLAG            = 'payment/rm_gateway_cc/flag';
    const XML_PATH_PAYMENT_GATEWAY_CC_INFO_BRL        = 'payment/rm_gateway_cc/info_brl';
    const XML_PATH_PAYMENT_GATEWAY_CC_SHOW_TOTAL      = 'payment/rm_gateway_cc/show_total';
        
    const XML_PATH_PAYMENT_GATEWAY_CC_SOFT_DESCRIPTOR         = 'payment/rm_gateway_cc/soft_descriptor';
    const XML_PATH_PAYMENT_GATEWAY_CC_ACQUIRER          = 'payment/rm_gateway_cc/acquirer';
    const XML_PATH_PAYMENT_GATEWAY_CC_CAN_ANTIFRAUD         = 'payment/rm_gateway_cc/can_antifraud';
    const XML_PATH_PAYMENT_GATEWAY_CC_ANTIFRAUD         = 'payment/rm_gateway_cc/antifraud';
    const XML_PATH_PAYMENT_GATEWAY_CC_CAN_CAPTURE         = 'payment/rm_gateway_cc/can_capture';
    
    const XML_PATH_PAYMENT_GATEWAY_CC_SMALLEST_INSTALLMENTS         = 'payment/rm_gateway_cc/smallest_installment';
    const XML_PATH_PAYMENT_GATEWAY_CC_MAX_INSTALLMENTS         = 'payment/rm_gateway_cc/max_installments';
    const XML_PATH_PAYMENT_GATEWAY_CC_INTEREST_RATE         = 'payment/rm_gateway_cc/interest_rate';
    const XML_PATH_PAYMENT_GATEWAY_CC_CREDIT_INTEREST         = 'payment/rm_gateway_cc/credit_interest';
    const XML_PATH_PAYMENT_GATEWAY_CC_SHOW_MIN_VALUE         = 'payment/rm_gateway_cc/show_min_value';
        

    const XML_PATH_PAYMENT_GATEWAY_CD_ACTIVE          = 'payment/rm_gateway_cd/active';
    const XML_PATH_PAYMENT_GATEWAY_CD_SOFT_DESCRIPTOR         = 'payment/rm_gateway_cd/soft_descriptor';
    const XML_PATH_PAYMENT_GATEWAY_CD_ACQUIRER          = 'payment/rm_gateway_cd/acquirer';
    const XML_PATH_PAYMENT_GATEWAY_CD_CAN_ANTIFRAUD         = 'payment/rm_gateway_cd/can_antifraud';
    const XML_PATH_PAYMENT_GATEWAY_CD_ANTIFRAUD         = 'payment/rm_gateway_cd/antifraud';
    const XML_PATH_PAYMENT_GATEWAY_CD_DISCOUNT         = 'payment/rm_gateway_cd/discount';
    const XML_PATH_PAYMENT_GATEWAY_CD_DESC         = 'payment/rm_gateway_cd/payment_desc';
    

    const XML_PATH_PAYMENT_GATEWAY_BOLETO_ACQUIRER          = 'payment/rm_gateway_boleto/acquirer';
    const XML_PATH_PAYMENT_GATEWAY_BOLETO_INSTRUCTIONS          = 'payment/rm_gateway_boleto/instructions';
    const XML_PATH_PAYMENT_GATEWAY_BOLETO_DAYS_EXPIRE          = 'payment/rm_gateway_boleto/slip_expire';
    const XML_PATH_PAYMENT_GATEWAY_BOLETO_DESC         = 'payment/rm_gateway_boleto/payment_desc';

    const XML_PATH_PAYMENT_GATEWAY_TEF_ACQUIRER          = 'payment/rm_gateway_tef/acquirer';
    const XML_PATH_PAYMENT_GATEWAY_TEF_DESC         = 'payment/rm_gateway_tef/payment_desc';

    const XML_PATH_PAYMENT_GATEWAY_TEF_ACTIVE         = 'payment/rm_gateway_tef/active';
    const XML_PATH_PAYMENT_GATEWAY_BOLETO_ACTIVE      = 'payment/rm_gateway_boleto/active';
    const XML_PATH_PAYMENT_GATEWAY_KEY                = 'payment/rm_gateway/key';
    const XML_PATH_PAYMENT_GATEWAY_CC_FORCE_INSTALLMENTS = 'payment/rm_gateway_cc/force_installments_selection';

     /**
     * Store Manager
     *
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

     /**
     * Quote Session
     *
     * @var  \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

     /**
     * Quote Session
     *
     * @var  \Magento\Customer\Model\Customer
     */
    protected $customer;

    protected $authResponse;

    protected $_curl;

    /** @var \Magento\Framework\Serialize\SerializerInterface  */
    protected $serializer;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Customer\Model\Customer                 $customer
     * @param \Magento\Framework\App\Helper\Context            $context
     * @param Logger                                           $loggerHelper
     * @param \Magento\Framework\App\ProductMetadataInterface  $productMetadata
     * @param \Magento\Framework\Module\ModuleListInterface    $moduleList
     * @param \Magento\Framework\HTTP\Client\Curl              $curl
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */

	public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\Helper\Context $context,
        \Azpay\Gateway\Helper\Logger $loggerHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\SerializerInterface $serializer
 
    ) {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepo = $customer;
        $this->_logHelper  = $loggerHelper;
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->_curl = $curl;
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * Returns session ID from Gateway that will be used on JavaScript methods.
     * or FALSE on failure
     * @return bool|string
     */
    public function getSessionId()
    {
        $url = $this->getWsUrl('sessions');
        //@TODO Replace forbidden curl_*
        $ch = curl_init($url);
        $params['email'] = $this->getMerchantEmail();
        $params['token'] = $this->getToken();   
        $params['public_key'] = $this->getGatewayPubKey();    

        //@TODO Replace curl
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_POSTFIELDS      => http_build_query($params),
                CURLOPT_POST            => count($params),
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_TIMEOUT         => 45,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_SSL_VERIFYHOST  => false,
            )
        );

        $response = null;

        try{
            $response = curl_exec($ch);
        }catch(\Exception $e){
            return $e->getMessage();
        }

        libxml_use_internal_errors(true);

        $this->authResponse = $response;
        $xml = \simplexml_load_string($response);

        if (false === $xml) {
            //@TODO Remove curl
            if (curl_errno($ch) > 0) {
                $this->writeLog('Gateway API communication failure: ' . curl_error($ch));
            } else {
                $this->writeLog(
                    'Authentication failed with Gateway API. Check registered email and token.
                    Payback return: ' . $response
                );
            }
            return false;
        }

        return (string)$xml->id;
    }

    public function getAuthResponse() {
        return $this->authResponse;
    }

    public function getAmbiente()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_AMBIENTE);
    }

    public function getMerchantId()
    {
        return $this->scopeConfig->getValue(self::PAYMENT_CONFIG_MERCHANT_ID);
    }

    public function getEnvironment()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_AMBIENTE) == 'teste' ? Environment::SANDBOX : Environment::PRODUCTION;
    }

    public function getMerchantKey()
    {
        return $this->scopeConfig->getValue(self::PAYMENT_CONFIG_MERCHANT_KEY);
    }

    /**
     * Return merchant e-mail setup on admin
     * @return string
     */
    public function getMerchantEmail()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_EMAIL);
    }

    public function getValidateRg()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_RG_REQUIRED);
    }

    public function getValidateTelefone()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_TELEFONE_REQUIRED);
    }
    
    
    public function getCcFraudMethod(){
        $sd = $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_ANTIFRAUD);
        if ($sd == 'kondutoscore') $method = 'score';
        if ($sd == 'clearsale') $method = 'start';
        if ($sd == 'fcontrol') $method = 'score';
        return $method;

    }
    public function getCcFraudOperator(){
        $sd = $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_ANTIFRAUD);
        if ($sd == 'kondutoscore') $operator = 'konduto';
        if ($sd == 'clearsale') $operator = 'clearsale';
        if ($sd == 'fcontrol') $operator = 'fcontrol';
        return $operator;
    }

    public function getCcCanAntifraud()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_CAN_ANTIFRAUD);
    }

    public function getCcCapture()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_CAN_CAPTURE);
    }

    public function getCcInstallments()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_MAX_INSTALLMENTS);
    }

    public function getCcInterestRate()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_INTEREST_RATE);
    }

    public function getCcSmallestInstallment(){
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_SMALLEST_INSTALLMENTS);
    }

    public function getCcInterest(){
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_CREDIT_INTEREST );
    }

    public function getCdCanAntifraud()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CD_CAN_ANTIFRAUD);
    }

    public function getCdDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CD_DESC);
    }

    public function getBoletoDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_BOLETO_DESC);
    }

    public function getTefDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_TEF_DESC);
    }

    

    public function getCdFraudMethod(){
        $sd = $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CD_ANTIFRAUD);
        if ($sd == 'kondutoscore') $method = 'score';
        if ($sd == 'clearsale') $method = 'start';
        if ($sd == 'fcontrol') $method = 'score';
        return $method;

    }
    public function getCdFraudOperator(){
        $sd = $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CD_ANTIFRAUD);
        if ($sd == 'kondutoscore') $operator = 'konduto';
        if ($sd == 'clearsale') $operator = 'clearsale';
        if ($sd == 'fcontrol') $operator = 'fcontrol';
        return $operator;
    }
    

    
    /**
     * Check if debug mode is active
     * @return bool
     */
    public function isDebugActive()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_DEBUG);
    }

    public function getBoletoAcquirer()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_BOLETO_ACQUIRER);
    }

    public function getBoletoInstructions()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_BOLETO_INSTRUCTIONS);
    }

    public function getBoletoDaysExpire()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_BOLETO_DAYS_EXPIRE);
    }

    public function getTefAcquirer()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_TEF_ACQUIRER);
    }

    public function getCdAcquirer()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CD_ACQUIRER);
    }

    public function getCcAcquirer()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_ACQUIRER);
    }

    public function getCcSoftDescriptor()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_SOFT_DESCRIPTOR);
    }

     /**
     * Get Gateway Public key (if exists)
     * @return string
     */
    public function getGatewayPubKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_KEY);
    }

   /**
     * Write something to gateway.log
     * @param $obj mixed|string
     */
    public function writeLog($obj)
    {
        if ($this->isDebugActive()) {
            $this->_logHelper->writeLog($obj);
        }
    }

    /**
     * Get current. Return FALSE if empty.
     * @return string | false
     */
    public function getToken()
    {
        $token = $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_TOKEN);
        if (empty($token)) {
            return false;
        }

        return $token;
    }

	/**
     * Return serialized (json) string with module configuration
     * return string
     */
    public function getConfigJs()
    {
        $config = array(
            'active_methods' => array(
                'cd' => $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CD_ACTIVE),
                'cc' => $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_ACTIVE),
                'boleto' => $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_BOLETO_ACTIVE),
                'tef' => $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_TEF_ACTIVE)
            ),
            'flag' => $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_FLAG),
            'debug' => $this->isDebugActive(),
            'GatewaySessionId' => $this->getSessionId(),
            'show_total' => $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_SHOW_TOTAL),
            'force_installments_selection' =>
                $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_GATEWAY_CC_FORCE_INSTALLMENTS)
        );
        return json_encode($config);
    }

    /**
     * Return store base url
     * return string
     */
    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

     /**
     * Return GrandTotal
     * return decimal
     */
    public function getGrandTotal()
    {
        return  $this->checkoutSession->getQuote()->getGrandTotal();
    }

    /**
     * Get payment hashes (sender_hash & credit_card_token) from session
     * @param string 
     * @return bool|string
     */
    public function getPaymentHash($param = null)
    {
        $psPayment = $this->checkoutSession->getData('PsPayment');
       
        /*$psPayment = $this->serializer->unserialize($psPayment);
//         $this->writeLog('getPaymentHash'.json_encode($psPayment));
        if (is_null($param)) {
            return $psPayment;
        }

        if (isset($psPayment[$param])) {
            return $psPayment[$param];
        }*/

        return false;
    }

    /**
     * Get CC Owner Data hashes (credit_card_owner & cpf) from session
     * @param string 
     * @return bool|string
     */
    public function getCCOwnerData($param = null)
    {
        $psCcOwner = $this->checkoutSession->getData('PsOwnerdata');        
        $psCcOwner = $this->serializer->unserialize($psCcOwner);

        if (isset($psCcOwner[$param])) {
            return $psCcOwner[$param];
        }

        return false;
    }

     /**
     * Check if CPF should be visible with other payment fields
     * @return bool
     */
    public function isCpfVisible()
    {
        $customerCpfAttribute = $this->scopeConfig->getValue('payment/rm_gateway/customer_cpf_attribute');
        return empty($customerCpfAttribute);
    }

    /**
     * Return Installment Qty
     * return int
     */
    public function getInstallmentQty()
    {
        return 5;
    }

    /**
     * Call Gateway API to place an order (/transactions)
     * @param $params
     * @param $payment
     * @param $type
     *
     * @return SimpleXMLElement
     */
    public function callApi($params, $payment, $type='transactions')
    {
        $params['public_key'] = $this->getGatewayPubKey();
        $params = $this->convertEncoding($params);
        $paramsObj = new \Magento\Framework\DataObject(array('params'=>$params));

        $params = $paramsObj->getParams();
        $paramsString = $this->convertToCURLString($params);

        $this->writeLog('Parameters being sent to API (/'.$type.'): '. var_export($params, true));

        $this->writeLog('WSDL URL:'.$this->getWsUrl($type));

        //@TODO Remove curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getWsUrl($type));
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsString);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgentDetails());

        try{
            $response = curl_exec($ch);
        }catch(\Exception $e){
            throw new \Magento\Framework\Validator\Exception('Communication failure with Gateway (' . $e->getMessage() . ')');
        }

        //@TODO Remove curl
        if (curl_error($ch)) {
            //@TODO Remove curl
            $this->writeLog('-----Curl error response----: ' . var_export(curl_error($ch), true));
            throw new \Magento\Framework\Validator\Exception(curl_error($ch));
        }
        //@TODO Remove curl
        curl_close($ch);

        $this->writeLog('Retorno Gateway (/'.$type.'): ' . var_export($response, true));

        $xml = \simplexml_load_string(trim($response));

        if ($xml->error->code) {

            $errArray = array();
            $xmlError = json_decode(json_encode($xml), true);
            $xmlError = $xmlError['error'];
            
			if(isset($xmlError['code'])) {
				$errArray[] = $xmlError['message'];
			} else {
				foreach ($xmlError as $xmlErr) {					
					$errArray[] = $xmlErr['message'];
				}
			}
            
			$errArray = implode(",", $errArray);
            if($errArray) {
                throw new \Magento\Framework\Validator\Exception(__($errArray));
            }

            $this->setSessionVl($errArray);
        }

        if (false === $xml) {
            switch($response){
                case 'Unauthorized':
                    $this->writeLog(
                        'Token / email not authorized by Gateway. Check your settings on the panel.'
                    );
                    break;
                case 'Forbidden':
                    $this->writeLog('Unauthorized access to Api Gateway. Make sure you have permission to  use this service. Return: ' . var_export($response, true)
                    );
                    break;
                default:
                    $this->writeLog('Unexpected return of Gateway. Return: ' . $response);
            }
            throw new \Magento\Framework\Validator\Exception(
                'There was a problem processing your request / payment. Please contact us.'
            );
        }

        return $xml;
    }

    /**
     * Convert array values to utf-8
     * @param array $params
     *
     * @return array
     */
    protected function convertEncoding(array $params)
    {
        foreach ($params as $k => $v) {
            $params[$k] = utf8_decode($v);
        }
        return $params;
    }

    /**
     * Convert API params (already ISO-8859-1) to url format (curl string)
     * @param array $params
     *
     * @return string
     */
    protected function convertToCURLString(array $params)
    {
        $fieldsString = '';
        foreach ($params as $k => $v) {
            $fieldsString .= $k.'='.urlencode($v).'&';
        }
        return rtrim($fieldsString, '&');
    }

    /**
     * Returns associative array with required parameters to API, used on CC method calls
     * @return array
     */
    public function getCreditCardApiCallParams(\Magento\Sales\Model\Order $order, $payment)
    {
        $params = array(
            'email'             => $this->getMerchantEmail(),
            'token'             => $this->getToken(),
            'paymentMode'       => 'default',
            'paymentMethod'     =>  'creditCard',
            'receiverEmail'     =>  $this->getMerchantEmail(),
            'currency'          => 'BRL',
            'creditCardToken'   => $payment->getAdditionalInformation('credit_card_token'),
            'reference'         => $order->getIncrementId(),
            'extraAmount'       => $this->getExtraAmount($order),
            'notificationURL'   => $this->getStoreUrl().'gateway/notification/index',
        );
        $params = array_merge($params, $this->getItemsParams($order));
        $params = array_merge($params, $this->getSenderParams($order, $payment));
        $params = array_merge($params, $this->getAddressParams($order, 'shipping'));
        $params = array_merge($params, $this->getAddressParams($order, 'billing'));
        $params = array_merge($params, $this->getCreditCardHolderParams($order, $payment));
        $params = array_merge($params, $this->getCreditCardInstallmentsParams($order, $payment));

        return $params;
    }

    /**
     * Calculates the "Exta" value that corresponds to Tax values minus Discount given
     * It makes the correct discount to be shown correctly on Gateway
     * @param Mage_Sales_Model_Order $order
     *
     * @return float
     */
    public function getExtraAmount($order)
    {
        $discount = $order->getDiscountAmount();
        $taxAmount = $order->getTaxAmount();
        $extra = $discount + $taxAmount;

        if ($this->shouldSplit($order)) {
            $extra += 0.01;
        }

        //Discounting gift products
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if ($item->getPrice() == 0) {
                $extra -= 0.01 * $item->getQtyOrdered();
            }
        }
        return number_format($extra, 2, '.', '');
    }

     /**
     * Return items information, to be send to API
     * @param Magento\Sales\Model\Order $order
     * @return array
     */
    public function getItemsParams(\Magento\Sales\Model\Order $order)
    {
        $return = array();
        $items = $order->getAllVisibleItems();
        if ($items) {
            $itemsCount = count($items);
            for ($x=1, $y=0; $x <= $itemsCount; $x++, $y++) {
                $itemPrice = $items[$y]->getPrice();
                $qtyOrdered = $items[$y]->getQtyOrdered();
                $return['itemId'.$x] = $items[$y]->getId()? $items[$y]->getId() : $items[$y]->getData('quote_item_id');
                $return['itemDescription'.$x] = substr($items[$y]->getName(), 0, 100);
                $return['itemAmount'.$x] = number_format($itemPrice, 2, '.', '');
                $return['itemQuantity'.$x] = (int)$qtyOrdered;

                //We can't send 0.00 as value to Gateway. Will be discounted on extraAmount.
                if ($itemPrice == 0) {
                    $return['itemAmount'.$x] = 0.01;
                }
            }
        }
        return $return;
    }

    /**
     * Return an array with Sender(Customer) information to be used on API call
     *
     * @param Magento\Sales\Model\Order $order
     * @param $payment
     * @return array
     */
    public function getSenderParams(\Magento\Sales\Model\Order $order, $payment)
    {
        $digits = new \Zend\Filter\Digits();
        $cpf = $this->getCustomerCpfValue($order, $payment);

        $phone = $this->extractPhone($order->getBillingAddress()->getTelephone());

        if($order->getCustomerIsGuest()){
            $senderName = $this->removeDuplicatedSpaces(
            sprintf('%s %s', $order->getBillingAddress()->getFirstname(), $order->getBillingAddress()->getLastname())
            );
        }else{
             $senderName = $this->removeDuplicatedSpaces(
            sprintf('%s %s', $order->getCustomerFirstname(), $order->getCustomerLastname())
            );
        }

        $senderName = substr($senderName, 0, 50);

        $return = array(
            'senderName'    => $senderName,
            'senderEmail'   => trim($order->getCustomerEmail()),
            'senderHash'    => $this->getPaymentHash('sender_hash'),
            'senderCPF'     => $digits->filter($cpf),
            'senderAreaCode'=> $phone['area'],
            'senderPhone'   => $phone['number'],
        );
        if (strlen($return['senderCPF']) > 11) {
            $return['senderCNPJ'] = $return['senderCPF'];
            unset($return['senderCPF']);
        }

        return $return;
    }

    /**
     * Returns an array with credit card's owner (Customer) to be used on API
     * @param Magento\Sales\Model\Order $order
     * @param $payment
     * @return array
     */
    public function getCreditCardHolderParams(\Magento\Sales\Model\Order $order, $payment)
    {
        $digits = new \Zend\Filter\Digits();
        $cpf = $this->getCustomerCpfValue($order, $payment);

        //data
        $customer = $this->customerRepo->load($order->getCustomerId());        
        $phone = $this->extractPhone($order->getBillingAddress()->getTelephone());

                
        $holderName = $this->removeDuplicatedSpaces($payment['additional_information']['credit_card_owner']);
        $cardNumber = $this->removeDuplicatedSpaces($payment['additional_information']['credit_card_number']);
        $cardCvv = $this->removeDuplicatedSpaces($payment['additional_information']['credit_card_cvv']);
        // PADRÃO YYYYMM, ADICIONA 20+YYMM
        $cardExpiryDate = $payment['additional_information']['credit_card_exp_year'] . $payment['additional_information']['credit_card_exp_month'];
        if (strlen($cardExpiryDate) == 4){
            $cardExpiryDate = "20$cardExpiryDate";
        }
        $return = array(
            'holder_name'      => $holderName,
            'card_number' => $cardNumber,
            'card_cvv'       => $cardCvv,
            'card_expiry_date'  => $cardExpiryDate,
        );

        return $return;
    }
    
    public function getValueInstallments(\Magento\Sales\Model\Order $order, $payment){
        $installments = $payment['additional_information']['credit_card_installments'];
        
        $order_total = $order->getGrandTotal();
        $config_installments =  $this->getCcInstallments();
        $config_interest_rate = $this->getCcInterestRate();        
        $interest_rate = $config_interest_rate / 100;        
        $smallest_installment =  $this->getCcSmallestInstallment();
        $interest =  $this->getCcInterest();;
                
            
		for ( $i = 1; $i <= $config_installments; $i++ ) {
			$credit_total    = $order_total / $i;
			
			if ( $i >= $interest && 0 < $interest_rate ) {
				$interest_total = $order_total * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $i ) ) ) );
				$interest_order_total = $interest_total * $i;

				if ( $credit_total < $interest_total ) {
                    $credit_total    = $interest_total;
                    if ($i == $installments)
					return $interest_order_total;
				}
			}
			
        }

        return 0;
    }

    public function getInstallments(\Magento\Sales\Model\Order $order, $payment)
    {
        
        $installments = $payment['additional_information']['credit_card_installments'];
               
        return (int) $installments;
    }
    

    /**
     * Return an array with installment information to be used with API
     * @param Magento\Sales\Model\Order $order
     * @param $payment Magento\Sales\Model\Order\Payment
     * @return array
     */
    public function getCreditCardInstallmentsParams(\Magento\Sales\Model\Order $order, $payment)
    {
        $return = array();
        if ($payment->getAdditionalInformation('installment_quantity')
            && $payment->getAdditionalInformation('installment_value')) {
            $return = array(
                'installmentQuantity'   => $payment->getAdditionalInformation('installment_quantity'),
                'installmentValue'      => number_format(
                    $payment->getAdditionalInformation('installment_value'), 2, '.', ''
                ),
            );
        } else {
            $return = array(
                'installmentQuantity'   => '1',
                'installmentValue'      => number_format($order->getGrandTotal(), 2, '.', ''),
            );
        }
        return $return;
    }

    public function getCpf(\Magento\Sales\Model\Order $order, $payment){
        $digits = new \Zend\Filter\Digits();
        $cpf = $this->getCustomerCpfValue($order, $payment);
        return $digits->filter($cpf);
    }

    public function getRg(\Magento\Sales\Model\Order $order, $payment){
        $digits = new \Zend\Filter\Digits();
        $rg = $this->getCustomerRgValue($order, $payment);
        return $digits->filter($rg);
    }

    public function getCnpj(\Magento\Sales\Model\Order $order, $payment){
        $digits = new \Zend\Filter\Digits();
        $cpf = $this->getCustomerCnpjValue($order, $payment);
        return $digits->filter($cpf);
    }

    public function getAddress(\Magento\Sales\Model\Order $order, $type){
        $digits = new \Zend\Filter\Digits();

        //address attributes
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = ($type=='shipping' && !$order->getIsVirtual()) ?
            $order->getShippingAddress() : $order->getBillingAddress();
        $addressStreetAttribute = $this->scopeConfig->getValue('payment/rm_gateway/address_street_attribute');
        $addressStreet = $this->getAddressAttributeValue($address, $addressStreetAttribute);
        $addrLine = $address->getStreet();
        return $addrLine[0];
    }

    public function getCity(\Magento\Sales\Model\Order $order, $type){
        $digits = new \Zend\Filter\Digits();

        //address attributes
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = ($type=='shipping' && !$order->getIsVirtual()) ?
            $order->getShippingAddress() : $order->getBillingAddress();
        
        return $address->getCity();
    }

    public function getState(\Magento\Sales\Model\Order $order, $type){
        $digits = new \Zend\Filter\Digits();

        //address attributes
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = ($type=='shipping' && !$order->getIsVirtual()) ?
            $order->getShippingAddress() : $order->getBillingAddress();
        
        return $this->getStateCode($address->getRegion());
    }

    public function getPostcode(\Magento\Sales\Model\Order $order, $type){
        $digits = new \Zend\Filter\Digits();

        //address attributes
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = ($type=='shipping' && !$order->getIsVirtual()) ?
            $order->getShippingAddress() : $order->getBillingAddress();
        
        return preg_replace('/[^0-9]/', '', $digits->filter($address->getPostcode()));
    }



    /**
     * Return an array with address (shipping/billing) information to be used on API
     * @param Magento\Sales\Model\Order $order
     * @param string (billing|shipping) $type
     * @return array
     */
    public function getAddressParams(\Magento\Sales\Model\Order $order, $type)
    {
        $digits = new \Zend\Filter\Digits();

        //address attributes
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = ($type=='shipping' && !$order->getIsVirtual()) ?
            $order->getShippingAddress() : $order->getBillingAddress();
        $addressStreetAttribute = $this->scopeConfig->getValue('payment/rm_gateway/address_street_attribute');
        $addressNumberAttribute = $this->scopeConfig->getValue('payment/rm_gateway/address_number_attribute');
        $addressComplementAttribute = $this->scopeConfig->getValue('payment/rm_gateway/address_complement_attribute');
        $addressNeighborhoodAttribute = $this->scopeConfig->getValue('payment/rm_gateway/address_neighborhood_attribute');

        //gathering address data
        $addressStreet = $this->getAddressAttributeValue($address, $addressStreetAttribute);
        $addressNumber = $this->getAddressAttributeValue($address, $addressNumberAttribute);
        $addressComplement = $this->getAddressAttributeValue($address, $addressComplementAttribute);
        $addressDistrict = $this->getAddressAttributeValue($address, $addressNeighborhoodAttribute);
        $addressPostalCode = $digits->filter($address->getPostcode());
        $addressCity = $address->getCity();
        $addressState = $this->getStateCode($address->getRegion());

        $return = array(
            $type.'AddressStreet'     => substr($addressStreet, 0, 80),
            $type.'AddressNumber'     => substr($addressNumber, 0, 20),
            $type.'AddressComplement' => substr($addressComplement, 0, 40),
            $type.'AddressDistrict'   => substr($addressDistrict, 0, 60),
            $type.'AddressPostalCode' => $addressPostalCode,
            $type.'AddressCity'       => substr($addressCity, 0, 60),
            $type.'AddressState'      => $addressState,
            $type.'AddressCountry'    => 'BRA',
        );

        //shipping specific
        if ($type == 'shipping') {
            $shippingType = $this->getShippingType($order);
            $shippingCost = $order->getShippingAmount();
            $return['shippingType'] = $shippingType;
            if ($shippingCost > 0) {
                if ($this->shouldSplit($order)) {
                    $shippingCost -= 0.01;
                }
                $return['shippingCost'] = number_format($shippingCost, 2, '.', '');
            }else {
                $return['shippingCost'] = '0.00';
            }
        }
        return $return;
    }

    /**
     * Returns customer's CPF based on your module configuration
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Payment_Model_Method_Abstract $payment
     *
     * @return mixed
     */
    private function getCustomerCpfValue(\Magento\Sales\Model\Order $order, $payment)
    {
        $customerCpfAttribute = $this->scopeConfig->getValue('payment/rm_gateway/customer_cpf_attribute');

        if (empty($customerCpfAttribute)) { //Asked with payment data
            if (isset($payment['additional_information']['cpf'])) {
                return $payment['additional_information']['cpf'];
            }
        }
        $entity = explode('|', $customerCpfAttribute);
        $cpf = '';
        if (count($entity) == 1 || $entity[0] == 'customer') {
            if (count($entity) == 2) {
                $customerCpfAttribute = $entity[1];
            }
            $customer = $order->getCustomer();

            $cpf = $order->getData('customer_' . $customerCpfAttribute);
        } else if (count($entity) == 2 && $entity[0] == 'billing' ) { //billing
            $cpf = $order->getShippingAddress()->getData($entity[1]);
        }

        if ($order->getCustomerIsGuest() && empty($cpf)) {
            $cpf = $order->getData('customer_' . $customerCpfAttribute);
        }

        $cpfObj = new \Magento\Framework\DataObject(array('cpf'=>$cpf));

        return $cpfObj->getCpf();
    }

    private function getCustomerRgValue(\Magento\Sales\Model\Order $order, $payment)
    {
        $customerCpfAttribute = $this->scopeConfig->getValue('payment/rm_gateway/customer_rg_attribute');

        if (empty($customerCpfAttribute)) { //Asked with payment data
            if (isset($payment['additional_information']['rg'])) {
                return $payment['additional_information']['rg'];
            }
        }
        $entity = explode('|', $customerCpfAttribute);
        $cpf = '';
        if (count($entity) == 1 || $entity[0] == 'customer') {
            if (count($entity) == 2) {
                $customerCpfAttribute = $entity[1];
            }
            $customer = $order->getCustomer();

            $cpf = $order->getData('customer_' . $customerCpfAttribute);
        } else if (count($entity) == 2 && $entity[0] == 'billing' ) { //billing
            $cpf = $order->getShippingAddress()->getData($entity[1]);
        }

        if ($order->getCustomerIsGuest() && empty($cpf)) {
            $cpf = $order->getData('customer_' . $customerCpfAttribute);
        }

        $cpfObj = new \Magento\Framework\DataObject(array('cpf'=>$cpf));

        return $cpfObj->getCpf();
    }

    private function getCustomerCnpjValue(\Magento\Sales\Model\Order $order, $payment)
    {        
        if (isset($payment['additional_information']['cnpj']))
        return $payment['additional_information']['cnpj'];
        return '';
    }

     /**
     * Extracts phone area code and returns phone number, with area code as key of the returned array
     * @author Azpay
     * @param string $phone
     * @return array
     */
    private function extractPhone($phone)
    {
        $digits = new \Zend\Filter\Digits();
        $phone = $digits->filter($phone);
        //se começar com zero, pula o primeiro digito
        if (substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1, strlen($phone));
        }
        $originalPhone = $phone;

        $phone = preg_replace('/^(\d{2})(\d{7,9})$/', '$1-$2', $phone);

        if (is_array($phone) && count($phone) == 2) {
            list($area, $number) = explode('-', $phone);
            return array(
                'area' => $area,
                'number'=>$number
            );
        }

        return array(
            'area' => (string)substr($originalPhone, 0, 2),
            'number'=> (string)substr($originalPhone, 2, 9),
        );
    }

    /**
     * Remove duplicated spaces from string
     * @param $string
     * @return string
     */
    public function removeDuplicatedSpaces($string)
    {
        $string = static::normalizeChars($string);

        return preg_replace('/\s+/', ' ', trim($string));
    }

     /**
     * Replace language-specific characters by ASCII-equivalents.
     * @see http://stackoverflow.com/a/16427125/529403
     * @param string $s
     * @return string
     */
    public static function normalizeChars($s)
    {
        $replace = array(
            'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'È' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N', 'Ò' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y',
            'ä' => 'a', 'ã' => 'a', 'á' => 'a', 'à' => 'a', 'å' => 'a', 'æ' => 'ae', 'è' => 'e', 'ë' => 'e', 'ì' => 'i',
            'í' => 'i', 'î' => 'i', 'ï' => 'i', 'Ã' => 'A', 'Õ' => 'O',
            'ñ' => 'n', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'ú', 'û' => 'u', 'ü' => 'ý', 'ÿ' => 'y',
            'Œ' => 'OE', 'œ' => 'oe', 'Š' => 'š', 'Ÿ' => 'Y', 'ƒ' => 'f', 'Ğ'=>'G', 'ğ'=>'g', 'Š'=>'S',
            'š'=>'s', 'Ş'=>'S', 'ș'=>'s', 'Ș'=>'S', 'ş'=>'s', 'ț'=>'t', 'Ț'=>'T', 'ÿ'=>'y', 'Ž'=>'Z', 'ž'=>'z'
        );
        return preg_replace('/[^0-9A-Za-zÃÁÀÂÇÉÊÍÕÓÔÚÜãáàâçéêíõóôúü.\-\/ ]/u', '', strtr($s, $replace));
    }

    /**
     * Should split shipping? If grand total is equal to discount total.
     * Gateway needs to receive product values > R$0,00, even if you need to invoice only shipping
     * and would like to give producs for free.
     * In these cases, splitting will add R$0,01 for each product, reducing R$0,01 from shipping total.
     *
     * @param $order
     *
     * @return bool
     */
    private function shouldSplit($order)
    {
        $discount = $order->getDiscountAmount();
        $taxAmount = $order->getTaxAmount();
        $extraAmount = $discount + $taxAmount;

        $totalAmount = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            $totalAmount += $item->getRowTotal();
        }
        return (abs($extraAmount) == $totalAmount);
    }

     /**
     * Return shipping code based on Gateway Documentation
     * 1 – PAC, 2 – SEDEX, 3 - Desconhecido
     * @param \Magento\Sales\Model\Order $order
     *
     * @return string
     */
    private function getShippingType(\Magento\Sales\Model\Order $order)
    {
        $method =  strtolower($order->getShippingMethod());
        if (strstr($method, 'pac') !== false) {
            return '1';
        } else if (strstr($method, 'sedex') !== false) {
            return '2';
        }
        return '3';
    }

     /**
     * Gets the shipping attribute based on one of the id's from
     * Azpay_Gateway_Model_Source_Customer_Address_*
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @param string $attributeId
     *
     * @return string
     */
    private function getAddressAttributeValue($address, $attributeId)
    {
        $isStreetline = preg_match('/^street_(\d{1})$/', $attributeId, $matches);

        if ($isStreetline !== false && isset($matches[1])) { //uses streetlines
             $street[1] = $address->getStreetLine(1); //street
             $street[2] = $address->getStreetLine(2); //number
             $street[3] = !$address->getStreetLine(4) ? '' : $address->getStreetLine(3); // complement
             $street[4] = !$address->getStreetLine(4) ? $address->getStreetLine(3) : $address->getStreetLine(4); // neighborhood
             $lineNum = (int)$matches[1];
             return $street[$lineNum];
        } else if ($attributeId == '') { //do not tell gateway
            return '';
        }
        return (string)$address->getData($attributeId);
    }

    /**
     * Get BR State code even if it was typed manually
     * @param $state
     *
     * @return string
     */
    public function getStateCode($state)
    {
        if(strlen($state) == 2 && is_string($state))
        {
            return mb_convert_case($state,MB_CASE_UPPER);
        }
        else if(strlen($state) > 2 && is_string($state))
        {
            $state = static::normalizeChars($state);
            $state = trim($state);
            $state = $this->stripAccents($state);
            $state = mb_convert_case($state, MB_CASE_UPPER);
            $codes = array(
                'AC'=>'ACRE',
                'AL'=>'ALAGOAS',
                'AM'=>'AMAZONAS',
                'AP'=>'AMAPA',
                'BA'=>'BAHIA',
                'CE'=>'CEARA',
                'DF'=>'DISTRITO FEDERAL',
                'ES'=>'ESPIRITO SANTO',
                'GO'=>'GOIAS',
                'MA'=>'MARANHAO',
                'MT'=>'MATO GROSSO',
                'MS'=>'MATO GROSSO DO SUL',
                'MG'=>'MINAS GERAIS',
                'PA'=>'PARA',
                'PB'=>'PARAIBA',
                'PR'=>'PARANA',
                'PE'=>'PERNAMBUCO',
                'PI'=>'PIAUI',
                'RJ'=>'RIO DE JANEIRO',
                'RN'=>'RIO GRANDE DO NORTE',
                'RO'=>'RONDONIA',
                'RS'=>'RIO GRANDE DO SUL',
                'RR'=>'RORAIMA',
                'SC'=>'SANTA CATARINA',
                'SE'=>'SERGIPE',
                'SP'=>'SAO PAULO',
                'TO'=>'TOCANTINS'
            );
            $code = array_search($state, $codes);
            if (false !== $code) {
                return $code;
            }
        }
        return $state;
    }

    /**
     * Replace accented characters
     * @param $string
     *
     * @return string
     */
    public function stripAccents($string)
    {
        return preg_replace('/[`^~\'"]/', null, iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }

    /**
     * Returns Webservice URL based on selected environment (prod or sandbox)
     *
     * @param string $amend suffix
     * @param bool $useApp uses app?
     *
     * @return string
     */
    public function getWsUrl($amend ='', $useApp = false)
    {
        return self::XML_PATH_PAYMENT_GATEWAY_WS_URL.$amend;
    }

    /**
     * Returns Store config value
     *
     * @param string
     * @return string/bool
     */
    public function getStoreConfigValue($scopeConfigPath)
    {
        return  $this->scopeConfig->getValue($scopeConfigPath);
    }

   
    public function setSessionVl($value)
    {
        return $this->checkoutSession->setCustomparam($value);
    }

    public function getSessionVl()
    {
        return $this->checkoutSession->getCustomparam();
    }

    public function getModuleInformation()
    {
        return $this->moduleList->getOne('Azpay_Gateway');
    }

    public function getMagentoVersion() {
        return $this->productMetadata->getVersion();
    }

    /**
     * Validate public key
     */
    public function validateKey() {


        //@TODO Remove hardcoded url
        $pubKey = $this->getGatewayPubKey();
        if(empty($pubKey)){
            return 'Public Key is empty.';
        }

        $url = 'http://ws.Azpay.net.br/pspro/v6/auth/' . $pubKey;
        $this->_curl->get($url);

        return $this->_curl->getBody();
    }


    /**
     * Get environment details for usage statistics
     * return string
     */
    public function getUserAgentDetails()
    {
        $ua = 'Gateway M2/';
        $ua .= $this->moduleList->getOne('Azpay_Gateway')['setup_version'];

        $ua .= ' (Magento ' . $this->getMagentoVersion() . ')';
        return $ua;
    }
    
    
    public function getBoletoApiCallParams($order, $payment)
    {
        $params = array(
            'email' => $this->getMerchantEmail(),
            'token' => $this->getToken(),
            'paymentMode'   => 'default',
            'paymentMethod' =>  'boleto',
            'receiverEmail' =>  $this->getMerchantEmail(),
            'currency'  => 'BRL',
            'reference'     => $order->getIncrementId(),
            'extraAmount'=> $this->getExtraAmount($order),
            'notificationURL' => $this->getStoreUrl().'gateway/notification/index',
        );
        
        $params = array_merge($params, $this->getItemsParams($order));
        $params = array_merge($params, $this->getSenderParams($order, $payment));
        $params = array_merge($params, $this->getAddressParams($order, 'shipping'));
        $params = array_merge($params, $this->getAddressParams($order, 'billing'));
        
        return $params;

    }
    
    public function getTefApiCallParams($order, $payment)
    {
        $params = $this->getBoletoApiCallParams($order, $payment);
        $params['paymentMethod'] = 'eft';
        $params['bankName'] = $payment['additional_information']['tef_bank'];
        return $params;
    }
}
