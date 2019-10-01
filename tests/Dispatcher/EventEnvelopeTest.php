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

use Gears\Event\Symfony\Dispatcher\EventEnvelope;
use Gears\Event\Symfony\Dispatcher\Tests\Stub\EventStub;
use PHPUnit\Framework\TestCase;

/**
 * Symfony event envelope test.
 */
class EventEnvelopeTest extends TestCase
{
    public function testEnvelope(): void
    {
        $event = EventStub::instance();

        $eventEnvelope = new EventEnvelope($event);

        static::assertSame($event, $eventEnvelope->getWrappedEvent());
    }
}
