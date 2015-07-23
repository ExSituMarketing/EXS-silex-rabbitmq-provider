<?php

namespace EXS\RabbitmqProvider\Providers\Services;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use EXS\RabbitmqProvider\Services\AmqpService;
use EXS\RabbitmqProvider\Services\ConsumerService;
use EXS\RabbitmqProvider\Services\PostmanService;

/**
 * Description of RabbitmqProvider
 * 
 * Register the service
 * Created      07/31/2015
 * @author      Lee
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class RabbitmqProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['exs.serv.amqp'] = (function ($app) {
            return new AmqpService($app['rabbit.connections']['default'], $app['exs.rabbitmq.env']);
        });
        $app['exs.serv.postman'] = (function ($app) {
            return new PostmanService($app['exs.serv.amqp']);
        });
        $app['exs.serv.consumer'] = (function ($app) {
            return new ConsumerService($app['exs.serv.amqp']);
        });
    }
}
