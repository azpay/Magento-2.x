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
class Cdacquirer implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = array();
        
        $options[] = array('value'=>'firstdata','label'=> __('BIN'));
        $options[] = array('value'=>'cielo_loja','label'=> __('CIELO - BUY PAGE LOJA'));        
        $options[] = array('value'=>'cielo_api','label'=> __('CIELO - SOLUÇÃO API 3.0'));
        $options[] = array('value'=>'erede','label'=> __('e-Rede Webservice'));
        $options[] = array('value'=>'getnet','label'=> __('GETNET'));        
        $options[] = array('value'=>'global_payments','label'=> __('GLOBAL PAYMENTS'));
        
        return $options;
    }
}