<?php

namespace EXS\RabbitmqProvider\Services;

use \AMQPConnection;
use \AMQPExchange;
use \AMQPQueue;

/**
 * AMQP class wrapper service
 * 
 * Service to bridge services to AMQP php library.
 * Created      07/31/2015
 * @author      Lee
 * @copyright   Copyright 2015 ExSitu Marketing.
 * @access public
 */
class AmqpService
{
        
    /**
     * MQ server hostname
     * @access private
     * @var string
     */
    private $_host;

    /**
     * MQ username
     * @access private
     * @var string
     */
    private $_mqUser;

    /**
     * MQ user's password
     * @access private
     * @var string
     */
    private $_mqPass;

    /**
     * Name of vhost to use
     * @access private
     * @var string
     */
    private $_vhost;
    
    /**
     * Port to use
     * @access private
     * @var string
     */
    private $_port;
    
    /**
     * exchange option
     * @var type array
     */
    protected $exchangeOptions = array(
        'name' => 'default',
        'type' => 'direct',
    );

    /**
     * Queue option
     * @var type array
     */
    protected $queueOptions = array(
        'name' => 'default.queue',
        'routing_key' => 'default.key'
    );
    
    /**
     * MQ constructor
     * @return void
     * @access public
     */
    public function __construct($connection = array(), $env = array())
    {
        $this->_host = $connection['host'];
        $this->_mqUser = $connection['user'];
        $this->_mqPass = $connection['password'];
        $this->_vhost = $connection['vhost'];
        $this->_port = $connection['port'];
        $this->exchangeOptions['name'] = $env['exchange'];
        $this->exchangeOptions['type'] = $env['type'];
        $this->queueOptions['name'] = $env['queue'];
        $this->queueOptions['routing_key'] = $env['key'];
    }
        
    /**
     * Establishes connection to MQ
     * @access public
     * @return object $amqpConnection
     */
    public function amqpConnect()
    {
        $amqpConnection = new \AMQPConnection();
        $amqpConnection->setHost($this->_host);
        $amqpConnection->setLogin($this->_mqUser);
        $amqpConnection->setPassword($this->_mqPass);
        $amqpConnection->setVhost($this->_vhost);
        $amqpConnection->setPort($this->_port);
        $amqpConnection->connect();
   
        if (!$amqpConnection->isConnected()) {
            throw new \Exception("Failed to connect to rabbitmq server");
        }
        return $amqpConnection;
    }
    
    /**
     * get channel
     * @param AMQPConnection $amqpConnection
     * @return \AMQPChannel
     */
    public function getAmqpChannel(\AMQPConnection $amqpConnection)
    {
        $channel = new \AMQPChannel($amqpConnection);
        return $channel;
    }
    
    /**
     * Get exchange server
     * @param AMQPChannel $channel
     * @return AMQPExchange
     */
    public function getAmqpExchange(\AMQPChannel $channel)
    {
        $exchange = new AMQPExchange($channel);
        $exchange->setName($this->exchangeOptions['name']);
        $exchange->setType($this->exchangeOptions['type']);
        $exchange->setFlags(false);
        $exchange->declareExchange();
        
        return $exchange;
    }
    
    /**
     * Get queue
     * @param AMQPChannel $channel
     * @return AMQPQueue
     */
    public function getAmqpQue(\AMQPChannel $channel)
    {
        $queue = new \AMQPQueue($channel);
        $queue->setName($this->queueOptions['name']);
        $queue->declareQueue();
        
        return $queue;
    }

    /**
     * Set exchange option property
     * @param type $key
     * @param type $value
     * @return string
     */
    public function setExchangeOption($key, $value)
    {
        if ($key && $value) {
            $this->exchangeOptions[$key] = $value;
        }
        return $this->exchangeOptions[$key];
    }
    
    /**
     * Set queue option property
     * @param type $key
     * @param type $value
     * @return type
     */
    public function setQueueOption($key, $value)
    {
        if ($key && $value) {
            $this->queueOptions[$key] = $value;
        }
        return $this->queueOptions[$key];
    }
    
    /**
     * Establishes disconnection to MQ
     * @param AMQPConnection $amqpConnection
     * @return boolean
     */
    public function amqpDisconnect(\AMQPConnection $amqpConnection)
    {
        if (!$amqpConnection->disconnect()) {
            throw new \Exception("Failed to disconnect from rabbitmq server");
        }
        return true;
    }
    
    /**
     * Send message to MQ
     * @param object $exchange
     * @param string $message
     * @return boolean
     * @access public
     */
    public function amqpSend(\AMQPExchange $exchange, $message)
    {
        $messageResponse = $exchange->publish($message, $this->queueOptions['routing_key']);
        if (!$messageResponse) {
            throw new \Exception("Could not publish the rabbitmq message");
        } else {
            return true;
        }
    }
    
    /**
     * read message from MQ
     * @param object $queue
     * @return array $queArray
     * @access public
     */
    public function amqpReceive(\AMQPQueue $queue, $limit = 1000)
    {
        $count = 0;
        $queArray = array();
        $queue->bind($this->exchangeOptions['name'], $this->queueOptions['routing_key']);
        while ($count <= $limit) {
            if ($message = $queue->get(AMQP_AUTOACK)) {
                $queArray[] =  $message->getBody();
                $count++;
            } else {
                sleep(1); // interval before resume consumming if the que is empty.
            }
        }

        return $queArray;
    }
    
    /**
     * read all message from MQ
     * @param object $queue
     * @return array $queArray
     * @access public
     */
    public function amqpReceiveAll(\AMQPQueue $queue)
    {
        $queArray = array();
        $queue->bind($this->exchangeOptions['name'], $this->queueOptions['routing_key']);
        while ($message = $queue->get(AMQP_AUTOACK)) {
            $queArray[] =  $message->getBody();
        }

        return $queArray;
    }
   
    /**
     * read message from MQ and remove them right away
     * @param object $queue
     * @return array $queArray
     * @access public
     */
    public function amqpReceiveAndRemove(\AMQPQueue $queue)
    {
        $queArray = array();
        $queue->bind($this->exchangeOptions['name'], $this->queueOptions['routing_key']);
    
        while ($message = $queue->get(AMQP_AUTOACK)) {
            $queArray[] = $message->getBody();
            $deliveryTag = $message->getDeliveryTag();
            $queue->ack($deliveryTag);
        }
    
        return $queArray;
    }
    
    /**
     * Check delivered message from MQ
     * @param object $queue
     * @return array $queArray
     * @access public
     */
    public function checkDelivery(\AMQPQueue $queue)
    {
        $queArray = array();
        $queue->bind($this->exchangeOptions['name'], $this->queueOptions['routing_key']);
        
        while ($message = $queue->get()) {
            if ($message->isRedelivery()) {
                $queArray[] = $message->getDeliveryTag()."=".$message->getBody();
            }
        }
        
        return $queArray;
    }

    /**
     * Remove message from MQ
     * @param object $queue
     * @param array $inputArray
     * @return void
     * @access public
     */
    public function removeMessage(\AMQPQueue $queue, $inputArray = array())
    {
        $queue->bind($this->exchangeOptions['name'], $this->queueOptions['routing_key']);
        foreach ($inputArray as $deliveryTag) {
            if (isset($deliveryTag)) {
                $queue->ack($deliveryTag);
            }
        }
    }
}








// end of script
