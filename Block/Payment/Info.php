<?php

namespace Azpay\Gateway\Block\Payment;

class Info extends \Magento\Payment\Block\Info
{
	protected $_checkoutSession;
    protected $_orderFactory;
    protected $_scopeConfig;

    protected $_template = 'Azpay_Gateway::info/info.phtml';
    protected $gatewayHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Azpay\Gateway\Helper\Data $gatewayHelper,
        array $data = []
    ) {
		parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;     
        $this->gatewayHelper = $gatewayHelper;  
    }


    // Use this method to get ID    
    public function getRealOrderId()
    {
        $lastorderId = $this->_checkoutSession->getLastOrderId();
        return $lastorderId;
    }

    public function getOrder()
    {
        if ($this->_checkoutSession->getLastRealOrderId()) {
            return $this->_checkoutSession->getLastRealOrder();
        }
        if ($order = $this->getInfo()->getOrder()) {
            return $order;
        }
        return false;
    }
	
	public function getPaymentMethod()
    {
		$payment = $this->_checkoutSession->getLastRealOrder()->getPayment();        
		return $payment->getMethod();
	}

    public function getStatus()
    {
        $order = $this->getOrder();
        if ($order) return $order->getStatus();
        return '';
    }

    public function getTransactionId()
    {
        $order = $this->getOrder();        
        if ($order) {
            $payment = $order->getPayment();
            return $payment->getAdditionalInformation('tidId');
        }
        return '';
    }
	
    public function getPaymentInfo()
    {
        $order = $this->getOrder();
        if ($payment = $order->getPayment()) {
			$paymentMethod = $payment->getMethod();
			switch($paymentMethod)
			{
				case 'rm_gateway_boleto':
					return array(
						'tipo' => 'Boleto',
						'url' => $payment->getAdditionalInformation('boletoUrl'),
						'texto' => 'Clique aqui para imprimir seu boleto',
                        'desc' => $this->gatewayHelper->getBoletoDescription()
					);
					break;
				case 'rm_gateway_tef':                    
					return array(
						'tipo' => 'Débito Online (TEF)',
						'url' => $payment->getAdditionalInformation('transferUrl'),
						'texto' => 'Clique aqui para realizar o pagamento',
                        'desc' => $this->gatewayHelper->getTefDescription()
					);  
                case 'rm_gateway_cd':                    
                    return array(
                        'tipo' => 'Débito',
                        'url' => $payment->getAdditionalInformation('transferUrl'),
                        'texto' => 'Clique aqui para autorizar o pagamento',
                        'desc' => $this->gatewayHelper->getCdDescription()
                    );              
				break;
			}
		}
        return false;
    }
}
