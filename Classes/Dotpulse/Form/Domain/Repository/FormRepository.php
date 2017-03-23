<?php
namespace Dotpulse\Form\Domain\Repository;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class FormRepository extends Repository {

    /**
     * @param string $formIdentifier
     * @param string $direction
     * @return \TYPO3\Flow\Persistence\QueryResultInterface
     */
    public function findByFormIdentifierSorted($formIdentifier, $direction = 'desc') {
        $query = $this->createQuery();
        
        $query->matching(
            $query->equals('formIdentifier', $formIdentifier)
        );
        $query->setOrderings(array(
            'crdate' => $direction == 'desc' ? \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING : \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING
        ));

        return $query->execute();
    }
    
}
