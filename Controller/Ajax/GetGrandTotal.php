<?php
namespace Azpay\Gateway\Controller\Ajax;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class GetGrandTotal
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Controller\Ajax
 */
class GetGrandTotal extends \Magento\Framework\App\Action\Action
{
     /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     */ 
    protected $checkoutSession;

     /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
         \Magento\Framework\App\Action\Context $context
 
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
    * @return json
    */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);     
        try{
             $total = $this->checkoutSession->getQuote()->getGrandTotal();
             $result = array(
                'status'=> 'success',
                'total' => $total
            );
         }catch (\Exception $e) {
            $result = array('status'=> 'error','message' => $e->getMessage());
        }

        $resultJson->setData($result);         
        return $resultJson;
    }
}