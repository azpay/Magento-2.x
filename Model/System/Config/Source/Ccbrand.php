<?php
namespace Azpay\Gateway\Model\System\Config\Source;

/**
 * Class Ccbrand Source model for CC flags
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Model\System\Config\Source
 */
class Ccbrand implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('value'=>'42x20','label'=> __('42x20 px'));
        $options[] = array('value'=>'68x30','label'=> __('68x30 px'));
        $options[] = array('value'=>'','label'=> __('Show text only'));

        return $options;
    }
}