<?php

namespace Vrpayecommerce\Vrpayecommerce\Helper;

class Logger
{
 	protected $logger;

	/**
     * initialize logging class
     */
    public function __construct() {
        if (class_exists('\Zend\Log\Writer\Stream', false))
        {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/vrpayecommerce-' . date('d-m-Y') . '.log');
            $this->logger = new \Zend\Log\Logger();
        }
        else
        {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/vrpayecommerce-' . date('d-m-Y') . '.log');
            $this->logger = new \Zend_Log();
        }
        $this->logger->addWriter($writer);
    }

    /**
     * add logging message
     *
     * @param string $logMessage
     * @param mix $value
     */
    public function addLogVrpayecommerce($logMessage, $value = '')
    {
        if(!empty($value)){
            $this->logger->info( $logMessage. json_encode($value));
        }else{
            $this->logger->info( $logMessage);
        }
    }
}