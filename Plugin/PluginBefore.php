<?php


namespace Azpay\Gateway\Plugin;

class PluginBefore
{
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view){
        $message ='Você quer mesmo capturar esta venda?';
        $message_cancel ='Você quer mesmo cancelar esta venda?';
        $url = '/gateway/ajax/capture?order_id=' . $view->getOrderId();
        $url_cancel = '/gateway/ajax/cancel?order_id=' . $view->getOrderId();

        $view->removeButton('order_cancel');

        $order = $view->getOrder();
        if (!$order) return;
        
        $status = $order->getStatus();
        $pm = $order->getPayment()->getMethodInstance()->getCode();
        if ($pm == 'rm_gateway_cc' && $status == 'processing' && !$order->hasInvoices()){
            $view->addButton(
                'capture',
                ['label' => __('Capture'), 'onclick' => "confirmSetLocation('{$message}', '{$url}')", 'class' => 'capture'],
                -1
            );
        }
        if ($status == 'processing')
        $view->addButton(
            'cancel',
            ['label' => __('Cancel'), 'onclick' => "confirmSetLocation('{$message_cancel}', '{$url_cancel}')", 'class' => 'cancel'],
            -1
        );


        return null;
    }

    /*public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {

        $this->_request = $context->getRequest();
        if($this->_request->getFullActionName() == 'sales_order_view'){
            $buttonList->add(
                'cancel',
                ['label' => __('Cancel'), 'onclick' => 'setLocation(window.location.href)', 'class' => 'reset'],
                -1
            );

            $buttonList->add(
                'capture',
                ['label' => __('Capture'), 'onclick' => 'setLocation(window.location.href)', 'class' => 'reset'],
                -1
            );
        }

    }*/
}

?>