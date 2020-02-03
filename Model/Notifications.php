<?php
namespace Azpay\Gateway\Model;

foreach (glob(dirname(dirname(__FILE__))."/vendor/brunopaz/php-sdk-gateway/src/gateway/API/*.php") as $filename)
{   
    //echo $filename . "<BR>";
    require_once $filename;
}

use \Gateway\API\Credential as Credential;
use \Gateway\API\Environment as Environment;
use \Gateway\API\Gateway as Gateway;
use \Gateway\API\Transaction as Transaction;
use \Gateway\API\Currency as Currency;
use \Gateway\API\Methods as Methods;
use \Gateway\API\Rebill as Rebill;
use \Gateway\API\Acquirers as Acquirers;
/**
 * Class Notifications
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Model
 */
class Notifications extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Gateway Helper
     *
     * @var Azpay\Gateway\Helper\Data;
     */ 
    protected $gatewayHelper;

    /**
     * Magento Sales Order Model
     *
     * @var \Magento\Sales\Model\Order
     */ 
    protected $orderModel;

    protected $orderRepository;

     /**
     * Magento transaction Factory
     *
     * @var \Magento\Framework\DB\Transaction
     */ 
    protected $transactionFactory;

    protected $checkoutSession;

    protected $resultRedirectFactory;

    private $messageManager;

    protected $_invoiceSender;

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
        \Magento\Sales\Api\Data\OrderInterface $orderModel,
        \Magento\Framework\DB\Transaction $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $commentSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
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

        $this->_invoiceSender = $invoiceSender;
        $this->gatewayHelper = $gatewayHelper;  
        $this->orderModel = $orderModel;
        $this->transactionFactory = $transactionFactory;
        $this->_commentSender = $commentSender;
        $this->checkoutSession = $checkoutSession;
        $this->resultRedirectFactory = $result;
        $this->messageManager = $messageManager;
    }

    public function captureNotificaton($orderId){
        $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->getMerchantKey(), $this->gatewayHelper->getEnvironment());

        $gateway = new Gateway($credential);
        
      
        $order = $this->orderModel->load($orderId);
        if (!$order->getId()) {
            $this->gatewayHelper->writeLog(
                sprintf('Request %s not found on system. Unable to process return.', $orderId)
            );
            return $this;
        }
        
        if ($order->getStatus() != 'processing') {
            $this->messageManager->addError('Add your success message');
            return;   
        }

        $payment = $order->getPayment();
        $ctid = $payment->getAdditionalInformation('tidId');
         if (isset($ctid)){
            $response = $gateway->Report($ctid);
    
            if ($response->canCapture()){
                $amount = $order->getGrandTotal();
                $response = $gateway->Capture($ctid, $amount * 100);  

                 if(!$order->hasInvoices()){

                    $invoice = $order->prepareInvoice();
                    $invoice->register()->pay();
                    $msg = sprintf('Captured payment. Transaction Identifier: %s', (string)$ctid);
                    $invoice->addComment($msg);
                    /*$invoice->sendEmail(
                        $this->gatewayHelper->getStoreConfigValue('payment/rm_gateway/send_invoice_email'),
                        'Payment received successfully.'
                    );*/

                    // salva o transaction id na invoice
                    if (isset($ctid)) {
                        $invoice->setTransactionId($ctid)->save();
                    }

                    $this->transactionFactory->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();

                    $order->addStatusHistoryComment(sprintf('Invoice # %s successfully created.', $invoice->getIncrementId()));
                    
                }    

                $payment->setSkipOrderProcessing(true);

                $payment
                    ->setTransactionId($response->getTransactionID())
                    ->setIsTransactionClosed(0);

                try {
                    $payment->save();
                    $order->save();
                    
                }catch(\Exception $e) {
                    $this->gatewayHelper->writeLog($e->getMessage());
                }    


            }
                 
        }
        
        
        return $this;   
      

    }

    public function cancelNotificaton($orderId){
        $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->getMerchantKey(), $this->gatewayHelper->getEnvironment());

        $gateway = new Gateway($credential);
        
      
        $order = $this->orderModel->load($orderId);
        if (!$order->getId()) {
            $this->gatewayHelper->writeLog(
                sprintf('Request %s not found on system. Unable to process return.', $orderId)
            );
            return $this;
        }
        

        if ($order->getStatus() == 'cancel' || $order->getStatus() == 'closed') {
            $this->messageManager->addError('Order com status ' . $order->getStatus());
            return $this;   
        }

        $payment = $order->getPayment();
        $ctid = $payment->getAdditionalInformation('tidId');

        if (isset($ctid)){
            $response = $gateway->Cancel($ctid);  
        
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);

            $payment->setSkipOrderProcessing(true);

            $payment                
                    ->setIsTransactionClosed(0);

            try {
                $payment->save();
                $order->save();
                
            }catch(\Exception $e) {
                echo $e;                
            }            
            
        }
               
        return $this;

    }

    public function proccessNotificaton($transactionID)
    {        
        $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->getMerchantKey(), $this->gatewayHelper->getEnvironment());

        $gateway = new Gateway($credential);
        
        //Recuperar reference do transaction        
        $response = $gateway->Report($transactionID);
        $reference = $response->getResponse();        
        $status = $reference['status'];
        $reference = $reference['order']['reference'];        

        $order = $this->orderModel->loadByIncrementId($reference);
        if (!$order->getId()) {
            $this->gatewayHelper->writeLog(
                sprintf('Request %s not found on system. Unable to process return.', $transactionID)
            );
            return $this;
        }
        
        $payment = $order->getPayment();
        $canRedirect = false;
        $message = '';        
        switch($status){
            case 0: //Criado
                $message = "Criada" ;
                break;
            case 1: //Autenticada
                $message =  "Autenticada" ;
                break;
            case 2: //Não-autenticada
                $message = "Não-autenticada";
                break;
            case 3: //Autorizada pela operadora
                $message = "Autorizada pela operadora" ;                
                break;
            case 4: //Não-autorizada pela operadora                
                $message = "Não-autorizada pela operadora";
                //$order->setState(\Magento\Sales\Model\Order::STATE_CLOSED);
                //$order->setStatus(\Magento\Sales\Model\Order::STATE_CLOSED);
                break;
            case 5: //Em cancelamento
                $message = "Em cancelamento";
                break;
            case 6: //Cancelado
                $message = "Cancelado";
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                break;
            case 7: //Em captura
                $message = "Em captura" ;
                break;
            case 8: //Capturada / Finalizada
                $message = "Capturada / Finalizada" ;
                $canRedirect = true;
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                if(!$order->hasInvoices()){

                    $invoice = $order->prepareInvoice();
                    $invoice->register()->pay();
                    $invoice->getOrder()->setCustomerNoteNotify(true);
                    $msg = sprintf('Captured payment. Transaction Identifier: %s', (string)$transactionID);
                    $invoice->addComment($msg);
                    
                    /*$invoice->sendEmail(
                        $this->gatewayHelper->getStoreConfigValue('payment/rm_gateway/send_invoice_email'),
                        'Payment received successfully.'
                    );
                    */
                    // salva o transaction id na invoice
                    if (isset($transactionID)) {
                        $invoice->setTransactionId($transactionID)->save();
                    }

                    $this->transactionFactory->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                    $order->addStatusHistoryComment(sprintf('Invoice # %s successfully created.', $invoice->getIncrementId()));                    

			        try {
			            $payment->save();
			            $order->save();
			            
			        }catch(\Exception $e) {
			            $this->gatewayHelper->writeLog($e->getMessage());
			        }

			        $this->_invoiceSender->send($invoice, true);
                }                
                
                break;
            case 9: //Não-capturada
                $message = "Não-capturada";
                break;
            case 10: //Pagamento Recorrente - Agendada
                $message = "Pagamento Recorrente - Agendada";
                $canRedirect = true;
                break;
            case 11: //Boleto Gerado
                $message = "Boleto gerado" ;
                $canRedirect = true;
                break;
        }

        
        $order->addStatusHistoryComment($message);
        
        try {
            $payment->save();
            $order->save();
            
        }catch(\Exception $e) {
            $this->gatewayHelper->writeLog($e->getMessage());
        }
        
        
        if ($order) {
            $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId())->setLastOrderId($order->getEntityId())
               ->setLastQuoteId($order->getQuoteId())
               ->setLastOrderStatus($order->getStatus());
            
        }    
        //header('Location: /checkout/onepage/success/');
        echo "<script type='text/javascript'>";
        echo "window.close();";
        echo "</script>";
        exit;
        if (!$canRedirect){
            /*$quote = $objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($order->getQuoteId());

            $quote->setReservedOrderId(null);
            $quote->setIsActive(true);
            $quote->removePayment();
            $quote->save();

            //replace the quote to the checkout session (I guess this is the better way)
            $this->checkoutSession->replaceQuote($quote);

            //OR add quote to cart
            $this->cart->setQuote($quote);*/

            //if your last order is still in the session (getLastRealOrder() returns order data) you can achieve what you need with this one line without loading the order:
            $this->checkoutSession->restoreQuote();
            header('Location: /checkout/');
            exit;            
        }else{
            header('Location: /checkout/onepage/success/');
            exit;
        }
        
    
    }

    /**
     * Processes notification XML data. XML is sent right after order is sent to Gateway, and on order updates.
     * @param SimpleXMLElement $resultXML
     */
    public function proccessNotificatonResult($resultXML, $_payment = false)
    {
        if (isset($resultXML->error)) {
            $errMsg = __((string)$resultXML->error->message);
            throw new \Magento\Framework\Validator\Exception(
              __(
                    'Problemas ao processar seu pagamento. %s(%s)',
                    $errMsg,
                    (string)$resultXML->error->code
                )
            );
        }

        if (isset($resultXML->reference)) {
            if(is_object($_payment) && $_payment instanceof \Magento\Payment\Model\InfoInterface) {
                $order = $_payment->getOrder();
                $payment = $_payment;
            }else {
                $orderNo = (string)$resultXML->reference;
                $order = $this->orderModel->loadByIncrementId($orderNo);
                if (!$order->getId()) {
                    $this->gatewayHelper->writeLog(
                        sprintf('Request %s not found on system. Unable to process return.', $orderNo)
                    );
                    return $this;
                }
                $payment = $order->getPayment();
            }

            $this->_code = $payment->getMethod();
            $processedState = $this->processStatus((int)$resultXML->status);

            $message = $processedState->getMessage();

            if ((int)$resultXML->status == 6) { //valor devolvido (gera credit memo e tenta cancelar o pedido)

                if ($order->canUnhold()) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_HOLDED);
                }

                if ($order->canCancel()) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                } else {
                    $this->gatewayHelper->writeLog("can't hold and can't cancel");
                    $payment->registerRefundNotification(floatval($resultXML->grossAmount));
                    $order->addStatusHistoryComment(
                        'Returned: Amount returned to buyer.'
                    );
                }
            }

            if ((int)$resultXML->status == 7 && isset($resultXML->cancellationSource)) {
                //Especificamos a fonte do cancelamento do pedido
                switch((string)$resultXML->cancellationSource)
                {
                    case 'INTERNAL':
                        $message .= __('Gateway itself denied or canceled the transaction.');
                        break;
                    case 'EXTERNAL':
                        $message .= __('The transaction was denied or canceled by the bank.');
                        break;
                }

                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            }

            if ($processedState->getStateChanged()) {

                // somente para o status 6 que edita o status do pedido - Weber
                if ((int)$resultXML->status != 6) {

                    $this->gatewayHelper->writeLog("State: ". $processedState->getState());

                    

                    if((int)$resultXML->status == 1 && is_object($_payment)) {
                        $_payment->setIsTransactionPending(true);
                    }

                }

            } else {
                $order->addStatusHistoryComment($message);
            }

            if ((int)$resultXML->status == 3) { 
                if(!$order->hasInvoices()){

                    $invoice = $order->prepareInvoice();
                    $invoice->register()->pay();
                    $msg = sprintf('Captured payment. Transaction Identifier: %s', (string)$resultXML->code);
                    $invoice->addComment($msg);
                    $invoice->sendEmail(
                        $this->gatewayHelper->getStoreConfigValue('payment/rm_gateway/send_invoice_email'),
                        'Payment received successfully.'
                    );

                    // salva o transaction id na invoice
                    if (isset($resultXML->code)) {
                        $invoice->setTransactionId((string)$resultXML->code)->save();
                    }

                    $this->transactionFactory->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                    $order->addStatusHistoryComment(sprintf('Invoice # %s successfully created.', $invoice->getIncrementId()));
                }
            }

            if(!is_object($_payment)) {
                try {
                    $payment->save();
                    $order->save();

                    if($processedState->getIsCustomerNotified()) {
                        $this->_commentSender->send($order, true, $message);
                    }
                }catch(\Exception $e) {
                    $this->gatewayHelper->writeLog($e->getMessage());
                }
            }

        } else {
            throw new \Magento\Framework\Validator\Exception(__('Invalid return. Order reference not found.'));
        }
    }

    /**
     * @param $notificationCode
     * @return SimpleXMLElement
     */
    public function getNotificationStatus($notificationCode)
    {
        //@TODO Remove hard coded URL
        $url = "https://ws.gateway.uol.com.br/v2/transactions/notifications/" . $notificationCode;

        $params = array('token' => $this->gatewayHelper->getToken(), 'email' => $this->gatewayHelper->getMerchantEmail());
        $url .= '?' . http_build_query($params);

        //@TODO Add ext-curl to composer
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        try {
            $return = curl_exec($ch);
        } catch (\Exception $e) {
            $this->gatewayHelper->writeLog(
                sprintf('Failed to catch return for notificationCode %s: %s(%d)', $notificationCode, curl_error($ch),
                    curl_errno($ch)
                )
            );
        }

        $this->gatewayHelper->writeLog(sprintf('Return of the Gateway to notificationCode %s: %s', $notificationCode, $return));

        libxml_use_internal_errors(true);
        $xml = \simplexml_load_string(trim($return));
        if (false === $xml) {
            $this->gatewayHelper->writeLog('Return XML notification Gateway in unexpected format. Return: ' . $return);
        }

        curl_close($ch);
        return $xml;
    }

     /**
     * Processes order status and return information about order status
     * @param $statusCode
     * @return Object
     */
    public function processStatus($statusCode)
    {
        $return = new \Magento\Framework\DataObject();
        $return->setStateChanged(true);
        $return->setIsTransactionPending(true); //payment is pending?

        switch($statusCode)
        {
            case '1':
                $return->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $return->setIsCustomerNotified($this->getCode()!='gateway_cc');

                $return->setMessage(
                    __('Awaiting payment: the buyer initiated the transaction, but so far Gateway has not received any payment information.')
                );
                break;
            case '2':
                $return->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                $return->setIsCustomerNotified(true);
                $return->setMessage(
                    __('Under review: the buyer chose to pay with a credit card and Gateway is analyzing the risk of the transaction.')
                );
                break;
            case '3':
                $return->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $return->setIsCustomerNotified(true);
                $return->setMessage(
                    __('Pay: the transaction was paid by the buyer and Gateway has already received a confirmation of the financial institution responsible for processing.')
                );
                $return->setIsTransactionPending(false);
                break;
            case '4':
                $return->setMessage(
                    __('Available: The transaction has been paid and has reached the end of its has been returned and there is no open dispute')
                );
                $return->setIsCustomerNotified(false);
                $return->setStateChanged(false);
                $return->setIsTransactionPending(false);
                break;
            case '5':
                $return->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $return->setIsCustomerNotified(false);
                $return->setIsTransactionPending(false);
                $return->setMessage(
                    __('In dispute: the buyer, within the term of release of the transaction, opened a dispute.')
                );
                break;
            case '6':
                $return->setData('state', \Magento\Sales\Model\Order::STATE_CLOSED);
                $return->setIsCustomerNotified(false);
                $return->setIsTransactionPending(false);
                $return->setMessage(__('Returned: The transaction amount was returned to the buyer.'));
                break;
            case '7':
                $return->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                $return->setIsCustomerNotified(true);
                $return->setMessage(__('Canceled: The transaction was canceled without being finalized.'));
                break;
            default:
                $return->setIsCustomerNotified(false);
                $return->setStateChanged(false);
                $return->setMessage(__('Invalid status code returned by Gateway. (%s)', $statusCode ));
        }
        return $return;
    }
}