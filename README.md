# CvoTechnologies/Notifier plugin for CakePHP

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/travis/CVO-Technologies/cakephp-notifier/master.svg?style=flat-square)](https://travis-ci.org/CVO-Technologies/cakephp-notifier)
[![Coverage Status](https://img.shields.io/codecov/c/github/cvo-technologies/cakephp-notifier.svg?style=flat-square)](https://codecov.io/github/cvo-technologies/cakephp-notifier)
[![Total Downloads](https://img.shields.io/packagist/dt/cvo-technologies/cakephp-notifier.svg?style=flat-square)](https://packagist.org/packages/cvo-technologies/cakephp-notifier)
[![Latest Stable Version](https://img.shields.io/packagist/v/cvo-technologies/cakephp-notifier.svg?style=flat-square&label=stable)](https://packagist.org/packages/cvo-technologies/cakephp-notifier)


## Usage

### Configuring notifications transports

Add the following section to your application config in `app.php`.

```php
    'NotificationTransport' => [
        'email' => [
            'className' => 'CvoTechnologies/Notifier.Email',
            'profile' => 'default',
        ],
        'irc' => [
            'className' => 'Irc',
            'channel' => '#cvo-technlogies'
        ],
        'twitter' => [
            'className' => 'CvoTechnologies/Twitter.Twitter',
        ],
        'example' => [
            'className' => 'Example',
            'someOption' => true
        ]
    ],
```

### Creating a notifier

```php
namespace App\Notifier;

use CvoTechnologies\Notifier\Notifier;

class UserNotifier extends Notifier
{
    public function welcome($user)
    {
        $this
            ->to([
                'irc' => $user->irc_nickname,
                'twitter' => $user->twitter_nickname
            ])
            ->subject(sprintf('Welcome %s', $user->name))
            ->template('welcome_message') // By default template with same name as method name is used.
            ->viewVars([
                'user' => $user
            ])
            ->transports([
                'irc',
                'twitter'
            ]);
    }
}
```

#### Creating notification template

Create a template file in `Template/Notification/transport-type`. This will be used as template for the notification.

For example: `Template/Notification/irc/welcome.ctp`
```php
Welcome <?= $user->name; ?> to our website!
```

#### Using it

Using the notifier is very easy. Here's an example on how to use it in a controller:

```php
namespace App\Controller;

use CvoTechnologies\Notifier\NotifierAwareTrait;

class UsersController extends AppController
{
    use NotifierAwareTrait;

    public function register()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data())
            if ($this->Users->save($user)) {
                $this->getNotifier('User')->send('welcome', [$user]);
            }
        }
        $this->set('user', $user);
    }
}
```

### Creating a transport

A transport is used to talk to a particular service.

It can accept configuration options that are passed from the `NotificationTransport` section in your application config.

```php
<?php

namespace App\Notifier\Transport;

use CvoTechnologies\Notifier\AbstractTransport;

class ExampleTransport extends AbstractTransport
{
    const TYPE = 'example';

    /**
     * Send notification.
     *
     * @param \CvoTechnologies\Notifier\Notification $notification Notification instance.
     * @return array
     */
    public function send(Notification $notification)
    {
        // Send notificaiton
        $result = NotificationSendingService::send($notification->message(static::TYPE));

        return (array)$result;
    }
}
```
