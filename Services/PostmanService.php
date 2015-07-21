<?php

namespace EXS\RabbitmqProvider\Services;

use EXS\RabbitmqProvider\Service\AmqpService;

/**
 * 
 *
 * Created 20-May-2015
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class PostmanService
{
    /**
     * Amqp service
     * @var \EXS\RabbitmqProvider\Service\AmqpService
     */
    protected $amqpService;
    
    public function __construct(AmqpService $amqpService)
    {            
        $this->amqpService = $amqpService;
    }    
    
    public function publish($message) 
    {
        if (empty(trim($message))) {
            return false;
        }
        
        $connetion = $this->amqpService->amqpConnect();
        $channel = $this->amqpService->getAmqpChannel($connetion);
        $exchange = $this->amqpService->getAmqpExchange($channel);
        $queue = $this->amqpService->getAmqpQue($channel);        
        $res = $this->amqpService->amqpSend($exchange, $message);
        $this->amqpService->amqpDisconnect($connetion);
        
        return true;
    }
    
}
