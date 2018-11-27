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

namespace Gears\Event\Symfony;

use Gears\Event\Event;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

final class EventEnvelope extends SymfonyEvent
{
    /**
     * @var Event
     */
    private $wrappedEvent;

    /**
     * EventWrapper constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->wrappedEvent = $event;
    }

    /**
     * Get wrapped event.
     *
     * @return Event
     */
    public function getWrappedEvent(): Event
    {
        return $this->wrappedEvent;
    }
}
