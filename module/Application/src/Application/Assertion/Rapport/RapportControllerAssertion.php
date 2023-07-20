<?php

namespace Application\Assertion\Rapport;

use Application\Assertion\ControllerAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Service\UserContextServiceAwareTrait;

class RapportControllerAssertion extends ControllerAssertion
{
    use ThrowsFailedAssertionExceptionTrait;
    use UserContextServiceAwareTrait;

    /**
     * @var \These\Entity\Db\These
     */
    private $these;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->these = $context['these'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function assert($privilege = null): bool
    {
        if ($this->these === null) {
            return true;
        }

        $this->assertAppartenanceThese();

        return true;
    }

    private function assertAppartenanceThese()
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        // rôle doctorant
        if ($role->isDoctorant()) {
            $doctorant = $this->userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $this->these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        }
        // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEcoleDoctorale() :
        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $this->these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        }
        if ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $this->these->hasActeurWithRole($individuUtilisateur, \Application\Entity\Db\Role::CODE_DIRECTEUR_THESE) ||
                $this->these->hasActeurWithRole($individuUtilisateur, \Application\Entity\Db\Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        }
    }
}