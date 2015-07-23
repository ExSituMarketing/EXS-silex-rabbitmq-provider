<?php

namespace EXS\RabbitmqProvider\Services;

use EXS\RabbitmqProvider\Services\AmqpService;

/**
 * Description of ConsumerService
 * 
 * Consume messages in rabbitmq message queues.
 * Created      07/31/2015
 * @author      Lee
 * @copyright   Copyright 2015 ExSitu Marketing.
 * @access public
 */
class ConsumerService
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
     * Consume all messages in the queue.
     * @return boolean
     */
    public function consumeAll($isDeclared = true)
    {
        $connection = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connection);
        $exchange = $this->amqpService->getAmqpExchange($channel, $isDeclared);
        $queue = $this->amqpService->getAmqpQue($channel, $isDeclared);
        $messages = $this->amqpService->amqpReceiveAll($queue);
        $this->amqpService->amqpDisconnect($connection);
        
        return $messages;
    }
    
    /**
     * Consume the first limit number of messages in the queue. 
     * @param int $limit
     * @return boolean
     */
    public function consumeWithLimit($limit = 1000, $isDeclared = true)
    {
        $connection = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connection);
        $exchange = $this->amqpService->getAmqpExchange($channel, $isDeclared);
        $queue = $this->amqpService->getAmqpQue($channel, $isDeclared);
        $result = $this->amqpService->amqpReceive($queue, $limit);
        $this->amqpService->amqpDisconnect($connection);
        
        return $result;
    }
}
