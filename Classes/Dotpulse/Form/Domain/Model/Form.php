<?php
namespace Dotpulse\Form\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Form {

    /**
     * @var string
     */
    protected $formIdentifier;

    /**
     * @var string
     */
    protected $formLabel;

    /**
     * @ORM\Column(type="flow_json_array")
     * @var array
     */
    protected $formValues = array();

    /**
     * @var \DateTime
     */
    protected $crdate;


    /**
     * @return string
     */
    public function getFormIdentifier() {
        return $this->formIdentifier;
    }

    /**
     * @param string $formIdentifier
     * @return void
     */
    public function setFormIdentifier($formIdentifier) {
        $this->formIdentifier = $formIdentifier;
    }

    /**
     * @return string
     */
    public function getFormLabel() {
        return $this->formLabel;
    }

    /**
     * @param string $formLabel
     * @return void
     */
    public function setFormLabel($formLabel) {
        $this->formLabel = $formLabel;
    }

    /**
     * @return array
     */
    public function getFormValues() {
        return $this->formValues;
    }

    /**
     * @param array $formValues
     * @return void
     */
    public function setFormValues($formValues) {
        $this->formValues = $formValues;
    }

    /**
     * @return \DateTime
     */
    public function getCrdate() {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return void
     */
    public function setCrdate(\DateTime $crdate) {
        $this->crdate = $crdate;
    }

}
