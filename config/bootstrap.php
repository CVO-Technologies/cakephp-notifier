<?php

use Cake\Core\Configure;
use CvoTechnologies\Notifier\Notification;

Notification::configTransport(Configure::consume('NotificationTransport'));
Notification::config(Configure::consume('Notification'));
