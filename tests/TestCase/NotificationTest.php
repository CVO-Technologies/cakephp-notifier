<?php

namespace CvoTechnologies\Notifier\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Notifier\Notification;
use CvoTechnologies\Notifier\Notifier\Transport\DebugTransport;

class NotificationTest extends TestCase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Notification = new Notification();
        $this->transports = [
            'debug' => [
                'className' => 'Debug'
            ]
        ];
        Notification::configTransport($this->transports);
    }
    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Notification::drop('test');
        Notification::dropTransport('debug');
        Notification::dropTransport('test_smtp');
    }

    public function testTitle()
    {
        $notification = new Notification();

        $this->assertEquals($notification, $notification->title('Test title'));
        $this->assertEquals('Test title', $notification->title());
    }

    public function testTo()
    {
        $notification = new Notification();
        $this->assertEquals($notification, $notification->to('destination@example.com', 'email'));
        $this->assertEquals('destination@example.com', $notification->to(null, 'email'));
        $this->assertEquals([
            'email' => 'destination@example.com'
        ], $notification->to());

        $this->assertEquals($notification, $notification->to([
            'irc' => [
                'nick1',
                'nick2'
            ]
        ]));
        $this->assertEquals([
            'nick1',
            'nick2'
        ], $notification->to(null, 'irc'));
        $this->assertEquals([
            'irc' => [
                'nick1',
                'nick2'
            ],
            'email' => 'destination@example.com'
        ], $notification->to());
    }


    /**
     * testTemplate method
     *
     * @return void
     */
    public function testTemplate()
    {
        $this->Notification->template('template', 'layout');
        $expected = ['template' => 'template', 'layout' => 'layout'];
        $this->assertSame($expected, $this->Notification->template());
        $this->Notification->template('new_template');
        $expected = ['template' => 'new_template', 'layout' => 'layout'];
        $this->assertSame($expected, $this->Notification->template());
        $this->Notification->template('template', null);
        $expected = ['template' => 'template', 'layout' => false];
        $this->assertSame($expected, $this->Notification->template());
        $this->Notification->template(null, null);
        $expected = ['template' => '', 'layout' => false];
        $this->assertSame($expected, $this->Notification->template());
    }
    /**
     * testTheme method
     *
     * @return void
     */
    public function testTheme()
    {
        $this->assertNull($this->Notification->theme());
        $this->Notification->theme('default');
        $expected = 'default';
        $this->assertSame($expected, $this->Notification->theme());
    }
    /**
     * testViewVars method
     *
     * @return void
     */
    public function testViewVars()
    {
        $this->assertSame([], $this->Notification->viewVars());
        $this->Notification->viewVars(['value' => 12345]);
        $this->assertSame(['value' => 12345], $this->Notification->viewVars());
        $this->Notification->viewVars(['name' => 'CakePHP']);
        $this->assertEquals(['value' => 12345, 'name' => 'CakePHP'], $this->Notification->viewVars());
        $this->Notification->viewVars(['value' => 4567]);
        $this->assertSame(['value' => 4567, 'name' => 'CakePHP'], $this->Notification->viewVars());
    }

    /**
     * testViewRender method
     *
     * @return void
     */
    public function testViewRender()
    {
        $result = $this->Notification->viewRender();
        $this->assertEquals('Cake\View\View', $result);
        $result = $this->Notification->viewRender('Cake\View\ThemeView');
        $this->assertInstanceOf('CvoTechnologies\Notifier\Notification', $result);
        $result = $this->Notification->viewRender();
        $this->assertEquals('Cake\View\ThemeView', $result);
    }

    /**
     * Test that using misconfigured transports fails.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Transport config "debug" is invalid, the required `className` option is missing
     */
    public function testTransportMissingClassName()
    {
        Notification::dropTransport('debug');
        Notification::configTransport('debug', []);
        $this->Notification->transports(['debug']);
    }
    /**
     * Test configuring a transport.
     *
     * @return void
     */
    public function testConfigTransport()
    {
        Notification::dropTransport('debug');
        $settings = [
            'className' => 'Debug',
            'log' => true
        ];
        $result = Notification::configTransport('debug', $settings);
        $this->assertNull($result, 'No return.');
        $result = Notification::configTransport('debug');
        $this->assertEquals($settings, $result);
    }
    /**
     * Test configuring multiple transports.
     */
    public function testConfigTransportMultiple()
    {
        Notification::dropTransport('debug');
        $settings = [
            'debug' => [
                'className' => 'Debug',
                'log' => true
            ],
            'test_smtp' => [
                'className' => 'Smtp',
                'username' => 'mark',
                'password' => 'password',
                'host' => 'example.com'
            ]
        ];
        Notification::configTransport($settings);
        $this->assertEquals($settings['debug'], Notification::configTransport('debug'));
        $this->assertEquals($settings['test_smtp'], Notification::configTransport('test_smtp'));
    }
    /**
     * Test that exceptions are raised when duplicate transports are configured.
     *
     * @expectedException \BadMethodCallException
     */
    public function testConfigTransportErrorOnDuplicate()
    {
        Notification::dropTransport('debug');
        $settings = [
            'className' => 'Debug',
            'log' => true
        ];
        Notification::configTransport('debug', $settings);
        Notification::configTransport('debug', $settings);
    }
    /**
     * Test configTransport with an instance.
     *
     * @return void
     */
    public function testConfigTransportInstance()
    {
        Notification::dropTransport('debug');
        $instance = new DebugTransport();
        Notification::configTransport('debug', $instance);
        $this->assertEquals(['className' => $instance], Notification::configTransport('debug'));
    }
    /**
     * Test enumerating all transport configurations
     *
     * @return void
     */
    public function testConfiguredTransport()
    {
        $result = Notification::configuredTransport();
        $this->assertInternalType('array', $result, 'Should have config keys');
        $this->assertEquals(
            array_keys($this->transports),
            $result,
            'Loaded transports should be present in enumeration.'
        );
    }
    /**
     * Test dropping a transport configuration
     *
     * @return void
     */
    public function testDropTransport()
    {
        $result = Notification::configTransport('debug');
        $this->assertInternalType('array', $result, 'Should have config data');
        Notification::dropTransport('debug');
        $this->assertNull(Notification::configTransport('debug'), 'Should not exist.');
    }

    /**
     * test profile method
     *
     * @return void
     */
    public function testProfile()
    {
        $config = ['test' => 'ok', 'test2' => true];
        $this->Notification->profile($config);
        $this->assertSame($this->Notification->profile(), $config);
        $config = ['test' => 'test@example.com'];
        $this->Notification->profile($config);
        $expected = ['test' => 'test@example.com', 'test2' => true];
        $this->assertSame($expected, $this->Notification->profile());
    }
    /**
     * test that default profile is used by constructor if available.
     *
     * @return void
     */
    public function testDefaultProfile()
    {
        $config = ['test' => 'ok', 'test2' => true];
        Configure::write('Notification.default', $config);
        Notification::config(Configure::consume('Notification'));
        $Notification = new Notification();
        $this->assertSame($Notification->profile(), $config);
        Configure::delete('Notification');
        Notification::drop('default');
    }
    /**
     * Test that using an invalid profile fails.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown notification configuration "derp".
     */
    public function testProfileInvalid()
    {
        $email = new Notification();
        $email->profile('derp');
    }
}
