<?php

namespace CvoTechnologies\Notifier\Exception;

use Cake\Core\Exception\Exception;

/**
 * Used when a notifier cannot be found.
 *
 */
class MissingNotifierException extends Exception
{

    protected $_messageTemplate = 'Notifier class "%s" could not be found.';
}
