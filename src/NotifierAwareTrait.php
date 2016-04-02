<?php

namespace CvoTechnologies\Notifier;

use Cake\Core\App;
use CvoTechnologies\Notifier\Exception\MissingNotifierException;

trait NotifierAwareTrait
{
    /**
     * Returns a notifier instance.
     *
     * @param string $name Notifier's name.
     * @param \CvoTechnologies\Notifier\Notification|null $notification Notification instance.
     * @return \CvoTechnologies\Notifier\Notification
     * @throws \CvoTechnologies\Notifier\Exception\MissingNotifierException if undefined notifier class.
     */
    public function getNotifier($name, Notification $notification = null)
    {
        if ($notification === null) {
            $notification = new Notification();
        }

        $className = App::className($name, 'Notifier', 'Notifier');

        if (empty($className)) {
            throw new MissingNotifierException(compact('name'));
        }

        return (new $className($notification));
    }
}
