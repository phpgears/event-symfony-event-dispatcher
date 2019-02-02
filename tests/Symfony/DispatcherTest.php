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

namespace Gears\Event\Symfony\Tests;

use Gears\Event\EventHandler;
use Gears\Event\Symfony\Dispatcher;
use Gears\Event\Symfony\EventEnvelope;
use Gears\Event\Symfony\Tests\Stub\EventStub;
use Gears\Event\Symfony\Tests\Stub\EventSubscriberInterfaceStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;

/**
 * Symfony event dispatcher wrapper test.
 */
class DispatcherTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Event handler must be an instance of .+\\EventHandler, stdClass given$/
     */
    public function testInvalidListener(): void
    {
        new Dispatcher(['eventName' => new \stdClass()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Dispatched event cannot be empty
     */
    public function testEmptyEvent(): void
    {
        $eventDispatcher = new Dispatcher();

        $eventDispatcher->dispatch('eventName');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Dispatched event must implement .+\\EventEnvelope, .+ given$/
     */
    public function testInvalidEvent(): void
    {
        $eventDispatcher = new Dispatcher();

        $eventDispatcher->dispatch('eventName', new Event());
    }

    public function testEventDispatch(): void
    {
        $event = EventStub::instance();

        $eventHandler = $this->getMockBuilder(EventHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventHandler->expects($this->once())
            ->method('handle')
            ->with($event);

        $subscriber = new EventSubscriberInterfaceStub([
            'eventName' => [
                [$eventHandler],
            ],
            'anotherEvent' => $eventHandler,
            'otherEvent' => [$eventHandler],
        ]);

        $eventDispatcher = new Dispatcher();
        $eventDispatcher->addSubscriber($subscriber);

        $eventDispatcher->dispatch('eventName', new EventEnvelope($event));
    }
}
