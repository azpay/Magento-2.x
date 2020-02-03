<?php
namespace Azpay\Gateway\Model\System\Config\Source;

/**
 * Class Cpf
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Model\System\Config\Source
 */
class Cpf implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Azpay\Gateway\Helper\Internal
     */
    protected $gatewayHelper;

    /**
     * @param \Azpay\Gateway\Helper\Internal $gatewayHelper
     */
    public function __construct(
            \Azpay\Gateway\Helper\Internal $gatewayHelper
    ){
        $this->gatewayHelper = $gatewayHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $fields = $this->gatewayHelper->getFields('customer');
        $options = [];
        $options[] = array('value'=>'','label'=> __('Request along with other payment details'));

        foreach ($fields as $key => $value) {
            if (!is_null($value['frontend_label'])) {
                $options['customer|'.$value['frontend_label']] = array(
                    'value' => 'customer|'.$value['attribute_code'],
                    'label' => 'Customer: '.$value['frontend_label'] . ' (' . $value['attribute_code'] . ')'
                );
            }
        }

        $addressFields = $this->gatewayHelper->getFields('customer_address');
        foreach ($addressFields as $key => $value) {
            if (!is_null($value['frontend_label'])) {
                $options['address|'.$value['frontend_label']] = array(
                    'value' => 'billing|'.$value['attribute_code'],
                    'label' => 'Billing: '.$value['frontend_label'] . ' (' . $value['attribute_code'] . ')'
                );
            }
        }

        return $options;
    }
}