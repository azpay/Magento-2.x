<?php
namespace Azpay\Gateway\Model\System\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class SubscriptionPeriodoOptions extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options=[
                                ['label' => __('Dia'), 'value' => 'day'],
                                ['label' => __('Semana'), 'value' => 'week'],
                                ['label' => __('Mês'), 'value' => 'month'],
                                ['label' => __('Ano'), 'value' => 'year']
                            ];
        }
        return $this->_options;
    }
}