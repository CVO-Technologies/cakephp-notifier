<?php

namespace TestApp\Notifier;

use CvoTechnologies\Notifier\Notifier;

/**
 * Test Suite Test App Notifier class.
 *
 */
class TestNotifier extends Notifier
{

    public function getNotificationForAssertion()
    {
        return $this->_notification;
    }

    public function reset()
    {
        $this->template = $this->viewBuilder()->template();

        return parent::reset();
    }
}
