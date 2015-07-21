<?php

namespace EXS\RabbitmqProvider\Services;

use EXS\RabbitmqProvider\Services\AmqpService;

/**
 * 
 *
 * Created 20-May-2015
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class ConsumerService
{
    /**
     * Amqp service
     * @var \EXS\RabbitmqProvider\Service\AmqpService
     */
    protected $amqpService;

    public function __construct(AmqpService $amqpService, $terminateSignal = '')
    {            
        $this->amqpService = $amqpService;
        $this->terminateSignal = $terminateSignal;
    }    
    
    public function consumeAll() 
    {
        $connetion = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connetion);
        $exchange = $this->amqpService->getAmqpExchange($channel);
        $queue = $this->amqpService->getAmqpQue($channel);        
        $messages = $this->amqpService->amqpReceiveAll($queue);
        $this->amqpService->amqpDisconnect($connetion);
        
        return $messages;
    }
    
    public function endlessConsumeWithLimit($limit = 1000) 
    {
        $connetion = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connetion);
        $exchange = $this->amqpService->getAmqpExchange($channel);
        $queue = $this->amqpService->getAmqpQue($channel); 
        $messages = $this->amqpService->amqpReceive($queue, $limit); 
        $this->amqpService->amqpDisconnect($connetion);
        
        return $messages;
    }    
    
}
