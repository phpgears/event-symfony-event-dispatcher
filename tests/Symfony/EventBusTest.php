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

use Gears\Event\Symfony\ContainerAwareDispatcher;
use Gears\Event\Symfony\EventBus;
use Gears\Event\Symfony\Tests\Stub\EventStub;
use PHPUnit\Framework\TestCase;

/**
 * Symfony event bus test.
 */
class EventBusTest extends TestCase
{
    public function testHandling(): void
    {
        $eventDispatcherMock = $this->getMockBuilder(ContainerAwareDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDispatcherMock->expects($this->once())
            ->method('dispatch');
        /* @var ContainerAwareDispatcher $eventDispatcherMock */

        (new EventBus($eventDispatcherMock))->dispatch(EventStub::instance());
    }
}
