<?php
namespace Azpay\Gateway\Model\System\Config\Source;

/**
 * Class Attributes
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Model\System\Config\Source
 */
class Attributes implements \Magento\Framework\Option\ArrayInterface
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
        $fields = $this->gatewayHelper->getFields('customer_address');
         $options = array();

        foreach ($fields as $key => $value) {
            if (!is_null($value['frontend_label'])) {
                //in multiline cases, it allows to specify what each line means (i.e.: street, number)
                if ($value['attribute_code'] == 'street') {
                    $streetLines = $this->gatewayHelper->getStoreConfig('customer/address/street_lines');
                    for ($i = 1; $i <= $streetLines; $i++) {
                        $options[] = array('value' => 'street_'.$i, 'label' => 'Street Line '.$i);
                    }
                } else {
                    $options[] = array(
                        'value' => $value['attribute_code'],
                        'label' => $value['frontend_label'] . ' (' . $value['attribute_code'] . ')'
                    );
                }
            }
        }
        return $options;
    }
}