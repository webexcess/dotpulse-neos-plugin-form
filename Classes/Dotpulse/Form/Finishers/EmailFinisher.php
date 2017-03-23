<?php
namespace Dotpulse\Form\Finishers;

use TYPO3\Flow\Annotations as Flow;
use Dotpulse\Form\Service\FormHelperService;

    /*                                                                        *
     * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
     *                                                                        *
     * It is free software; you can redistribute it and/or modify it under    *
     * the terms of the GNU Lesser General Public License, either version 3   *
     * of the License, or (at your option) any later version.                 *
     *                                                                        *
     * The TYPO3 project - inspiring people to share!                         *
     *                                                                        */

/**
 * This finisher sends an email to one recipient
 *
 * Options:
 *
 * - templatePathAndFilename (mandatory): Template path and filename for the mail body
 * - layoutRootPath: root path for the layouts
 * - partialRootPath: root path for the partials
 * - variables: associative array of variables which are available inside the Fluid template
 *
 * The following options control the mail sending. In all of them, placeholders in the form
 * of {...} are replaced with the corresponding form value; i.e. {email} as recipientAddress
 * makes the recipient address configurable.
 *
 * - subject (mandatory): Subject of the email
 * - recipientAddress (mandatory): Email address of the recipient
 * - recipientName: Human-readable name of the recipient
 * - senderAddress (mandatory): Email address of the sender
 * - senderName: Human-readable name of the sender
 * - replyToAddress: Email address of to be used as reply-to email (use multiple addresses with an array)
 * - carbonCopyAddress: Email address of the copy recipient (use multiple addresses with an array)
 * - blindCarbonCopyAddress: Email address of the blind copy recipient (use multiple addresses with an array)
 * - format: format of the email (one of the FORMAT_* constants). By default mails are sent as HTML
 * - testMode: if TRUE the email is not actually sent but outputted for debugging purposes. Defaults to FALSE
 */
class EmailFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher
{
    const FORMAT_PLAINTEXT = 'plaintext';
    const FORMAT_HTML = 'html';

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'useRecipientFromInspector' => true,
        'recipientName' => '',
        'senderName' => '',
        'excludeFields' => array(),
        'format' => self::FORMAT_HTML,
        'testMode' => false,
        'debug' => false,
    );

    /**
     * @Flow\Inject
     * @var FormHelperService
     */
    protected $formHelperService;

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

        $excludeFields = is_array($this->parseOption('excludeFields')) ? $this->parseOption('excludeFields') : explode(',', preg_replace('/\s+/', '', $this->parseOption('excludeFields')));

        $arguments = $formRuntime->getRequest()->getArguments();
        $argumentsTmp = array();
        foreach ($arguments as $argument => $value) {
            if ( !in_array($argument, $excludeFields) ) {
                $argumentsTmp[$argument] = $value;
            }
        }
        $formRuntime->getRequest()->setArguments($argumentsTmp);

        $formValues = array();
        foreach ($formRuntime->getRequest()->getArguments() as $key => $value) {
            $element = $formRuntime->getFormDefinition()->getElementByIdentifier($key);

            if ($element) {
                $formValues[$key]['label'] = $this->formHelperService->getTranslatedLabel('label', $element);

                if (is_array($value)) {
                    foreach ($value as $valueItem) {
                        $translatedValue = $this->formHelperService->getTranslatedLabel('options.' . $valueItem, $element);
                        $formValues[$key]['value'][] = $translatedValue == '' ? nl2br($valueItem) : nl2br($translatedValue);
                    }
                } else {
                    $translatedValue = $this->formHelperService->getTranslatedLabel('options.' . $value, $element);
                    $formValues[$key]['value'] = $translatedValue == '' ? nl2br($value) : nl2br($translatedValue);
                }
            }
        }

        $standaloneView = $this->initializeStandaloneView();
        $standaloneView->assign('formValues', $formValues);
        $standaloneView->assign('postValues', $formRuntime->getRequest()->getArguments());
        $message = $standaloneView->render();

        if ($this->parseOption('debug')) {
            echo $message; exit;
        }

        $recipientAddress = null;
        if ( $this->parseOption('recipientAddress') != '' ) {
            $recipientAddress = $this->parseOption('recipientAddress');
        } else {
            if ( $this->parseOption('recipientAddressFallback') ) {
                $recipientAddress = $this->parseOption('recipientAddressFallback');
            }
        }

        $subject = $this->parseOption('subject');
        $recipientName = $this->parseOption('recipientName');
        $senderAddress = $this->parseOption('senderAddress');
        $senderName = $this->parseOption('senderName');
        $replyToAddress = $this->parseOption('replyToAddress');
        $carbonCopyAddress = $this->parseOption('carbonCopyAddress');
        $blindCarbonCopyAddress = $this->parseOption('blindCarbonCopyAddress');
        $format = $this->parseOption('format');
        $testMode = $this->parseOption('testMode');

        if ($subject === null) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if ($recipientAddress === null) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "recipientAddress" must be set for the EmailFinisher.', 1327060200);
        }
        if ($senderAddress === null) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $mail = new \TYPO3\SwiftMailer\Message();

        $mail
            ->setFrom(array($senderAddress => $senderName))
            ->setTo(array($recipientAddress => $recipientName))
            ->setSubject($subject);

        if ($replyToAddress !== null) {
            $mail->setReplyTo($replyToAddress);
        }

        if ($carbonCopyAddress !== null) {
            $mail->setCc($carbonCopyAddress);
        }

        if ($blindCarbonCopyAddress !== null) {
            $mail->setBcc($blindCarbonCopyAddress);
        }

        if ($format === self::FORMAT_PLAINTEXT) {
            $mail->setBody($message, 'text/plain');
        } else {
            $mail->setBody($message, 'text/html');
        }

        if ($testMode === true) {
            \TYPO3\Flow\var_dump(
                array(
                    'sender' => array($senderAddress => $senderName),
                    'recipient' => array($recipientAddress => $recipientName),
                    'replyToAddress' => $replyToAddress,
                    'carbonCopyAddress' => $carbonCopyAddress,
                    'blindCarbonCopyAddress' => $blindCarbonCopyAddress,
                    'message' => $message,
                    'format' => $format,
                ),
                'E-Mail "' . $subject . '"'
            );
        } else {
            $mail->send();
        }
    }

    /**
     * @return \TYPO3\Fluid\View\StandaloneView
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    protected function initializeStandaloneView()
    {
        //\TYPO3\Flow\var_dump($this->options);
        $standaloneView = new \TYPO3\Fluid\View\StandaloneView();
        if (!isset($this->options['templatePathAndFilename'])) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "templatePathAndFilename" must be set for the EmailFinisher.', 1327058829);
        }
        $standaloneView->setTemplatePathAndFilename($this->options['templatePathAndFilename']);

        if (isset($this->options['partialRootPath'])) {
            $standaloneView->setPartialRootPath($this->options['partialRootPath']);
        }

        if (isset($this->options['layoutRootPath'])) {
            $standaloneView->setLayoutRootPath($this->options['layoutRootPath']);
        }

        if (isset($this->options['variables'])) {
            $standaloneView->assignMultiple($this->options['variables']);
        }

        //\TYPO3\Flow\var_dump($standaloneView->getRequest()->getArguments());
        return $standaloneView;
    }

    /**
     * Extends the functionality of the default parseOption() method
     * by making node-properties available
     *
     * @param string $optionName
     * @return mixed|string
     */
    protected function parseOption($optionName) {
        if (!isset($this->options[$optionName]) || $this->options[$optionName] === '') {
            if (isset($this->defaultOptions[$optionName])) {
                $option = $this->defaultOptions[$optionName];
            } else {
                return NULL;
            }
        } else {
            $option = $this->options[$optionName];
        }
        if (!is_string($option)) {
            return $option;
        }
        if(preg_match('/{node\.([^}]+)}/', $option, $matches)) {
            $renderingOptions = $this->finisherContext->getFormRuntime()->getRenderingOptions();
            if(isset($renderingOptions['node'])) {
                $node = $renderingOptions['node'];
                if($node->hasProperty($matches[1])) {
                    $property = $node->getProperty($matches[1]);
                    if(!empty($property)) {
                        return $property;
                    }
                }
            }
        }


        if ($optionName === 'subject') {
            $renderingOptions = $this->finisherContext->getFormRuntime()->getRenderingOptions();
            $node = $renderingOptions['node'];
            if ($node->hasProperty('subject')) {
                $recipientName = $node->getProperty('subject');
                if(!empty($recipientName)) {
                    return $recipientName;
                }
            }
        }

        if ($this->parseOption('useRecipientFromInspector') === true) {
            // check if recipient name is provided by the node
            // use the recipientName defined in the form as fallback
            if ($optionName === 'recipientName') {
                $renderingOptions = $this->finisherContext->getFormRuntime()->getRenderingOptions();
                $node = $renderingOptions['node'];
                if ($node->hasProperty('recipientName')) {
                    $recipientName = $node->getProperty('recipientName');
                    if(!empty($recipientName)) {
                        return $recipientName;
                    }
                }
            }
            // check if recipient address is provided by the node
            // use the recipientAddress defined in the form as fallback
            if ($optionName === 'recipientAddress') {
                $renderingOptions = $this->finisherContext->getFormRuntime()->getRenderingOptions();
                $node = $renderingOptions['node'];
                if ($node->hasProperty('recipientAddress')) {
                    $recipientAddress = $node->getProperty('recipientAddress');
                    if(!empty($recipientAddress) && filter_var($recipientAddress, FILTER_VALIDATE_EMAIL)) {
                        return $recipientAddress;
                    }
                }
            }
        }

        return parent::parseOption($optionName);
    }
}
