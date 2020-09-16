[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.1-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://packagist.org/packages/phpgears/event-symfony-event-dispatcher)
[![License](https://img.shields.io/github/license/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://github.com/phpgears/event-symfony-event-dispatcher/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://travis-ci.org/phpgears/event-symfony-event-dispatcher)
[![Style Check](https://styleci.io/repos/158948865/shield)](https://styleci.io/repos/158948865)
[![Code Quality](https://img.shields.io/scrutinizer/g/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpgears/event-symfony-event-dispatcher)
[![Code Coverage](https://img.shields.io/coveralls/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://coveralls.io/github/phpgears/event-symfony-event-dispatcher)

[![Total Downloads](https://img.shields.io/packagist/dt/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://packagist.org/packages/phpgears/event-symfony-event-dispatcher/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/phpgears/event-symfony-event-dispatcher.svg?style=flat-square)](https://packagist.org/packages/phpgears/event-symfony-event-dispatcher/stats)

# Event bus Event Dispatcher

Event bus implementation with Symfony's Event Dispatcher

## Installation

### Composer

```
composer require phpgears/event-symfony-event-dispatcher
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';
```

### Events Bus

```php
use Gears\Event\Symfony\Dispatcher\ContainerAwareDispatcher;
use Gears\Event\Symfony\Dispatcher\EventBus;
use Gears\Event\Symfony\Dispatcher\Dispatcher;

$eventToHandlerMap = [];

$symfonyDispatcher = new Dispatcher($eventToHandlerMap);
// OR
/** @var \Psr\Container\ContainerInterface $container */
$symfonyDispatcher = new ContainerAwareDispatcher($container, $eventToHandlerMap);

$eventBus = new EventBus($symfonyDispatcher);

/** @var \Gears\Event\Event $event */
$eventBus->dispatch($event);
```

#### Asynchronicity

To allow events to be handled asynchronously you should include `Gears\Event\Symfony\Dispatcher\AsyncEventHandler` in dispatcher's constructor

AsyncEventHandler requires an implementation of `Gears\Event\Async\EventQueue` which will be responsible for event queueing and an instance of `Gears\Event\Async\Discriminator\EventDiscriminator` used to discriminate which events should be queued

```php
use Gears\Event\Async\Discriminator\ParameterEventDiscriminator;
use Gears\Event\Async\Serializer\NativePhpEventSerializer;
use Gears\Event\Symfony\Dispatcher\AsyncEventQueueHandler;
use Gears\Event\Symfony\Dispatcher\ContainerAwareDispatcher;
use Gears\Event\Symfony\Dispatcher\EventBus;
use Gears\Event\Symfony\Dispatcher\Dispatcher;

/* @var \Gears\Event\Async\EventQueue $eventQueue */
$eventQueue = new EventQueueImplementation(new NativePhpEventSerializer());

$asyncEventHandler = new AsyncEventQueueHandler($eventQueue, new ParameterEventDiscriminator('async'));

$eventToHandlerMap = [];

$symfonyDispatcher = new Dispatcher($eventToHandlerMap, [$asyncEventHandler]);
// OR
/** @var \Psr\Container\ContainerInterface $container */
$symfonyDispatcher = new ContainerAwareDispatcher($container, $eventToHandlerMap, [$asyncEventHandler]);

$eventBus = new EventBus($symfonyDispatcher);

/** @var \Gears\Event\Event $event */
$eventBus->dispatch($event);
```

If you'd like to send different events to different message queues you can just add more instances of AsyncEventQueueHandler

To know more about how to create and configure an EventQueue head to [phpgears/event-async](https://github.com/phpgears/event-async)

##### Dequeueing

This part is highly dependent on your message queue, though event serializers can be used to deserialize queue messages

This is just an example of the process

```php
use Gears\Event\Async\Serializer\NativePhpEventSerializer;
use Gears\Event\Symfony\Dispatcher\ContainerAwareDispatcher;
use Gears\Event\Symfony\Dispatcher\EventBus;
use Gears\Event\Symfony\Dispatcher\Dispatcher;

$eventToHandlerMap = [];

$symfonyDispatcher = new Dispatcher($eventToHandlerMap);
// OR
/** @var \Psr\Container\ContainerInterface $container */
$symfonyDispatcher = new ContainerAwareDispatcher($container, $eventToHandlerMap);

$eventBus = new EventBus($symfonyDispatcher);
$serializer = new NativePhpEventSerializer();

while (true) {
    /* @var your_message_queue_manager $queue */
    $message = $queue->getMessage(); // extract messages from queue

    if ($message !== null) {
        $event = $serializer->fromSerialized($message);

        $eventBus->dispatch($event);
    }
}
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/phpgears/event-symfony-event-dispatcher/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/phpgears/event-symfony-event-dispatcher/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/phpgears/event-symfony-event-dispatcher/blob/master/LICENSE) included with the source code for a copy of the license terms.
