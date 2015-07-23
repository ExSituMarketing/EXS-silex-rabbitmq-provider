<?php

namespace EXS\RabbitmqProvider\Services;

use EXS\RabbitmqProvider\Services\AmqpService;

/**
 * Description of PostmanService
 * 
 * Publish messages to rabbitmq message queues.
 * Created      07/31/2015
 * @author      Lee
 * @copyright   Copyright 2015 ExSitu Marketing.
 * @access public
 */
class PostmanService
{

    /**
     * Amqp service
     * @var \EXS\RabbitmqProvider\Service\AmqpService
     */
    protected $amqpService;

    /**
     * Initiate the service
     * @param AmqpService $amqpService
     */
    public function __construct(AmqpService $amqpService)
    {
        $this->amqpService = $amqpService;
    }

    /**
     * Publish the message to queue 
     * @param string $message
     * @return boolean
     */
    public function publish($message = '', $isDeclared = true)
    {
        $valid = $this->getCleanMessage($message);
        if ($valid !== false) {
            $this->processPublish($message, $isDeclared);
        }
    }

    /**
     * Process message publishing
     * @param string $message
     */
    public function processPublish($message = '', $isDeclared = true)
    {
        $connetion = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connetion);
        $exchange = $this->amqpService->getAmqpExchange($channel, $isDeclared);
        $queue = $this->amqpService->getAmqpQue($channel, $isDeclared);
        $res = $this->amqpService->amqpSend($exchange, $message);
        $this->amqpService->amqpDisconnect($connetion);
    }

    /**
     * Remove white spaces in the message
     * @param string $message
     * @return mixed
     */
    public function getCleanMessage($message = '')
    {
        $trimmed = trim($message);
        if (empty($trimmed)) {
            return false;
        }
        return $trimmed;
    }
}
