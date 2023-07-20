<?php

namespace Application\Assertion\Rapport;

use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\Role;
use These\Entity\Db\These;
use Application\Service\AuthorizeServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;

class RapportPageAssertion implements PageAssertionInterface, UserContextServiceAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use AuthorizeServiceAwareTrait;
    use UserContextServiceAwareTrait;
    
    /**
     * @var These
     */
    private $these;

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->these = $context['these'] ?? null;
        $this->rapport = $context['rapport'] ?? null;
    }

    /**
     * @param array $page
     * @return bool
     */
    public function assert(array $page): bool
    {
        if ($this->these === null) {
            return true;
        }

        $this->assertEtatThese();
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
                $this->these->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE) ||
                $this->these->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        }
    }

    private function assertEtatThese()
    {
        $this->assertTrue(
            in_array($this->these->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]),
            "La thèse doit être en cours ou soutenue"
        );
    }
}