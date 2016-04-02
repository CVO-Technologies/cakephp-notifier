<?php

namespace CvoTechnologies\Notifier\Notifier\Transport;

use Cake\Mailer\Email;
use CvoTechnologies\Notifier\AbstractTransport;
use CvoTechnologies\Notifier\Notification;

class EmailTransport extends AbstractTransport
{
    const TYPE = 'email';

    /**
     * Default config for this class
     *
     * @var array
     */
    protected $_defaultConfig = [
        'profile' => 'default',
    ];

    /**
     * Send notification
     *
     * @param \CvoTechnologies\Notifier\Notification $notification Notification instance.
     * @return array
     */
    public function send(Notification $notification)
    {
        $email = new Email();
        $email->profile($this->config('profile'));
        $email->to($notification->to(null, static::TYPE));
        $email->subject($notification->title());

        $email->viewBuilder()->templatePath($notification->viewBuilder()->templatePath());
        $email->viewBuilder()->template($notification->viewBuilder()->template());
        $email->viewBuilder()->plugin($notification->viewBuilder()->plugin());
        $email->viewBuilder()->theme($notification->viewBuilder()->theme());
        $email->viewBuilder()->layout($notification->viewBuilder()->layout());
        $email->viewBuilder()->autoLayout($notification->viewBuilder()->autoLayout());
        $email->viewBuilder()->layoutPath($notification->viewBuilder()->layoutPath());
        $email->viewBuilder()->name($notification->viewBuilder()->name());
        $email->viewBuilder()->className($notification->viewBuilder()->className());
        $email->viewBuilder()->options($notification->viewBuilder()->options());
        $email->viewBuilder()->helpers($notification->viewBuilder()->helpers());
        $email->viewVars($notification->viewVars());

        return $email->send();
    }

    /**
     * {@inheritDoc}
     */
    public function canRenderTemplates()
    {
        return true;
    }
}
