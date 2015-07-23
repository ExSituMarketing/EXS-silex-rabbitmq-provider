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
     * @param type $terminateSignal
     */
    public function __construct(AmqpService $amqpService, $terminateSignal = '')
    {
        $this->amqpService = $amqpService;
        $this->terminateSignal = $terminateSignal;
    }
    
    /**
     * Consume all messages in the queue.
     * @return boolean
     */
    public function consumeAll()
    {
        $connection = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connection);
        $exchange = $this->amqpService->getAmqpExchange($channel);
        $queue = $this->amqpService->getAmqpQue($channel);
        $messages = $this->amqpService->amqpReceiveAll($queue);
        $this->amqpService->amqpDisconnect($connection);
        
        return $messages;
    }
    
    /**
     * Consume the first limit number of messages in the queue. 
     * @param int $limit
     * @return boolean
     */
    public function consumeWithLimit($limit = 1000)
    {
        $connection = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connection);
        $exchange = $this->amqpService->getAmqpExchange($channel);
        $queue = $this->amqpService->getAmqpQue($channel);
        $result = $this->amqpService->amqpReceive($queue, $limit);
        $this->amqpService->amqpDisconnect($connection);
        
        return $result;
    }
}
