<?php
namespace Azpay\Gateway\Block;
class Order extends \Magento\Framework\View\Element\Template
{
    public function _prepareLayout()
    {   	
        parent::_prepareLayout();
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Front1 View'));   
        return $this;
    }
}