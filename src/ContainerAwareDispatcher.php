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
use Psr\Container\ContainerInterface;

class ContainerAwareDispatcher extends Dispatcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ContainerAwareEventDispatcher constructor.
     *
     * @param ContainerInterface            $container
     * @param array<string, mixed>          $listenersMap
     * @param array<AsyncEventQueueHandler> $asyncEventHandlers
     */
    public function __construct(
        ContainerInterface $container,
        array $listenersMap = [],
        array $asyncEventHandlers = []
    ) {
        parent::__construct($listenersMap, $asyncEventHandlers);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function assertListenerType($listener): void
    {
        if (!\is_string($listener)) {
            throw new \InvalidArgumentException(\sprintf(
                'Event handler must be a container entry, "%s" given',
                \is_object($listener) ? \get_class($listener) : \gettype($listener)
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatchEvent(iterable $listeners, Event $event): void
    {
        /** @var string $listener */
        foreach ($listeners as $listener) {
            $handler = $this->container->get($listener);

            if (!$handler instanceof EventHandler) {
                throw new \RuntimeException(\sprintf(
                    'Event handler should implement "%s", "%s" given',
                    EventHandler::class,
                    \is_object($handler) ? \get_class($handler) : \gettype($handler)
                ));
            }

            $handler->handle($event);
        }
    }
}
