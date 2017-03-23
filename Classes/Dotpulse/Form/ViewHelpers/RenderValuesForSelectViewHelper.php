<?php
namespace Dotpulse\Form\ViewHelpers;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3\Flow\I18n\Translator;

class RenderValuesForSelectViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @param string $identifier
     * @param array $values
     * @param string $package
     * @return string the rendered string
     * @throws ViewHelperException
     */
    public function render($identifier, $values = array(), $package) {
        $return = array();

        foreach ($values as $value => $text) {
            $return[] = array(
                'value' => $value,
                'text' => $this->translator->translateById($identifier.'.'.$value, array(), null, null, 'Forms', $package)
            );
        }

        return json_encode($return);
    }
}
