<?php
namespace Azpay\Gateway\Controller\Test;

/**
 * Class GetConfig
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Controller\Test
 */
class GetConfig extends \Magento\Framework\App\Action\Action
{
    /**
     * GetConfig resultPageFactory
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $resultJsonFactory;

    /**
     * GetConfig constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Azpay\Gateway\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    )
    {
        $this->_helper = $helper;
        $this->resultJsonFactory = $jsonFactory;
        return parent::__construct($context);
    }

    /**
     * Function execute
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $tokenLen = strlen($this->_helper->getToken());
        $info = array(
            'Magento Version' => substr($this->_helper->getMagentoVersion(), 0, 1),
            'Azpay_Gateway' => array(
                'version'   => $this->_helper->getModuleInformation()['setup_version'],
                'debug'     => (boolean)$this->_helper->isDebugActive()
            ),
            'configJs'      => json_decode($this->_helper->getConfigJs()),
            'key_validate'  => $this->_helper->validateKey(),
            'token_consistency' => ($tokenLen == 32 || $tokenLen == 100) ? "Good" : "Token does not consist 32 or 100 characters"
        );

        $resultJson->setData($info);
        return $resultJson;
    }
}