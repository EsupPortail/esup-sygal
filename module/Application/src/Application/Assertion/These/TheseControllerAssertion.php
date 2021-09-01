<?php

namespace Application\Assertion\These;

use Application\Assertion\ControllerAssertion;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class TheseControllerAssertion extends ControllerAssertion
{
    use UserContextServiceAwareTrait;

    const THESE_CONTROLLER = 'Application\Controller\These';
    const DOCTORANT_CONTROLLER = 'Application\Controller\Doctorant';

    /**
     * @var Doctorant
     */
    private $doctorant;

    /**
     * @var These
     */
    private $these;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        parent::setContext($context);

        if (array_key_exists('these', $context)) {
            $this->these = $context['these'];
        }
        if (array_key_exists('doctorant', $context)) {
            $this->doctorant = $context['doctorant'];
        }
    }

    /**
     * @param string $privilege
     * @return bool
     */
    public function assert($privilege = null)
    {
        switch (true) {
            case $this->selectedRoleIsDoctorant():
                return $this->assertAsDoctorant();
        }

        if ($this->these === null) {
            return false;
        }

//        if (! $this->userContextService->isStructureDuRoleRespecteeForThese($this->these)) {
//            return false;
//        }

        return true;
    }

    protected function assertAsDoctorant(): bool
    {
        $identityDoctorant = $this->getIdentityDoctorant();

        if ($identityDoctorant === null) {
            throw new RuntimeException("Anomalie: le role doctorant est sélectionné mais aucune donnée d'identité doctorant n'est disponible");
        }

        switch (true) {
            case $this->actionIs(self::DOCTORANT_CONTROLLER, 'modifier-persopass'):
                return $this->doctorant && $this->doctorant->getId() === $identityDoctorant->getId();
                break;
        }

        if ($this->these === null) {
            return true;
        }

        return $this->these->getDoctorant()->getId() === $identityDoctorant->getId();
    }

    /**
     * @param string $expectedController
     * @param string $expectedAction
     * @return bool
     */
    private function actionIs($expectedController, $expectedAction)
    {
        return $this->controller === $expectedController && $this->action === $expectedAction;
    }

    /**
     * @return bool
     */
    private function selectedRoleIsDoctorant()
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }

    /**
     * @var Doctorant
     */
    protected $identityDoctorant;

    /**
     * @return Doctorant
     */
    private function getIdentityDoctorant()
    {
        if (null === $this->identityDoctorant) {
            $this->identityDoctorant = $this->userContextService->getIdentityDoctorant();
        }

        return $this->identityDoctorant;
    }
}