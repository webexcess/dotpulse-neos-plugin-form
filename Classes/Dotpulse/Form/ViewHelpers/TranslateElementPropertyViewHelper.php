<?php
namespace Dotpulse\Form\ViewHelpers;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Translator;
use TYPO3\Flow\Resource\Exception as ResourceException;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3\Form\Core\Model\FormElementInterface;

use Dotpulse\Form\Service\FormHelperService;

/**
 * ViewHelper to translate the property of a given form element based on its rendering options
 *
 * = Examples =
 *
 * <code>
 * {element -> form:translateElementProperty(property: 'placeholder')}
 * </code>
 * <output>
 *  the translated placeholder, or the actual "placeholder" property if no translation was found
 * </output>
 *
 */
class TranslateElementPropertyViewHelper extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var FormHelperService
     */
    protected $formHelperService;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @param string $property
     * @param FormElementInterface $element
     * @param boolean $outputId
     * @return string the rendered form head
     */
    public function render($property, FormElementInterface $element = null, $outputId = false)
    {
        if ($element === null) {
            $element = $this->renderChildren();
        }

        return $this->formHelperService->getTranslatedLabel($property, $element);
    }
}
