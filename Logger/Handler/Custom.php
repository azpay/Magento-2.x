<?php
namespace Azpay\Gateway\Logger\Handler;
use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Class Custom
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Logger\Handler
 */
class Custom extends Base
{
    /**
    * @var string
    */
    protected $fileName = '/var/log/gateway.log';
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

}