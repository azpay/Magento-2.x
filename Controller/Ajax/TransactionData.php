<?php
namespace Azpay\Gateway\Controller\Ajax;
use Magento\Framework\Controller\ResultFactory;

foreach (glob(dirname(dirname(dirname(__FILE__)))."/vendor/brunopaz/php-sdk-gateway/src/gateway/API/*.php") as $filename)
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
 * Class UpdateInstallments
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Controller\Ajax
 */
class TransactionData extends \Magento\Framework\App\Action\Action
{
     /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     */ 
    protected $checkoutSession;

    /** @var \Magento\Framework\Serialize\SerializerInterface  */
    protected $serializer;

    /**
     * Gateway Helper
     *
     * @var Azpay\Gateway\Helper\Data;
     */ 
    protected $gatewayHelper;

    /**
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Action\Context $context,
        \Azpay\Gateway\Helper\Data $gatewayHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer
 
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
        $this->gatewayHelper = $gatewayHelper;
        
    }

    /**
    * @return json
    */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);     
        $transactionId =  $_GET['id'];
        try{
            
            $credential = new Credential($this->gatewayHelper->getMerchantId(), $this->gatewayHelper->getMerchantKey(), $this->gatewayHelper->getEnvironment());

            $gateway = new Gateway($credential);
            

            //Recuperar reference do transaction        
            $response = $gateway->Report($transactionId);
            $reference = $response->getResponse();        
            $status = $reference['status'];
            $reference = $reference['order']['reference'];        

             
            $result = array(
                'status'=> 'success',
                'status' => $status,
                'message' => __('Get Installments.')
            );
         }catch (\Exception $e) {
            $result = array('status'=> 'error','message' => $e->getMessage());
        }
        $resultJson->setData($result);
        return $resultJson;
    }


    function htmlInstallments(){
        $quote = $this->checkoutSession->getQuote();
        $subscription = false;
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() == 'subscription_product_type'){
                $subscription = true;
            }
        }

        $order_total = $quote->getGrandTotal();
        $installments =  $this->gatewayHelper->getCcInstallments();
        $config_interest_rate = $this->gatewayHelper->getCcInterestRate();        
        $interest_rate = $config_interest_rate / 100;        
        $smallest_installment =  $this->gatewayHelper->getCcSmallestInstallment();
        $interest =  $this->gatewayHelper->getCcInterest();;
        $html = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        if ($subscription) $installments = 1;
        
		for ( $i = 1; $i <= $installments; $i++ ) {
			$credit_total    = $order_total / $i;
			$credit_interest = sprintf( __( 'sem juros. Total: %s', 'gateway-woocommerce' ), $priceHelper->currency($order_total, true, false) );
			$smallest_value  = ( 5 <= $smallest_installment ) ? $smallest_installment : 5;

			if ( $i >= $interest && 0 < $interest_rate ) {
				$interest_total = $order_total * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $i ) ) ) );
				$interest_order_total = $interest_total * $i;

				if ( $credit_total < $interest_total ) {
					$credit_total    = $interest_total;
					$credit_interest = sprintf( __( 'com juros de %s%% a.m. Total: %s', 'gateway-woocommerce' ), $config_interest_rate, $priceHelper->currency($interest_order_total, true, false) );
				}
			}

			if ( 1 != $i && $credit_total < $smallest_value ) {
				continue;
			}

			$at_sight = ( 1 == $i ) ? 'gateway-at-sight' : '';

            if ($i == 1)
			$html .= '<option value="' . $i . '" class="' . $at_sight . '">' . sprintf( __( 'à vista. Total: %s', 'gateway-woocommerce' ), $priceHelper->currency($credit_total, true, false) ) . '</option>';
            else
            $html .= '<option value="' . $i . '" class="' . $at_sight . '">' . sprintf( __( '%sx de %s %s', 'gateway-woocommerce' ), $i, $priceHelper->currency($credit_total, true, false), $credit_interest ) . '</option>';
        }
        return $html;

    }
}