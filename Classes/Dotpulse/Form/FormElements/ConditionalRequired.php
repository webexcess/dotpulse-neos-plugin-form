<?php
namespace Dotpulse\Form\FormElements;

class ConditionalRequired extends \TYPO3\Form\Core\Model\AbstractFormElement
{

    /**
     * Check if the element is required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return true;
    }

    /**
     * Executed before the current element is outputted to the client
     *
     * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
     * @return void
     */
    public function beforeRendering(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime)
    {
        $this->requireIfTriggerIsSet($formRuntime->getFormState());
    }

    /**
     * Executed after the page containing the current element has been submitted
     *
     * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
     * @param mixed $elementValue raw value of the submitted element
     * @return void
     */
    public function onSubmit(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue)
    {
        $this->requireIfTriggerIsSet($formRuntime->getFormState());
    }

    /**
     * Adds a NotEmptyValidator to the current element if the "trigger" value is not empty.
     * The trigger can be configured with $this->properties['triggerPropertyPath']
     *
     * @param \TYPO3\Form\Core\Runtime\FormState $formState
     * @return void
     */
    protected function requireIfTriggerIsSet(\TYPO3\Form\Core\Runtime\FormState $formState)
    {
        $field = $this->properties['triggerPropertyField'];
        $type = isset($this->properties['triggerPropertyType']) ? $this->properties['triggerPropertyType'] : 'input';
        $defaultValue = isset($this->properties['triggerPropertyValue']) ? $this->properties['triggerPropertyValue'] : '';

        if (!isset($field)) {
            return;
        }

        $triggerValue = $formState->getFormValue($field);

        if ($type == 'input') {
            if ($triggerValue === NULL || $triggerValue != $defaultValue) {
                return;
            }
        } elseif ($type == 'checkbox') {
            if ($triggerValue != $defaultValue) {
                return;
            }
        }

        $this->addValidator(new \TYPO3\Flow\Validation\Validator\NotEmptyValidator());
    }
}
