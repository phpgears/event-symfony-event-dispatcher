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

use Gears\Event\Async\Discriminator\EventDiscriminator;
use Gears\Event\Async\EventQueue;
use Gears\Event\Async\QueuedEvent;
use Gears\Event\Symfony\Dispatcher\AsyncEventQueueHandler;
use Gears\Event\Symfony\Dispatcher\Tests\Stub\EventStub;
use PHPUnit\Framework\TestCase;

class AsyncEventQueueHandlerTest extends TestCase
{
    public function testShouldEnqueue(): void
    {
        $eventQueue = $this->getMockBuilder(EventQueue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventQueue->expects(static::once())
            ->method('send');
        $eventDiscriminator = $this->getMockBuilder(EventDiscriminator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDiscriminator->expects(static::once())
            ->method('shouldEnqueue')
            ->willReturn(true);

        (new AsyncEventQueueHandler($eventQueue, $eventDiscriminator))
            ->handle(EventStub::instance());
    }

    public function testShouldNotEnqueue(): void
    {
        $eventQueue = $this->getMockBuilder(EventQueue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventQueue->expects(static::never())
            ->method('send');
        $eventDiscriminator = $this->getMockBuilder(EventDiscriminator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDiscriminator->expects(static::once())
            ->method('shouldEnqueue')
            ->willReturn(false);

        $mockEvent = EventStub::instance();

        (new AsyncEventQueueHandler($eventQueue, $eventDiscriminator))
            ->handle($mockEvent);
    }

    public function testShouldNotEnqueueReceivedCommand(): void
    {
        $eventQueue = $this->getMockBuilder(EventQueue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventQueue->expects(static::never())
            ->method('send');
        $eventDiscriminator = $this->getMockBuilder(EventDiscriminator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDiscriminator->expects(static::never())
            ->method('shouldEnqueue');

        $mockCommand = new QueuedEvent(EventStub::instance());

        (new AsyncEventQueueHandler($eventQueue, $eventDiscriminator))
            ->handle($mockCommand);
    }
}
