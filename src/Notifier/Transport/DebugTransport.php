<?php

namespace CvoTechnologies\Notifier\Notifier\Transport;

use CvoTechnologies\Notifier\AbstractTransport;
use CvoTechnologies\Notifier\Notification;

class DebugTransport extends AbstractTransport
{
    const TYPE = 'debug';

    /**
     * Send notification
     *
     * @param \CvoTechnologies\Notifier\Notification $notification Notification instance.
     * @return array
     */
    public function send(Notification $notification)
    {
        return $notification;
    }
}
