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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

interface EventDispatcher extends EventDispatcherInterface
{
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param mixed $eventEnvelope
     *
     * @return SymfonyEvent
     */
    public function dispatch($eventEnvelope): SymfonyEvent;
}
