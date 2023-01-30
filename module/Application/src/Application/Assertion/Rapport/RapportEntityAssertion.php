<?php

namespace Application\Assertion\Rapport;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\Role;
use These\Entity\Db\These;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Service\UserContextServiceAwareTrait;

class RapportEntityAssertion implements EntityAssertionInterface
{
    use UserContextServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->rapport = $context['rapport'];
    }

    /**
     * @param string $privilege
     * @return boolean
     * @throws FailedAssertionException
     */
    public function assert($privilege = null): bool
    {
        switch ($privilege) {
            case RapportPrivileges::RAPPORT_CSI_TELEVERSER_TOUT:
            case RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN:
            case RapportPrivileges::RAPPORT_CSI_SUPPRIMER_TOUT:
            case RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN:

            case RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_TOUT:
            case RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_SIEN:
            case RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_TOUT:
            case RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_SIEN:
                $this->assertEtatThese();
        }

        switch ($privilege) {
            case RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN:
            case RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN:
            case RapportPrivileges::RAPPORT_CSI_TELECHARGER_SIEN:

            case RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_SIEN:
            case RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_SIEN:
            case RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_SIEN:
                $this->assertAppartenanceThese();
        }

        switch ($privilege) {
            case RapportPrivileges::RAPPORT_CSI_SUPPRIMER_TOUT:
            case RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN:

            case RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_TOUT:
            case RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_SIEN:
                $this->assertAucuneValidation();
        }

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
        // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEcoleDoctorale() :
        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $this->rapport->getThese()->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        }
        if ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $this->rapport->getThese()->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE) ||
                $this->rapport->getThese()->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        }
    }

    private function assertEtatThese()
    {
        $this->assertTrue(
            in_array($this->rapport->getThese()->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]),
            "La thèse doit être en cours ou soutenue"
        );
    }

    private function assertAucuneValidation()
    {
        $this->assertTrue(
            $this->rapport->getRapportValidation() === null,
            "Le rapport ne doit pas avoir été validé"
        );
    }
}