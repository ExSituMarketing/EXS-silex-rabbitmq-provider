<?php

namespace EXS\RabbitmqProvider\Providers\Services;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use EXS\RabbitmqProvider\Services\ConsumerService;
use EXS\RabbitmqProvider\Services\PostmanService;

/**
 * Register the service to log errors and define the ErrorHandler
 *
 * Created 4-May-2015
 * @author Damien Demessence
 * @copyright   Copyright 2015 ExSitu Marketing.
 */
class RabbitmqProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['exs.serv.postman'] = ( function ($app) {
            return new PostmanService();
        });
        $app['exs.serv.consumer'] = ( function ($app) {
            return new ConsumerService();
        });        
    }
}