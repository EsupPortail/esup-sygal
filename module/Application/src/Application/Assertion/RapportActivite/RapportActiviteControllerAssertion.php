<?php

namespace Application\Assertion\RapportActivite;

use Application\Assertion\ControllerAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\These;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Service\UserContextServiceAwareTrait;

class RapportActiviteControllerAssertion extends ControllerAssertion
{
    use ThrowsFailedAssertionExceptionTrait;
    use UserContextServiceAwareTrait;

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->rapport = $context['rapport'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function assert($privilege = null)
    {
        if ($this->rapport === null) {
            return true;
        }
        if ($this->rapport->getThese() === null) {
            return true;
        }

        $this->assertAppartenanceThese();

        return true;
    }

    private function assertAppartenanceThese()
    {
        if ($doctorant = $this->userContextService->getIdentityDoctorant()) {
            $this->assertTrue(
                $this->rapport->getThese()->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        }
        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleDirecteurEcoleDoctorale()) {
            $this->assertTrue(
                $this->rapport->getThese()->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        }
    }
}