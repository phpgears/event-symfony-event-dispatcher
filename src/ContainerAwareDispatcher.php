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
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContainerAwareDispatcher extends SymfonyEventDispatcher implements EventDispatcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ContainerAwareEventDispatcher constructor.
     *
     * @param ContainerInterface   $container
     * @param array<string, mixed> $listenersMap
     */
    public function __construct(ContainerInterface $container, array $listenersMap = [])
    {
        $this->container = $container;

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
     * Adds an event subscriber.
     *
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            if (!\is_array($params)) {
                $params = [$params];
            }

            foreach ($params as $listener) {
                if (\is_string($listener)) {
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
     * @param string          $eventName
     * @param callable|string $listener
     * @param int             $priority
     */
    public function addListener($eventName, $listener, $priority = 0): void
    {
        if (!\is_string($listener)) {
            throw new \InvalidArgumentException(\sprintf(
                'Event handler must be a container entry, %s given',
                \is_object($listener) ? \get_class($listener) : \gettype($listener)
            ));
        }

        parent::addListener($eventName, $listener, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, SymfonyEvent $event = null): SymfonyEvent
    {
        if ($event === null) {
            throw new \InvalidArgumentException('Dispatched event cannot be empty');
        }

        if (!$event instanceof EventEnvelope) {
            throw new \InvalidArgumentException(\sprintf(
                'Dispatched event must implement %s, %s given',
                EventEnvelope::class,
                \get_class($event)
            ));
        }

        $this->dispatchEvent($this->getListeners($eventName), $event);

        return $event;
    }

    /**
     * Dispatch event to registered listeners.
     *
     * @param string[]      $listeners
     * @param EventEnvelope $event
     */
    private function dispatchEvent(array $listeners, EventEnvelope $event): void
    {
        $dispatchEvent = $event->getWrappedEvent();

        foreach ($listeners as $listener) {
            /* @var EventHandler $handler */
            $handler = $this->container->get($listener);

            if (!$handler instanceof EventHandler) {
                throw new \RuntimeException(\sprintf(
                    'Event handler should implement %s, %s given',
                    EventHandler::class,
                    \is_object($handler) ? \get_class($handler) : \gettype($handler)
                ));
            }

            $handler->handle($dispatchEvent);
        }
    }
}
