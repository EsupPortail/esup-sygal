<?php

namespace RapportActivite\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class RapportActiviteOperationAbstractAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use RapportActiviteServiceAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    /**
     * @param array $page
     * @return bool
     */
    private function assertPage(array $page): bool
    {
        return true;
    }

    protected function assertEtatThese(These $these)
    {
        $this->assertTrue(
            in_array($these->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]),
            "La thèse doit être en cours ou soutenue"
        );
    }

    /**
     * todo :
     */
    protected function assertAppartenanceThese(These $these)
    {
        // rôle doctorant
        if ($doctorant = $this->userContextService->getIdentityDoctorant()) {
            $this->assertTrue(
                $these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        }

        // rôles structure-dépendants : ED, UR
        $role = $this->userContextService->getSelectedIdentityRole();
        if ($role->isStructureDependant()) {
            $structure = null;
            if ($role->getTypeStructureDependant()->isEcoleDoctorale()) {
                $structure = $these->getEcoleDoctorale()->getStructure();
            } elseif ($role->getTypeStructureDependant()->isUniteRecherche()) {
                $structure = $these->getUniteRecherche()->getStructure();
            }
            if ($structure !== null) {
                $this->assertTrue(
                    $structure->getId() === $role->getStructure()->getId(),
                    "La thèse n'est pas rattachée à la structure '{$role->getStructure()->getCode()}' ({$role->getStructure()->getTypeStructure()})"
                );
            }
        }

        // rôle directeur de thèse
        if ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        }

        // rôle codirecteur de thèse
        if ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas codirigée par " . $individuUtilisateur
            );
        }
    }

    protected function findLastCompletedOperation(RapportActivite $rapportActivite): ?RapportActiviteOperationInterface
    {
        return $this->rapportActiviteOperationRule->findLastCompletedOperation($rapportActivite);
    }

    protected function findNextExpectedOperation(RapportActivite $rapportActivite): ?RapportActiviteOperationInterface
    {
        return $this->rapportActiviteOperationRule->findNextExpectedOperation($rapportActivite);
    }

    protected function assertOperationsMatch(RapportActiviteOperationInterface $operation, ?RapportActiviteOperationInterface $expectedOperation = null)
    {
        $this->assertTrue(
            $expectedOperation !== null && $operation->matches($expectedOperation),
            "L'opération n'est pas celle attendue pour ce rapport"
        );
    }

    protected function assertOperationIsAllowed(RapportActiviteOperationInterface $operation)
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        $this->assertFalse(
            $this->rapportActiviteOperationRule->isOperationReadonly($operation),
            "L'opération attendue est en lecture seule (non réalisable)"
        );
        $this->assertTrue(
            $this->rapportActiviteOperationRule->isOperationAllowedByRole($operation, $role),
            "L'opération attendue pour ce rapport concerne un rôle utilisateur différent"
        );
    }

    protected function assertFollowingOperationCompatible(RapportActiviteOperationInterface $operation)
    {
        $this->assertTrue(
            $this->rapportActiviteOperationRule->isFollowingOperationValueCompatible($operation),
            "La valeur de l'opération réalisée suivante ne permet pas de réaliser cette opération"
        );
    }

    protected function assertPrecedingOperationValueCompatible(RapportActiviteOperationInterface $operation)
    {
        $this->assertTrue(
            $this->rapportActiviteOperationRule->isPrecedingOperationValueCompatible($operation),
            "La valeur de l'opération réalisée précédemment ne permet pas de réaliser cette opération"
        );
    }
}