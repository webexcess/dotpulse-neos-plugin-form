<?php
namespace Dotpulse\Form\Controller\Module;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;

use Dotpulse\Form\Domain\Model\Form;
use Dotpulse\Form\Domain\Repository\FormRepository;
use Dotpulse\Form\Service\FormHelperService;

/**
 * The TYPO3 Management module controller
 *
 * @Flow\Scope("singleton")
 */
class FormController extends \TYPO3\Neos\Controller\Module\AbstractModuleController {

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
     * @var \TYPO3\Flow\I18n\Translator
     */
    protected $translator;

    /**
     * @Flow\Inject
     * @var \TYPO3\Form\Persistence\YamlPersistenceManager
     */
    protected $yamlPersistenceManager;

    /**
     * @return void
     */
    public function indexAction() {
        $returnArray = array();
        $forms = $this->formRepository->findAll();

        foreach ($forms as $form) {
            if ( array_key_exists($form->getFormIdentifier(), $returnArray) ) {
                $total++;
            } else {
                $total = 1;
            }

            $returnArray[$form->getFormIdentifier()]['label'] = $form->getFormLabel();
            $returnArray[$form->getFormIdentifier()]['totals'] = $total;
        }

        $this->view->assignMultiple(array(
            'forms' => $returnArray,
            'argumentNamespace' => $this->request->getArgumentNamespace()
        ));
    }

    /**
     * @param string $formIdentifier
     * @return void
     */
    public function listAction($formIdentifier) {
        $lables = $this->formHelperService->checkAndUpdateFormValues($formIdentifier);
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier);
        
        $this->view->assignMultiple(array(
            'formIdentifier' => $formIdentifier,
            'forms' => $forms,
            'labels' => $lables
        ));
    }

    /**
     * @param string $formIdentifier
     * @param Form $form
     * @return void
     */
    public function deleteAction($formIdentifier, Form $form = NULL) {
        $this->formRepository->remove($form);
        $this->addFlashMessage('The form has been deleted.', 'Form deleted', Message::SEVERITY_OK, array(), 1453154852);
        $this->redirect('list', NULL, NULL, array('formIdentifier' => $formIdentifier));
    }

    /**
     * @param string $formIdentifier
     * @return void
     */
    public function deleteAllAction($formIdentifier) {
        $forms = $this->formRepository->findByFormIdentifier($formIdentifier);
        foreach ($forms as $form) {
            $this->formRepository->remove($form);
        }
        $this->addFlashMessage('All form entries has been deleted.', 'Form entries deleted', Message::SEVERITY_OK, array(), 1453156389);
        $this->redirect('index');
    }

    /**
     * @param string $formIdentifier
     * @return void
     */
    public function editLabelAction($formIdentifier) {
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier, 'asc');
        
        // Get all labels from form-entries
        $labelsFromForms = array();
        foreach ($forms as $form) {
            foreach ($form->getFormValues() as $key => $value) {
                $labelsFromForms[] = $key;
            }
        }

        // get existing labels from form.yaml
        $existingLabels = array();
        if ( $this->yamlPersistenceManager->exists($formIdentifier) ) {
            $formSettings = $this->yamlPersistenceManager->load($formIdentifier);

            foreach ($labelsFromForms as $label) {
                if ( $this->formHelperService->searchInArrayByKey($formSettings, 'identifier', $label) ) {
                    $existingLabels[] = $label;
                }
            }
        }
        $existingLabels = array_unique($existingLabels);

        $formsReturn = array();
        foreach ($forms[0]->getFormValues() as $identifier => $formValue) {
            $formsReturn[$identifier] = array(
                'label' => $formValue['label'],
                'delete' => !in_array($identifier, $existingLabels)
            );
        }
        
        $this->view->assignMultiple(array(
            'formIdentifier' => $formIdentifier,
            'forms' => $formsReturn
        ));
    }

    /**
     * @param string $formIdentifier
     * @param string $identifier
     * @return void
     */
    public function renameLabelAction($formIdentifier, $identifier) {
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier);
        $value = $this->formHelperService->getLabelByIdentifier($forms, $identifier);
        $this->view->assignMultiple(array(
            'formIdentifier' => $formIdentifier,
            'identifier' => $identifier,
            'value' => $value
        ));
    }

    /**
     * @param string $formIdentifier
     * @param string $identifier
     * @param string $name
     * @return void
     */
    public function updateLabelAction($formIdentifier, $identifier, $name) {
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier);
        
        foreach ($forms as $form) {
            $formValues = $form->getFormValues();
            $formValuesNew = array();

            foreach ($formValues as $formValueIdentifier => $formValue) {
                if ( $formValueIdentifier == $identifier ) {
                    $formValuesNew[$formValueIdentifier]['label'] = $name;
                    $formValuesNew[$formValueIdentifier]['value'] = $formValues[$formValueIdentifier]['value'];
                } else {
                    $formValuesNew[$formValueIdentifier] = $formValues[$formValueIdentifier];
                }
            }

            $form->setFormValues($formValuesNew);
            $this->formRepository->update($form);
        }

        $this->persistenceManager->persistAll();
        $this->addFlashMessage('The Label has been updated', 'Label updated', Message::SEVERITY_OK, array(), 1453824496);
        $this->redirect('editLabel', NULL, NULL, array('formIdentifier' => $formIdentifier));
    }

    /**
     * @param string $formIdentifier
     * @param string $identifier
     * @return void
     */
    public function deleteLabelAction($formIdentifier, $identifier) {
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier);
        
        foreach ($forms as $form) {
            $formValues = $form->getFormValues();
            $formValuesNew = array();

            foreach ($formValues as $formValueIdentifier => $formValue) {
                if ( $formValueIdentifier != $identifier ) {
                    $formValuesNew[$formValueIdentifier] = $formValues[$formValueIdentifier];
                }
            }

            $form->setFormValues($formValuesNew);
            $this->formRepository->update($form);
        }

        $this->persistenceManager->persistAll();
        $this->addFlashMessage('The Label has been deleted', 'Label deleted', Message::SEVERITY_OK, array(), 1453826830);
        $this->redirect('editLabel', NULL, NULL, array('formIdentifier' => $formIdentifier));
    }

    /**
     * @param string $formIdentifier
     * @return void
     */
    public function exportAction($formIdentifier) {
        $outputArr = array();
        $formLabel = '';

        $lables = $this->formHelperService->checkAndUpdateFormValues($formIdentifier);
        $forms = $this->formRepository->findByFormIdentifierSorted($formIdentifier);

        if ( $forms ) {
            $i = 0;
            foreach ($forms as $form) {
                $formLabel = $form->getFormLabel();
                foreach ($form->getFormValues() as $key => $value) {
                    $outputArr['label'][$key] = $value['label'];
                    $outputArr['form_' . $i][$key] = $value['value'];
                }
                $outputArr['form_' . $i]['date'] = $form->getCrdate()->format('Y-m-d H:i:s');
                $i++;
            }
            $outputArr['label']['date'] = $this->translator->translateById('label.form.date', array(), null, null, 'Modules', 'Dotpulse.Form');
        }

        if ( !empty($outputArr) ) {
            $this->downloadSendHeaders(date("Y-m-d") . '-' . str_replace(' ', '_', $formLabel) . '.csv');
            $out = fopen('php://output', 'w');
            foreach ($outputArr as $values) {
                fputcsv($out, $values);
            }
            fclose($out);
        }
        exit;
    }

    protected function downloadSendHeaders($filename) {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }
}
