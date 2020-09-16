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

use Gears\Event\Event;
use Gears\Event\EventHandler;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

class Dispatcher extends SymfonyEventDispatcher implements EventDispatcher
{
    /**
     * @var AsyncEventQueueHandler[]
     */
    private $asyncEventHandlers = [];

    /**
     * ContainerAwareEventDispatcher constructor.
     *
     * @param array<string, mixed>     $listenersMap
     * @param AsyncEventQueueHandler[] $asyncEventHandlers
     */
    public function __construct(array $listenersMap = [], array $asyncEventHandlers = [])
    {
        parent::__construct();

        foreach ($asyncEventHandlers as $asyncEventHandler) {
            $this->addAsyncEventHandler($asyncEventHandler);
        }

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
     * Add asynchronous event handler.
     *
     * @param AsyncEventQueueHandler $eventHandler
     */
    public function addAsyncEventHandler(AsyncEventQueueHandler $eventHandler): void
    {
        $this->asyncEventHandlers[] = $eventHandler;
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
        $this->assertListenerType($listener);

        parent::addListener($eventName, $listener, (int) $priority);
    }

    /**
     * Assert listener type.
     *
     * @param mixed $listener
     */
    protected function assertListenerType($listener): void
    {
        if (!$listener instanceof EventHandler) {
            throw new \InvalidArgumentException(\sprintf(
                'Event handler must be an instance of "%s", "%s" given',
                EventHandler::class,
                \is_object($listener) ? \get_class($listener) : \gettype($listener)
            ));
        }
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

        $event = $eventEnvelope->getWrappedEvent();

        $this->handleAsync($event);

        parent::dispatch($eventEnvelope, $event->getEventType());

        return $eventEnvelope;
    }

    /**
     * Handle async.
     *
     * @param Event $event
     */
    protected function handleAsync(Event $event): void
    {
        foreach ($this->asyncEventHandlers as $eventHandler) {
            $eventHandler->handle($event);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param iterable<mixed> $listeners
     * @param string          $eventName
     * @param object          $event
     */
    protected function callListeners(iterable $listeners, string $eventName, $event): void
    {
        if ($event instanceof EventEnvelope) {
            $this->dispatchEvent($listeners, $event->getWrappedEvent());

            return;
        }

        parent::callListeners($listeners, $eventName, $event);
    }

    /**
     * Dispatch event to registered listeners.
     *
     * @param iterable<EventHandler> $listeners
     * @param Event                  $event
     */
    protected function dispatchEvent(iterable $listeners, Event $event): void
    {
        foreach ($listeners as $handler) {
            $handler->handle($event);
        }
    }
}
