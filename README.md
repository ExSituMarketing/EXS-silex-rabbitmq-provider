EXS-silex-rabbitmq-provider
==========================

Super simple provider for reading (consume) / writing (post) to rabbitmq from silex 2.x application.


## Installing the EXS-silex-rabbitmq-provider in a Silex project
The installation process is actually very simple.  Set up a Silex project with Composer.

Once the new project is set up, open the composer.json file and add the exs/silex-rabbitmq-provider as a dependency:
``` js
//composer.json
//...
"require": {
        //other bundles
        "exs/silex-rabbitmq-provider": "@dev"
```
Or you could just add it via the command line:
```
$ composer.phar require exs/silex-rabbitmq-provider ~1.0@dev
```

Save the file and have composer update the project via the command line:
``` shell
php composer.phar update
```
Composer will now update all dependencies and you should see our bundle in the list:
``` shell
  - Installing exs/silex-rabbitmq-provider (dev-master 463eb20)
    Cloning 463eb2081e7205e7556f6f65224c6ba9631e070a
```

Update the app.php to include the EXS-silex-rabbitmq-provider provider:
``` php
//app.php
//...
$app->register(new \EXS\RabbitmqProvider\Providers\Services\RabbitmqProvider());
```
Update your rabbitmq connection and environment in your config.php:
```php
//...
// Rabbit MQ
$app['rabbit.connections'] = array(
    'default' => array(
        'host' => 'localhost',
        'port' => 5672,
        'user' => 'REPLACE_YOUR_USER_NAME',
        'password' => 'REPLACE_YOUR_PASSWORD',
        'vhost' => 'REPLACE_YOUR_VHOST_NAME'
    )
);

// rabbitmq provider environment
$app['exs.rabbitmq.env'] = array(        
    'exchange' => 'REPLACE_EXCHANGE_NAME',
    'type' => 'REPLACE_EXCHANGE_TYPE',
    'queue' => 'REPLACE_QUEUE_NAME',
    'key' => 'REPLACE_ROUTING_KEY_NAME' 
 );
//...
```



## USAGE


Publish messages to the queue
```php
use EXS\RabbitmqProvider\Services\PostmanService;

$postman = new PostmanService();
$postman->publish($YOUR_MESSAGE_HERE);
));

Consume messages from the queue
```php
use EXS\RabbitmqProvider\Services\ConsumerService;

$consumer = new ConsumerService();
// Get all messages from the queue
$messages = $this->consumerService->consumeAll();

// Get 1000 messages from the queue
$messages = $this->consumerService->consumeWithLimit(1000);

));

And now you can publish and consume messages with rabbitmq.

#### Notice ####
This provider does not support multiple exchanges or queues. 


#### Contributing ####
Anyone and everyone is welcome to contribute.

If you have any questions or suggestions please [let us know][1].

[1]: http://www.ex-situ.com/