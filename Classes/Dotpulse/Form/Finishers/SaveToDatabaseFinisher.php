<?php
namespace Dotpulse\Form\Finishers;

use TYPO3\Flow\Annotations as Flow;
use Dotpulse\Form\Service\FormHelperService;
use Dotpulse\Form\Domain\Repository\FormRepository;
use Dotpulse\Form\Domain\Model\Form;

/**
 * This finisher uploads images to the given folder
 *
 * - folder (mandatory): folder relativ to the /Web folder
 * - allowedTypes (mandatory): array of allowed file types
 */
class SaveToDatabaseFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher
{

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'excludeFields' => array(),
        'dbLabels' => array(),
        'debug' => false
    );

    /**
     * @Flow\Inject
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * @Flow\Inject
     * @var FormHelperService
     */
    protected $formHelperService;

    /**
     * @Flow\Inject
     * @var \TYPO3\Form\Persistence\YamlPersistenceManager
     */
    protected $yamlPersistenceManager;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\I18n\Service
     */
    protected $i18nService;


    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $defaultLocale = $this->i18nService->getConfiguration()->getDefaultLocale();

        $formIdentifier = $formRuntime->getIdentifier();
        $formValues = $formRuntime->getRequest()->getArguments();

        // Exclude Fields
        $excludeFields = $this->parseOption('excludeFields');
        if (is_string($excludeFields)) {
            $excludeFields = explode(',', str_replace(' ', '', trim($excludeFields)));
        }

        // Get Label from Form
        if ($this->yamlPersistenceManager->exists($formIdentifier)) {
            $formSettings = $this->yamlPersistenceManager->load($formIdentifier);
            $formLabel = $formSettings['label'];

            // Get data
            $postValues = array();
            foreach ($formValues as $key => $value) {
                if (!in_array($key, $excludeFields)) {
                    $element = $formRuntime->getFormDefinition()->getElementByIdentifier($key);
                    if ($element) {
                        if (array_key_exists($key, $this->parseOption('dbLabels')) && $this->parseOption('dbLabels')[$key] != '') {
                            $postValues[$key]['label'] = $this->parseOption('dbLabels')[$key];
                        } else {
                            $postValues[$key]['label'] = $this->formHelperService->getTranslatedLabel('label', $element, $defaultLocale);
                        }

                        if (is_array($value)) {
                            foreach ($value as $valueItem) {
                                $translatedValue = $this->formHelperService->getTranslatedLabel('options.' . $valueItem, $element, $defaultLocale);
                                $postValues[$key]['value'][] = $translatedValue == '' ? nl2br($valueItem) : nl2br($translatedValue);
                            }
                        } else {
                            $translatedValue = $this->formHelperService->getTranslatedLabel('options.' . $value, $element, $defaultLocale);
                            $postValues[$key]['value'] = $translatedValue == '' ? nl2br($value) : nl2br($translatedValue);
                        }
                    }
                }
            }

            if ($this->parseOption('debug') == true) {
                \TYPO3\Flow\var_dump($postValues); exit;
            } else {
                if (!empty($postValues)) {
                    $formData = new Form();
                    $formData->setFormIdentifier($formIdentifier);
                    $formData->setFormLabel($formLabel);
                    $formData->setFormValues($postValues);
                    $formData->setCrdate(new \DateTime());
                    $this->formRepository->add($formData);
                    $this->persistenceManager->persistAll();
                }
            }
        }
    }
}
