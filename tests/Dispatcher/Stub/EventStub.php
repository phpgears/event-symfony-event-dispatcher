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

use Gears\Event\AbstractEmptyEvent;

/**
 * Event stub class.
 */
class EventStub extends AbstractEmptyEvent
{
    /**
     * Instantiate event.
     *
     * @return self
     */
    public static function instance(): self
    {
        return self::occurred();
    }
}
