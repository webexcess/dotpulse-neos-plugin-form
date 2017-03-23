<?php
namespace Dotpulse\Form\Finishers;

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;

use Dotpulse\Form\Domain\Repository\FormRepository;
use Dotpulse\Form\Domain\Model\Form;

/**
 * This finisher uploads images to the given folder
 *
 * - folder (mandatory): folder relativ to the /Web folder
 * - allowedTypes (mandatory): array of allowed file types
 */
class SaveToDatabaseFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'excludeFields' => array()
    );

    /**
     * @Flow\Inject
     * @var FormRepository
     */
    protected $formRepository;

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
     * @Flow\Inject
     * @var \TYPO3\Flow\I18n\Translator
     */
    protected $translator;

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    protected function executeInternal() {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $response = $formRuntime->getResponse();
        $defaultLocale = $this->i18nService->getConfiguration()->getDefaultLocale();
        
        $formIdentifier = $formRuntime->getIdentifier();
        $formLabel = $formRuntime->getLabel();
        $formValues = $formRuntime->getRequest()->getArguments();
        
        $renderingOptions = $formRuntime->getRenderingOptions();
        $translationPackage = $renderingOptions['translationPackage'];

        // Exclude Fields
        $excludeFields = $this->parseOption('excludeFields');
        if ( is_string($excludeFields) ) {
            $excludeFields = explode(',', str_replace(' ', '', trim($excludeFields)));
        }

        // Get Label from Form
        if ( $this->yamlPersistenceManager->exists($formIdentifier) ) {
            $formSettings = $this->yamlPersistenceManager->load($formIdentifier);
            $formLabel = $formSettings['label'];
        }

        $postValues = array();
        foreach ($formValues as $key => $value) {
            if ( !in_array($key, $excludeFields) ) {
                $postValues[$key]['label'] = $this->translator->translateById($key, array(), null, $defaultLocale, 'Forms', $translationPackage);
                $postValues[$key]['value'] = $value;
            }
        }
        // echo '<pre>'.print_r($postValues, true).'</pre>'; exit;

        if ( !empty($postValues) ) {
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
