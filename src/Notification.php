<?php

namespace CvoTechnologies\Notifier;

use BadMethodCallException;
use Cake\Core\App;
use Cake\Core\StaticConfigTrait;
use Cake\View\ViewVarsTrait;
use InvalidArgumentException;
use LogicException;

class Notification
{

    use StaticConfigTrait;
    use ViewVarsTrait;

    protected $_to = [];

    /**
     * The title of the notification
     *
     * @var string
     */
    protected $_title = '';

    /**
     * Final message to send
     *
     * @var array
     */
    protected $_message = [];

    /**
     * The transport instance to use for sending notifications.
     *
     * @var \CvoTechnologies\Notifier\AbstractTransport[]
     */
    protected $_transports = null;

    /**
     * Configuration profiles for transports.
     *
     * @var array
     */
    protected static $_transportConfig = [];

    /**
     * A copy of the configuration profile for this
     * instance. This copy can be modified with Notification::profile().
     *
     * @var array
     */
    protected $_profile = [];

    public function __construct($config = null)
    {
        $this->viewBuilder()
            ->className('Cake\View\View')
            ->template('')
            ->layout('default');

        if ($config === null) {
            $config = static::config('default');
        }
        if ($config) {
            $this->profile($config);
        }
    }

    /**
     * Get/Set Title.
     *
     * @param string|null $title Title string.
     * @return string|$this
     */
    public function title($title = null)
    {
        if ($title === null) {
            return $this->_title;
        }
        $this->_title = (string)$title;
        return $this;
    }

    /**
     * Template and layout
     *
     * @param bool|string $template Template name or null to not use
     * @param bool|string $layout Layout name or null to not use
     * @return array|$this
     */
    public function template($template = false, $layout = false)
    {
        if ($template === false) {
            return [
                'template' => $this->viewBuilder()->template(),
                'layout' => $this->viewBuilder()->layout()
            ];
        }
        $this->viewBuilder()->template($template ?: '');
        if ($layout !== false) {
            $this->viewBuilder()->layout($layout ?: false);
        }
        return $this;
    }

    /**
     * View class for render
     *
     * @param string|null $viewClass View class name.
     * @return string|$this
     */
    public function viewRender($viewClass = null)
    {
        if ($viewClass === null) {
            return $this->viewBuilder()->className();
        }
        $this->viewBuilder()->className($viewClass);
        return $this;
    }

    /**
     * Variables to be set on render
     *
     * @param array|null $viewVars Variables to set for view.
     * @return array|$this
     */
    public function viewVars($viewVars = null)
    {
        if ($viewVars === null) {
            return $this->viewVars;
        }
        $this->set((array)$viewVars);
        return $this;
    }

    /**
     * Theme to use when rendering
     *
     * @param string|null $theme Theme name.
     * @return string|$this
     */
    public function theme($theme = null)
    {
        if ($theme === null) {
            return $this->viewBuilder()->theme();
        }
        $this->viewBuilder()->theme($theme);
        return $this;
    }

    /**
     * Helpers to be used in render
     *
     * @param array|null $helpers Helpers list.
     * @return array|$this
     */
    public function helpers($helpers = null)
    {
        if ($helpers === null) {
            return $this->viewBuilder()->helpers();
        }
        $this->viewBuilder()->helpers((array)$helpers, false);
        return $this;
    }

    /**
     * Get/set the transport.
     *
     * When setting the transport you can either use the name
     * of a configured transport or supply a constructed transport.
     *
     * @param string|\Cake\Mailer\AbstractTransport|null $name Either the name of a configured
     *   transport, or a transport instance.
     * @return \Cake\Mailer\AbstractTransport|$this
     * @throws \LogicException When the chosen transport lacks a send method.
     * @throws \InvalidArgumentException When $name is neither a string nor an object.
     */
    public function transports($transports = null)
    {
        if ($transports === null) {
            return $this->_transports;
        }

        $transportInstances = [];
        foreach ($transports as $name) {
            if (is_string($name)) {
                $transport = $this->_constructTransport($name);
            } elseif (is_object($name)) {
                $transport = $name;
            } else {
                throw new InvalidArgumentException(
                    sprintf('The value passed for the "$name" argument must be either a string, or an object, %s given.', gettype($name))
                );
            }
            if (!method_exists($transport, 'send')) {
                throw new LogicException(sprintf('The "%s" do not have send method.', get_class($transport)));
            }

            $transportInstances[] = $transport;
        }


        $this->_transports = $transportInstances;
        return $this;
    }

    public function to($to = null, $type = null)
    {
        if ($to === null) {
            if ($type) {
                return (isset($this->_to[$type])) ? $this->_to[$type] : null;
            }

            return $this->_to;
        }

        if (is_array($to)) {
            foreach ($to as $type => $target) {
                $this->to($target, $type);
            }

            return $this;
        }

        $this->_to[$type] = $to;

        return $this;
    }

    /**
     * Build a transport instance from configuration data.
     *
     * @param string $name The transport configuration name to build.
     * @return \CvoTechnologies\Notifier\AbstractTransport
     * @throws \InvalidArgumentException When transport configuration is missing or invalid.
     */
    protected function _constructTransport($name)
    {
        if (!isset(static::$_transportConfig[$name])) {
            throw new InvalidArgumentException(sprintf('Transport config "%s" is missing.', $name));
        }

        if (!isset(static::$_transportConfig[$name]['className'])) {
            throw new InvalidArgumentException(
                sprintf('ATransport config "%s" is invalid, the required `className` option is missing', $name)
            );
        }

        $config = static::$_transportConfig[$name];

        if (is_object($config['className'])) {
            return $config['className'];
        }

        $className = App::className($config['className'], 'Notifier/Transport', 'Transport');
        if (!$className) {
            throw new InvalidArgumentException(sprintf('Transport class "%s" not found.', $name));
        } elseif (!method_exists($className, 'send')) {
            throw new InvalidArgumentException(sprintf('The "%s" does not have a send() method.', $className));
        }

        unset($config['className']);
        return new $className($config);
    }

