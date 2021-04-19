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
class Ccacquirer implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('value'=>'adiq','label'=> __('ADIQ - Webservice'));
        $options[] = array('value'=>'firstdata','label'=> __('BIN'));
        $options[] = array('value'=>'cielo_loja','label'=> __('CIELO - BUY PAGE LOJA'));
        $options[] = array('value'=>'cielo','label'=> __('CIELO - BUY PAGE CIELO'));
        $options[] = array('value'=>'cielo_api','label'=> __('CIELO - SOLUÇÃO API 3.0'));
        $options[] = array('value'=>'erede','label'=> __('e-Rede Webservice'));
        $options[] = array('value'=>'getnet','label'=> __('GETNET'));
        $options[] = array('value'=>'getnet_v1','label'=> __('GETNET V1'));
        $options[] = array('value'=>'granito','label'=> __('Granito Pagamentos'));
        $options[] = array('value'=>'global_payments','label'=> __('GLOBAL PAYMENTS'));
        $options[] = array('value'=>'komerci_webservice','label'=> __('REDE - KOMERCI WEBSERVICE'));
        $options[] = array('value'=>'komerci_integrado','label'=> __('REDE - KOMERCI INTEGRADO'));
        $options[] = array('value'=>'privatelabel','label'=> __('PrivateLabel'));
        $options[] = array('value'=>'stone','label'=> __('STONE PAGAMENTOS'));
        $options[] = array('value'=>'worldpay','label'=> __('World Pay'));
        $options[] = array('value'=>'gateway','label'=> __('Azpay'));
        $options[] = array('value'=>'zoop','label'=> __('Zoop'));

        return $options;
    }
}