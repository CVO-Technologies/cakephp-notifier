<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CvoTechnologies\Notifier\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Notifier\NotifierAwareTrait;

/**
 * Testing stub.
 */
class Stub
{

    use NotifierAwareTrait;
}

/**
 * NotifierAwareTrait test case
 */
class NotifierAwareTraitTest extends TestCase
{

    /**
     * Test getNotifier
     *
     * @return void
     */
    public function testGetNotifier()
    {
        $originalAppNamespace = Configure::read('App.namespace');
        Configure::write('App.namespace', 'TestApp');
        $stub = new Stub();
        $this->assertInstanceOf('TestApp\Notifier\TestNotifier', $stub->getNotifier('Test'));
        Configure::write('App.namespace', $originalAppNamespace);
    }

    /**
     * Test exception thrown by getNotifier.
     *
     * @expectedException CvoTechnologies\Notifier\Exception\MissingNotifierException
     * @expectedExceptionMessage Notifier class "Test" could not be found.
     */
    public function testGetNotifierThrowsException()
    {
        $stub = new Stub();
        $stub->getNotifier('Test');
    }
}
