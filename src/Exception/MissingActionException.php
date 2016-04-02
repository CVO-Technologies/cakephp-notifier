<?php

namespace CvoTechnologies\Notifier\Exception;

use Cake\Core\Exception\Exception;

/**
 * Missing Action exception - used when a notifier action cannot be found.
 */
class MissingActionException extends Exception
{

    /**
     * {@inheritDoc}
     */
    protected $_messageTemplate = 'Notifier %s::%s() could not be found, or is not accessible.';

    /**
     * {@inheritDoc}
     */
    public function __construct($message, $code = 404)
    {
        parent::__construct($message, $code);
    }
}
