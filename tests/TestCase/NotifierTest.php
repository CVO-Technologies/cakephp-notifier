<?php

namespace CvoTechnologies\Notifier\Test\TestCase;

use Cake\TestSuite\TestCase;
use TestApp\Notifier\TestNotifier;

class NotifierTest extends TestCase
{
    public function getMockForNotification($methods = [], $args = [])
    {
        return $this->getMock('CvoTechnologies\Notifier\Notification', (array)$methods, (array)$args);
    }

    public function testConstructor()
    {
        $notifier = new TestNotifier();
        $this->assertInstanceOf('CvoTechnologies\Notifier\Notification', $notifier->getNotificationForAssertion());
    }

    public function testReset()
    {
        $notifier = new TestNotifier();
        $notification = $notifier->getNotificationForAssertion();

        $notifier->set(['foo' => 'bar']);
        $this->assertNotEquals($notification->viewVars(), $notifier->reset()->getNotificationForAssertion()->viewVars());
    }

    public function testGetName()
    {
        $result = (new TestNotifier())->getName();
        $expected = 'Test';
        $this->assertEquals($expected, $result);
    }

    public function testLayout()
    {
        $result = (new TestNotifier())->layout('foo');
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $result);
        $this->assertEquals('foo', $result->viewBuilder()->layout());
    }

    public function testProxies()
    {
        $notification = $this->getMockForNotification('setHeaders');
        $notification->expects($this->once())
            ->method('setHeaders')
            ->with(['X-Something' => 'nice']);
        $result = (new TestNotifier($notification))->setHeaders(['X-Something' => 'nice']);
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $result);

        $notification = $this->getMockForNotification('addHeaders');
        $notification->expects($this->once())
            ->method('addHeaders')
            ->with(['X-Something' => 'very nice', 'X-Other' => 'cool']);
        $result = (new TestNotifier($notification))->addHeaders(['X-Something' => 'very nice', 'X-Other' => 'cool']);
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $result);

        $notification = $this->getMockForNotification('attachments');
        $notification->expects($this->once())
            ->method('attachments')
            ->with([
                ['file' => CAKE . 'basics.php', 'mimetype' => 'text/plain']
            ]);
        $result = (new TestNotifier($notification))->attachments([
            ['file' => CAKE . 'basics.php', 'mimetype' => 'text/plain']
        ]);
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $result);
    }

    public function testSet()
    {
        $notification = $this->getMockForNotification('viewVars');
        $notification->expects($this->once())
            ->method('viewVars')
            ->with(['key' => 'value']);
        $result = (new TestNotifier($notification))->set('key', 'value');
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $result);

        $notification = $this->getMockForNotification('viewVars');
        $notification->expects($this->once())
            ->method('viewVars')
            ->with(['key' => 'value']);
        $result = (new TestNotifier($notification))->set(['key' => 'value']);
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $result);
    }

    public function testSend()
    {
        $notification = $this->getMockForNotification('send');
        $notification->expects($this->any())
            ->method('send')
            ->will($this->returnValue([]));

        $notifier = $this->getMock('TestApp\Notifier\TestNotifier', ['test'], [$notification]);
        $notifier->expects($this->once())
            ->method('test')
            ->with('foo', 'bar');

        $notifier->template('foobar');
        $notifier->send('test', ['foo', 'bar']);
        $this->assertEquals($notifier->template, 'foobar');
    }

    public function testSendWithUnsetTemplateDefaultsToActionName()
    {
        $notification = $this->getMockForNotification('send');
        $notification->expects($this->any())
            ->method('send')
            ->will($this->returnValue([]));

        $notifier = $this->getMock('TestApp\Notifier\TestNotifier', ['test'], [$notification]);
        $notifier->expects($this->once())
            ->method('test')
            ->with('foo', 'bar');

        $notifier->send('test', ['foo', 'bar']);
        $this->assertEquals($notifier->template, 'test');
    }

    /**
     * test that initial email instance config is restored after email is sent.
     *
     * @return [type]
     */
    public function testDefaultProfileRestoration()
    {
        $notification = $this->getMockForNotification('send', [['template' => 'cakephp']]);
        $notification->expects($this->any())
            ->method('send')
            ->will($this->returnValue([]));

        $notifier = $this->getMock('TestApp\Notifier\TestNotifier', ['test'], [$notification]);
        $notifier->expects($this->once())
            ->method('test')
            ->with('foo', 'bar');

        $notifier->template('test');
        $notifier->send('test', ['foo', 'bar']);
        $this->assertEquals($notifier->template, 'test');
        $this->assertEquals('test', $notifier->viewBuilder()->template());
    }

    /**
     * @expectedException CvoTechnologies\Notifier\Exception\MissingActionException
     * @expectedExceptionMessage Notifier TestNotifier::test() could not be found, or is not accessible.
     */
    public function testMissingActionThrowsException()
    {
        (new TestNotifier())->send('test');
    }

    public function testImplementedEvents()
    {
        $this->assertEquals([], (new TestNotifier())->implementedEvents());
    }
}
