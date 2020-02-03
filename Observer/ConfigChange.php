<?php 

namespace Azpay\Gateway\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigChange implements ObserverInterface
{
	private $request;
	private $configWriter;
	private $gatewayHelper;
	public function __construct(
		RequestInterface $request,
		WriterInterface $configWriter,
		\Azpay\Gateway\Helper\Data $gatewayHelper
	)
	{
		$this->request = $request;
		$this->configWriter = $configWriter;
		$this->gatewayHelper = $gatewayHelper;

	}

	public function execute(EventObserver $observer)
	{
		$required = $this->gatewayHelper->getValidateTelefone();
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();		
		//Update Data into table
		$sql = "update eav_attribute set is_required=$required where attribute_code='telephone';";
		$connection->query($sql);

		return $this;
	}
}
?>