<?php

namespace Application\Assertion\RapportActivite;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\These;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Service\UserContextServiceAwareTrait;

class RapportActiviteEntityAssertion implements EntityAssertionInterface
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
            case RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT:
            case RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
            case RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT:
            case RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                $this->assertEtatThese();
        }

        switch ($privilege) {
            case RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
            case RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
            case RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN:
                $this->assertAppartenanceThese();
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
        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleDirecteurEcoleDoctorale()) {
            $this->assertTrue(
                $this->rapport->getThese()->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
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
}