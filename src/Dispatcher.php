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

use Gears\Event\EventHandler;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

class Dispatcher extends SymfonyEventDispatcher implements EventDispatcher
{
    /**
     * ContainerAwareEventDispatcher constructor.
     *
     * @param array<string, mixed> $listenersMap
     */
    public function __construct(array $listenersMap = [])
    {
        parent::__construct();

        foreach ($listenersMap as $eventName => $listeners) {
            if (!\is_array($listeners)) {
                $listeners = [$listeners];
            }

            foreach ($listeners as $listener) {
                $this->addListener($eventName, $listener);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            if (!\is_array($params)) {
                $params = [$params];
            }

            foreach ($params as $listener) {
                if (!\is_array($listener)) {
                    $this->addListener($eventName, $listener);
                } else {
                    $this->addListener($eventName, $listener[0], $listener[1] ?? 0);
                }
            }
        }
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string $eventName
     * @param mixed  $listener
     * @param mixed  $priority
     */
    public function addListener($eventName, $listener, $priority = 0): void
    {
        if (!$listener instanceof EventHandler) {
            throw new \InvalidArgumentException(\sprintf(
                'Event handler must be an instance of "%s", "%s" given',
                EventHandler::class,
                \is_object($listener) ? \get_class($listener) : \gettype($listener)
            ));
        }

        parent::addListener($eventName, $listener, (int) $priority);
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param mixed $eventEnvelope
     *
     * @return SymfonyEvent
     */
    public function dispatch($eventEnvelope): SymfonyEvent
    {
        if ($eventEnvelope === null) {
            throw new \InvalidArgumentException('Dispatched event cannot be empty');
        }

        if (!$eventEnvelope instanceof EventEnvelope) {
            throw new \InvalidArgumentException(\sprintf(
                'Dispatched event must implement "%s", "%s" given',
                EventEnvelope::class,
                \get_class($eventEnvelope)
            ));
        }

        $eventListeners = $this->getListeners($eventEnvelope->getWrappedEvent()->getEventType());
        $this->dispatchEvent($eventListeners, $eventEnvelope);

        return $eventEnvelope;
    }

    /**
     * Dispatch event to registered listeners.
     *
     * @param EventHandler[] $listeners
     * @param EventEnvelope  $event
     */
    private function dispatchEvent(array $listeners, EventEnvelope $event): void
    {
        $dispatchEvent = $event->getWrappedEvent();

        foreach ($listeners as $handler) {
            $handler->handle($dispatchEvent);
        }
    }
}
