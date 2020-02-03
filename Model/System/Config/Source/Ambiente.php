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
class Ambiente implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = array();
        
        $options[] = array('value'=>'teste','label'=> __('Teste'));
        $options[] = array('value'=>'producao','label'=> __('Produção'));       
        
        return $options;
    }
}