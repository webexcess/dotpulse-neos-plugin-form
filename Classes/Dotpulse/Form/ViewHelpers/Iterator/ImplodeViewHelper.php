<?php
namespace Dotpulse\Form\ViewHelpers\Iterator;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;

class ImplodeViewHelper extends ExplodeViewHelper {

    /**
     * @var string
     */
    protected $method = 'implode';

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('content', 'string', 'Array or array-convertible object to be imploded by glue');
        $this->registerArgument('glue', 'string', 'String used as glue in the string to be exploded.', false, ',');
    }

}
