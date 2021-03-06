<?php
namespace Dotpulse\Form\Finishers;

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
class EmailWithAttachmentFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher
{
    const FORMAT_PLAINTEXT = 'plaintext';
    const FORMAT_HTML = 'html';

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'recipientAddressFallback' => '',
        'recipientName' => '',
        'senderName' => '',
        'excludeFields' => array(),
        'attachmentFields' => array(),
        'format' => self::FORMAT_HTML,
        'testMode' => false,
    );

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
        $attachmentFields = is_array($this->parseOption('attachmentFields')) ? $this->parseOption('attachmentFields') : explode(',', preg_replace('/\s+/', '', $this->parseOption('attachmentFields')));

        $attachments = array();
        if (!empty($attachmentFields)) {
            foreach ($attachmentFields as $attachmentField) {
                if ($formRuntime->getRequest()->hasArgument($attachmentField)) {
                    $tmpFile = $formRuntime->getRequest()->getArgument($attachmentField);
                    $attachments[] = \Swift_Attachment::newInstance(file_get_contents($tmpFile['tmp_name']), $tmpFile['name'], $tmpFile['type']);
                }
            }
        }

        $arguments = $formRuntime->getRequest()->getArguments();
        $argumentsTmp = array();
        foreach ($arguments as $argument => $value) {
            if ( !in_array($argument, $excludeFields) && !in_array($argument, $attachmentFields) ) {
                $argumentsTmp[$argument] = $value;
            }
        }
        $formRuntime->getRequest()->setArguments($argumentsTmp);

        $standaloneView = $this->initializeStandaloneView();
        $standaloneView->assign('form', $formRuntime);
        $message = $standaloneView->render();

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

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->attach($attachment);
            }
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
        return parent::parseOption($optionName);
    }
}
