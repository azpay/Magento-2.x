<?php
namespace Azpay\Gateway\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;

class RestrictAddToCart implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    protected $request;
    protected $product;
 
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RequestInterface $request,
        Product $product
    )
    {
        $this->_messageManager = $messageManager;
        $this->request = $request;
        $this->product = $product;
    }
 
    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $postValues = $this->request->getPostValue();
        $productId = $postValues['product'];
        $addProduct = $this->product->load($productId);

        $found = false;
        $items = $cart->getQuote()->getAllItems();
        $count = 0;
        foreach ($items as $item) {
           //logic for item that you can not buy together
           if ($item->getProduct()->getTypeId() == 'subscription_product_type') {
                $count++;
            }else{
                $found = true;                          
            }
        }
        
        $is_sub = $addProduct->getTypeId() == 'subscription_product_type';
        if ($count > 0 || $found && $is_sub) {
                $this->_messageManager->addError(__('Seu carrinho possui produto de outro tipo, é possível apenas um tipo de produto / um produto recorrente'));
                //set false if you not want to add product to cart
                $observer->getRequest()->setParam('product', false);
                return $this;
         }
 
        return $this;
    }
}