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
use Gears\Event\Symfony\Dispatcher\ContainerAwareDispatcher;
use Gears\Event\Symfony\Dispatcher\EventEnvelope;
use Gears\Event\Symfony\Dispatcher\Tests\Stub\EventStub;
use Gears\Event\Symfony\Dispatcher\Tests\Stub\EventSubscriberInterfaceStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Symfony event dispatcher wrapper test.
 */
class ContainerAwareDispatcherTest extends TestCase
{
    public function testInvalidListener(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Event handler must be a container entry, "stdClass" given');

        /** @var ContainerInterface $containerMock */
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        new ContainerAwareDispatcher($containerMock, [\stdClass::class => new \stdClass()]);
    }

    public function testEmptyEvent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dispatched event cannot be empty');

        /** @var ContainerInterface $containerMock */
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcher = new ContainerAwareDispatcher($containerMock);

        $eventDispatcher->dispatch(null);
    }

    public function testInvalidEvent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Dispatched event must implement ".+\\\EventEnvelope", ".+" given$/');

        /** @var ContainerInterface $containerMock */
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcher = new ContainerAwareDispatcher($containerMock);

        $eventDispatcher->dispatch(new Event());
    }

    public function testInvalidHandler(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Event handler should implement "Gears\Event\EventHandler", "string" given');

        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock->expects(static::once())
            ->method('get')
            ->with('eventHandler')
            ->will(static::returnValue('thisIsNoHandler'));
        /** @var ContainerInterface $containerMock */
        $eventDispatcher = new ContainerAwareDispatcher($containerMock, [EventStub::class => 'eventHandler']);

        $eventDispatcher->dispatch(new EventEnvelope(EventStub::instance()));
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

        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock->expects(static::once())
            ->method('get')
            ->with('eventHandler')
            ->will(static::returnValue($eventHandler));
        /** @var ContainerInterface $containerMock */
        $subscriber = new EventSubscriberInterfaceStub([EventStub::class => 'eventHandler']);

        $eventDispatcher = new ContainerAwareDispatcher($containerMock);
        $eventDispatcher->addSubscriber($subscriber);

        $eventDispatcher->dispatch(new EventEnvelope($event));
    }
}