    /**
     * Get generated message (used by transport classes)
     *
     * @param string|null $type Use MESSAGE_* constants or null to return the full message as array
     * @return string|array String if have type, array if type is null
     */
    public function message($type)
    {
        return $this->_message[$type];
    }

    /**
     * Add or read transport configuration.
     *
     * Use this method to define transports to use in delivery profiles.
     * Once defined you cannot edit the configurations, and must use
     * Email::dropTransport() to flush the configuration first.
     *
     * When using an array of configuration data a new transport
     * will be constructed for each message sent. When using a Closure, the
     * closure will be evaluated for each message.
     *
     * The `className` is used to define the class to use for a transport.
     * It can either be a short name, or a fully qualified classname
     *
     * @param string|array $key The configuration name to read/write. Or
     *   an array of multiple transports to set.
     * @param array|\Cake\Mailer\AbstractTransport|null $config Either an array of configuration
     *   data, or a transport instance.
     * @return \CvoTechnologies\Notifier\AbstractTransport[]|null Either null when setting or an array of data when reading.
     * @throws \BadMethodCallException When modifying an existing configuration.
     */
    public static function configTransport($key, $config = null)
    {
        if ($config === null && is_string($key)) {
            return isset(static::$_transportConfig[$key]) ? static::$_transportConfig[$key] : null;
        }
        if ($config === null && is_array($key)) {
            foreach ($key as $name => $settings) {
                static::configTransport($name, $settings);
            }
            return null;
        }
        if (isset(static::$_transportConfig[$key])) {
            throw new BadMethodCallException(sprintf('Cannot modify an existing config "%s"', $key));
        }

        if (is_object($config)) {
            $config = ['className' => $config];
        }

        static::$_transportConfig[$key] = $config;
    }

    /**
     * Get/Set the configuration profile to use for this instance.
     *
     * @param null|string|array $config String with configuration name, or
     *    an array with config or null to return current config.
     * @return string|array|$this
     */
    public function profile($config = null)
    {
        if ($config === null) {
            return $this->_profile;
        }
        if (!is_array($config)) {
            $config = (string)$config;
        }
        $this->_applyConfig($config);
        return $this;
    }

    /**
     * Send an email using the specified content, template and layout
     *
     * @param string|array|null $content String with message or array with messages
     * @return array
     * @throws \BadMethodCallException
     */
    public function send($content = null)
    {
        $transports = $this->transports();
        if (!$transports) {
            $msg = 'Cannot send email, transports were not defined. Did you call transports() or define ' .
                ' transports in the set profile?';
            throw new BadMethodCallException($msg);
        }

        foreach ($transports as $transport) {
            if (!$transport->canRenderTemplates()) {
                $this->_message[$transport::TYPE] = $this->_renderTemplates($content, $transport::TYPE);
            }

            $transport->send($this);
        }
    }

    /**
     * Build and set all the view properties needed to render the templated emails.
     * If there is no template set, the $content will be returned in a hash
     * of the text content types for the email.
     *
     * @param string $content The content passed in from send() in most cases.
     * @return array The rendered content with html and text keys.
     */
    protected function _renderTemplates($content, $type)
    {
        $template = $this->viewBuilder()->template();
        if (empty($template)) {
            return $content;
        }

        $View = $this->createView();

        list($templatePlugin) = pluginSplit($View->template());
        list($layoutPlugin) = pluginSplit($View->layout());
        if ($templatePlugin) {
            $View->plugin = $templatePlugin;
        } elseif ($layoutPlugin) {
            $View->plugin = $layoutPlugin;
        }

        if ($View->get('content') === null) {
            $View->set('content', $content);
        }

        $View->hasRendered = false;
        $View->templatePath('Notification' . DIRECTORY_SEPARATOR . $type);
        $View->layoutPath('Notification' . DIRECTORY_SEPARATOR . $type);

        return $View->render();
    }

    /**
     * Apply the config to an instance
     *
     * @param string|array $config Configuration options.
     * @return void
     * @throws \InvalidArgumentException When using a configuration that doesn't exist.
     */
    protected function _applyConfig($config)
    {
        if (is_string($config)) {
            $name = $config;
            $config = static::config($name);
            if (empty($config)) {
                throw new InvalidArgumentException(sprintf('Unknown notification configuration "%s".', $name));
            }
            unset($name);
        }

        $this->_profile = array_merge($this->_profile, $config);

        $simpleMethods = [
            'transports', 'to'
        ];
        foreach ($simpleMethods as $method) {
            if (isset($config[$method])) {
                $this->$method($config[$method]);
            }
        }

        $viewBuilderMethods = [
            'template', 'layout', 'theme'
        ];
        foreach ($viewBuilderMethods as $method) {
            if (array_key_exists($method, $config)) {
                $this->viewBuilder()->$method($config[$method]);
            }
        }

        if (array_key_exists('helpers', $config)) {
            $this->viewBuilder()->helpers($config['helpers'], false);
        }
        if (array_key_exists('viewRender', $config)) {
            $this->viewBuilder()->className($config['viewRender']);
        }
        if (array_key_exists('viewVars', $config)) {
            $this->set($config['viewVars']);
        }
    }
}
