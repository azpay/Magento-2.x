<?php

namespace Azpay\Gateway\Plugin\Checkout\Model;

use Magento\Checkout\Block\Checkout\LayoutProcessor as ChekcoutLayerprocessor;

class LayoutProcessor
{
    protected $dataHelper;

    public function __construct(
        \Azpay\Gateway\Helper\Data $dataHelper
    ) {

        $this->dataHelper = $dataHelper;
    }

    public function afterProcess(
        ChekcoutLayerprocessor $subject,
        array $jsLayout
    ) {
        $flag = true;
        /*if ($this->dataHelper->getBuyerGst()) {
            $flag = true;
        }*/

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['buyer_ptype'] = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'options' => [ [
                    'value' => 'fisica',
                    'label' => 'Pessoa Física',
                ],
                [
                    'value' => 'juridica',
                    'label' => 'Pessoa Jurídica',
                ]],
                'id' => 'buyer_ptype'
            ],
            'dataScope' => 'shippingAddress.buyer_ptype',
            'label' => 'Tipo de pessoa',
            'provider' => 'checkoutProvider',
            'visible' => $flag,
            'validation' => [],
            'sortOrder' => 50,
            'id' => 'buyer_ptype',
            
        ];

        /*$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['buyer_cpf'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'buyer_cpf'
            ],
            'dataScope' => 'shippingAddress.buyer_cpf',
            'label' => 'CPF',
            'provider' => 'checkoutProvider',
            'visible' => $flag,
            'validation' => [ ],
            'sortOrder' => 51,
            'id' => 'buyer_cpf'
        ];*/

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['buyer_rg'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'buyer_rg'
            ],
            'dataScope' => 'shippingAddress.buyer_rg',
            'label' => 'RG',
            'provider' => 'checkoutProvider',
            'visible' => $flag,
            'validation' => [],
            'sortOrder' => 52,
            'id' => 'buyer_rg'
        ];
        
        /*$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['buyer_cnpj'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'buyer_cnpj'
            ],
            'dataScope' => 'shippingAddress.buyer_cnpj',
            'label' => 'CNPJ',
            'provider' => 'checkoutProvider',
            'visible' => $flag,
            'validation' => [],
            'sortOrder' => 53,
            'id' => 'buyer_cpf'
        ];*/

        return $jsLayout;
    }
}