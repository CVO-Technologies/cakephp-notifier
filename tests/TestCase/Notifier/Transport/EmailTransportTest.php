<?php

namespace CvoTechnologies\Notifier\Test\TestCase\Notifier\Transport;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Notifier\Notification;
use CvoTechnologies\Notifier\Notifier\Transport\EmailTransport;

class EmailTransportTest extends TestCase
{
    public function testSend()
    {
        Email::configTransport('default', [
            'className' => 'Debug'
        ]);
        Email::config('default', [
            'from' => 'email@example.com',
            'transport' => 'default'
        ]);
        Configure::write('App.encoding', 'UTF-8');

        $notification = new Notification();
        $notification->to('destination@example.com', 'email');

        $emailTransport = new EmailTransport();

        $this->assertInternalType('array', $emailTransport->send($notification));
    }

    public function testCanRender()
    {
        $emailTransport = new EmailTransport();

        $this->assertTrue($emailTransport->canRenderTemplates());
    }
}
