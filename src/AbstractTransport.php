<?php

namespace CvoTechnologies\Notifier;

use Cake\Core\InstanceConfigTrait;

abstract class AbstractTransport
{
    use InstanceConfigTrait;

    /**
     * Default config for this class
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Send notification
     *
     * @param \CvoTechnologies\Notifier\Notification $notification Notification instance.
     * @return array
     */
    abstract public function send(Notification $notification);

    /**
     * Constructor
     *
     * @param array $config Configuration options.
     */
    public function __construct($config = [])
    {
        $this->config($config);
    }

    public function canRenderTemplates()
    {
        return false;
    }
}
