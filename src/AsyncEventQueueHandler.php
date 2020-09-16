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

namespace Gears\Event\Symfony\Dispatcher;

use Gears\Event\Async\Discriminator\EventDiscriminator;
use Gears\Event\Async\EventQueue;
use Gears\Event\Async\QueuedEvent;
use Gears\Event\Event;

final class AsyncEventQueueHandler
{
    /**
     * Event queue.
     *
     * @var EventQueue
     */
    private $eventQueue;

    /**
     * Event discriminator.
     *
     * @var EventDiscriminator
     */
    private $discriminator;

    /**
     * AsyncEventHandler constructor.
     *
     * @param EventQueue         $eventQueue
     * @param EventDiscriminator $discriminator
     */
    public function __construct(EventQueue $eventQueue, EventDiscriminator $discriminator)
    {
        $this->eventQueue = $eventQueue;
        $this->discriminator = $discriminator;
    }

    /**
     * Handle event.
     *
     * @param Event $event
     */
    public function handle(Event $event): void
    {
        if (!$event instanceof QueuedEvent && $this->discriminator->shouldEnqueue($event)) {
            $this->eventQueue->send(new QueuedEvent($event));

            return;
        }
    }
}
