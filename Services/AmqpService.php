<?php

namespace EXS\RabbitmqProvider\Services;

use \AMQPConnection;
use \AMQPExchange;
use \AMQPQueue;

/**
 * AMQP class wrapper service
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
    public function __construct($connections = array())
    {
        $this->_host = $connections['host'];
        $this->_mqUser = $connections['user'];
        $this->_mqPass = $connections['password'];
        $this->_vhost = $connections['vhost'];
        $this->_port = $connections['port'];
        $this->exchangeOptions['name'] = $connections['exchange'];
        $this->exchangeOptions['type'] = $connections['type'];
        $this->queueOptions['name'] = $connections['queue'];
        $this->queueOptions['routing_key'] = $connections['key'];
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
   
    	if(!$amqpConnection->isConnected())
    	{
    		return false;
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
        if($key && $value) {
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
        if($key && $value) {
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
    		return false;
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
        if(!$messageResponse) {
            return false;
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
                sleep(1);
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
    	while($message = $queue->get(AMQP_AUTOACK)) {            
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
    
        while($message = $queue->get(AMQP_AUTOACK))
        {
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
        
        while($message = $queue->get())
        {
            if($message->isRedelivery()) 
            {
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
        foreach($inputArray as $deliveryTag)
        {
                if(isset($deliveryTag))
                {
                        $queue->ack($deliveryTag);
                }
        }
    }        
}








// end of script