<?php
namespace Dotpulse\Form\Service;

/*
 * This file is part of the TYPO3.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use Dotpulse\Form\Domain\Model\Form;
use Dotpulse\Form\Domain\Repository\FormRepository;
use TYPO3\Form\Core\Model\FormElementInterface;
use TYPO3\Form\Core\Runtime\FormRuntime;
use TYPO3\Flow\I18n\Translator;
use TYPO3\Flow\Resource\Exception as ResourceException;

/**
 * @Flow\Scope("singleton")
 */
class FormHelperService
{

    /**
     * @Flow\Inject
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @param array $array
     * @param string $key
     * @param mixed $value can be a array or a string. the string can also be comma seperated
     * @return string
     */
    public function searchInArrayByKey($array, $key, $value)
    {
        $results = array();
        if (is_string($value)) {
            $value = explode(',', str_replace(' ', '', trim($value)));
        }

        if (is_array($array)) {
            if (isset($array[$key]) && in_array($array[$key], $value)) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->searchInArrayByKey($subarray, $key, $value));
            }
        }

        return $results;
    }

    /**
     * @param array $forms
     * @return void
     */
    protected function getLabels($forms)
    {
        $labels = array();
        foreach ($forms as $form) {
            $values = $form->getFormValues();
            foreach ($values as $identifier => $value) {
                $labels[$identifier] = $value['label'];
            }
        }
        return $labels;
    }

    /**
     * @param array $forms
     * @param string $identifier
     * @return void
     */
    public function getLabelByIdentifier($forms, $identifier)
    {
        $labels = $this->getLabels($forms);
        return $labels[$identifier];
    }

    /**
     * @param string $formIdentifier
     * @return void
     */
    public function checkAndUpdateFormValues($formIdentifier)
    {
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier);
        $labels = $this->getLabels($forms);

        foreach ($forms as $form) {
            $formValues = $form->getFormValues();
            $formValuesNew = array();

            foreach ($labels as $identifier => $formValue) {
                $formValuesNew[$identifier] = isset($formValues[$identifier]) ? $formValues[$identifier] : array('label' => $labels[$identifier], 'value' => '');
            }

            $form->setFormValues($formValuesNew);
            $this->formRepository->update($form);
        }
        $this->persistenceManager->persistAll();

        return $labels;
    }

    /**
     * @param string $property
     * @param FormElementInterface $element
     * @param \TYPO3\Flow\I18n\Locale|null $locale
     * @return string
     */
    public function getTranslatedLabel($property, FormElementInterface $element, \TYPO3\Flow\I18n\Locale $locale = null)
    {
        if ($property === 'label') {
            $defaultValue = $element->getLabel();
        } elseif (strpos($property, 'options.') !== false && array_key_exists('options', $element->getProperties())) {
            $key = substr($property, strpos($property, '.') + 1);
            if ($key) {
                $defaultValue = array_key_exists($key, $element->getProperties()['options']) ? $element->getProperties()['options'][$key] : '';
            } else {
                $defaultValue = '';
            }
        } else {
            $defaultValue = isset($element->getProperties()[$property]) ? (string)$element->getProperties()[$property] : '';
        }

        $renderingOptions = $element->getRenderingOptions();
        if (!isset($renderingOptions['translateBy'])) {
            return $defaultValue;
        }

        $translateBy = $element->getIdentifier();
        if ($renderingOptions['translateBy'] == 'label') {
            $translateBy = $element->getLabel() ? $element->getLabel() : $element->getIdentifier();
            if (strpos($property, 'options.') !== false && $defaultValue != '') {
                $property = 'options.' . $defaultValue;
            }
        }

        $translationId = sprintf('forms.%s.%s.%s', $renderingOptions['renderableNameInTemplate'], $translateBy, $property);

        try {
            $translation = $this->translator->translateById($translationId, [], null, $locale, $renderingOptions['translationFile'], $renderingOptions['translationPackage']);
        } catch (ResourceException $exception) {
            return $defaultValue;
        }

        if ($translation == $translationId) {
            return $defaultValue;
        }
        return $translation === null ? $defaultValue : $translation;
    }
}
