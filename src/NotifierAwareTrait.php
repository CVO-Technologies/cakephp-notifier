<?php

namespace CvoTechnologies\Notifier;

use Cake\Core\App;

trait NotifierAwareTrait
{
    /**
     * Returns a mailer instance.
     *
     * @param string $name Mailer's name.
     * @param \Cake\Mailer\Email|null $notification Notification instance.
     * @return \Cake\Mailer\Mailer
     * @throws \Cake\Mailer\Exception\MissingMailerException if undefined mailer class.
     */
    public function getNotifier($name, Notification $notification = null)
    {
        if ($notification === null) {
            $notification = new Notification();
        }

        $className = App::className($name, 'Notifier', 'Notifier');

        if (empty($className)) {
            throw new MissingMailerException(compact('name'));
        }

        return (new $className($notification));
    }
}
