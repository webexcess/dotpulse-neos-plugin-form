<?php
namespace Dotpulse\Form\ViewHelpers\Type;

/*
 * This file is part of the TYPO3.Fluid package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class IsArrayViewHelper extends AbstractConditionViewHelper
{
    /**
     * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
     *
     * @param array|string $value
     * @return string the rendered string
     * @api
     */
    public function render($value)
    {
        if (is_array($value)) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }
}
