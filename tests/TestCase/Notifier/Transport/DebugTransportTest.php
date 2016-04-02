<?php

namespace CvoTechnologies\Notifier\Test\TestCase\Notifier\Transport;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Notifier\Notification;
use CvoTechnologies\Notifier\Notifier\Transport\DebugTransport;

class DebugTransportTest extends TestCase
{
    public function testSend()
    {
        $notification = new Notification();

        $transport = new DebugTransport();

        $this->assertSame($notification, $transport->send($notification));
    }

    public function testCanRender()
    {
        $this->assertFalse((new DebugTransport())->canRenderTemplates());
    }
}
