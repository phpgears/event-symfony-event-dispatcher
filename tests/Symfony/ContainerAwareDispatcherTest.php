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
use Gears\Event\Symfony\ContainerAwareDispatcher;
use Gears\Event\Symfony\EventEnvelope;
use Gears\Event\Symfony\Tests\Stub\EventStub;
use Gears\Event\Symfony\Tests\Stub\EventSubscriberInterfaceStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Symfony event dispatcher wrapper test.
 */
class ContainerAwareDispatcherTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Event handler must be a container entry, stdClass given
     */
    public function testInvalidListener(): void
    {
        /** @var ContainerInterface $containerMock */
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        new ContainerAwareDispatcher($containerMock, ['eventName' => new \stdClass()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Dispatched event cannot be empty
     */
    public function testEmptyEvent(): void
    {
        /** @var ContainerInterface $containerMock */
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcher = new ContainerAwareDispatcher($containerMock);

        $eventDispatcher->dispatch('eventName');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Dispatched event must implement .+\\EventEnvelope, .+ given$/
     */
    public function testInvalidEvent(): void
    {
        /** @var ContainerInterface $containerMock */
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcher = new ContainerAwareDispatcher($containerMock);

        $eventDispatcher->dispatch('eventName', new Event());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Event handler should implement Gears\Event\EventHandler, string given
     */
    public function testInvalidHandler(): void
    {
        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock->expects($this->once())
            ->method('get')
            ->with('eventHandler')
            ->will($this->returnValue('thisIsNoHandler'));
        /** @var ContainerInterface $containerMock */
        $eventDispatcher = new ContainerAwareDispatcher($containerMock, ['eventName' => 'eventHandler']);

        $eventDispatcher->dispatch('eventName', new EventEnvelope(EventStub::instance()));
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

        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock->expects($this->once())
            ->method('get')
            ->with('eventHandler')
            ->will($this->returnValue($eventHandler));
        /** @var ContainerInterface $containerMock */
        $subscriber = new EventSubscriberInterfaceStub([
            'eventName' => 'eventHandler',
            'otherEvent' => ['eventHandler'],
            'anotherEvent' => [
                ['eventHandler'],
            ],
        ]);

        $eventDispatcher = new ContainerAwareDispatcher($containerMock);
        $eventDispatcher->addSubscriber($subscriber);

        $eventDispatcher->dispatch('eventName', new EventEnvelope($event));
    }
}
