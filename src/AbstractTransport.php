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

    /**
     * Whether this transport can render templates on its own. This means we won't call the render method on the
     * notification.
     *
     * @return bool Whether this transport renders templates.
     */
    public function canRenderTemplates()
    {
        return false;
    }
}
