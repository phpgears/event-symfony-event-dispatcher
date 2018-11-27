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
use Gears\Event\EventBus as EventBusInterface;

final class EventBus implements EventBusInterface
{
    /**
     * Wrapped event dispatcher.
     *
     * @var ContainerAwareEventDispatcher
     */
    private $wrappedDispatcher;

    /**
     * EventBus constructor.
     *
     * @param ContainerAwareEventDispatcher $wrappedDispatcher
     */
    public function __construct(ContainerAwareEventDispatcher $wrappedDispatcher)
    {
        $this->wrappedDispatcher = $wrappedDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(Event $event): void
    {
        $this->wrappedDispatcher->dispatch(\get_class($event), new EventEnvelope($event));
    }
}
