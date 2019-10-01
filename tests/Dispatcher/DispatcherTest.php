<?php

/*
 * event-symfony-event-dispatcher (https://github.com/phpgears/event-symfony-event-dispatcher).
 * Event bus with Symfony Event Dispatcher.
 *
 * @license MIT
 * @link https://github.com/phpgears/event-symfony-event-dispatcher
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Gears\Event\Symfony\Dispatcher\Tests;

use Gears\Event\EventHandler;
use Gears\Event\Symfony\Dispatcher\Dispatcher;
use Gears\Event\Symfony\Dispatcher\EventEnvelope;
use Gears\Event\Symfony\Dispatcher\Tests\Stub\EventStub;
use Gears\Event\Symfony\Dispatcher\Tests\Stub\EventSubscriberInterfaceStub;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Symfony event dispatcher wrapper test.
 */
class DispatcherTest extends TestCase
{
    public function testInvalidListener(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp(
            '/^Event handler must be an instance of .+\\\EventHandler, stdClass given$/'
        );

        new Dispatcher([\stdClass::class => new \stdClass()]);
    }

    public function testEmptyEvent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dispatched event cannot be empty');

        $eventDispatcher = new Dispatcher();

        $eventDispatcher->dispatch(null);
    }

    public function testInvalidEvent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Dispatched event must implement .+\\\EventEnvelope, .+ given$/');

        $eventDispatcher = new Dispatcher();

        $eventDispatcher->dispatch(new Event());
    }

    public function testEventDispatch(): void
    {
        $event = EventStub::instance();

        $eventHandler = $this->getMockBuilder(EventHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventHandler->expects(static::once())
            ->method('handle')
            ->with($event);

        $subscriber = new EventSubscriberInterfaceStub([
            EventStub::class => [
                [$eventHandler],
            ],
        ]);

        $eventDispatcher = new Dispatcher();
        $eventDispatcher->addSubscriber($subscriber);

        $eventDispatcher->dispatch(new EventEnvelope($event));
    }
}
