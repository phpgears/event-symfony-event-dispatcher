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

namespace Gears\Event\Symfony\Dispatcher\Tests\Stub;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Symfony event subscriber stub class.
 */
class EventSubscriberInterfaceStub implements EventSubscriberInterface
{
    /**
     * @var array<string, mixed>
     */
    protected static $listeners;

    /**
     * EventSubscriberInterfaceStub constructor.
     *
     * @param array<string, mixed> $listeners
     */
    public function __construct(array $listeners)
    {
        self::$listeners = $listeners;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return self::$listeners;
    }
}
