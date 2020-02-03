<?php
namespace Azpay\Gateway\Helper;

/**
 * Class Logger
 *
 * @see        Official Website
 * @author    Azpay (and others)
 * @copyright 2018-2019 Azpay
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   Azpay\Gateway\Helper
 */
class Logger extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $customLogger;

    public function __construct(
        \Psr\Log\LoggerInterface $customLogger
    ) {

        $this->customLogger = $customLogger;
    }

    /**
     * @param $obj
     */
    public function writeLog($obj) {
        if (is_string($obj)) {
            $this->customLogger->debug($obj);
        } else {
            $this->customLogger->debug(json_encode($obj));
        }
    }
}