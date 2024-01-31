<?php

namespace Admission\Assertion;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\TypeValidation;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use Admission\Entity\Db\Admission;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class AdmissionOperationAbstractAssertion extends AdmissionAbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use AdmissionServiceAwareTrait;
    use AdmissionOperationRuleAwareTrait;

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

    protected function assertEtatAdmission(Admission $admission)
    {
        $this->assertTrue(
            in_array($admission->getEtat()->getCode(), [Admission::ETAT_EN_COURS_SAISIE, Admission::ETAT_EN_COURS_VALIDATION]),
            "Le dossier d'admission doit être en cours"
        );
    }

    protected function assertDossierCompletAdmission(AdmissionValidation $admissionValidation)
    {
        //cette condition ne concerne pas la première validation du dossier
        if($admissionValidation->getTypeValidation()->getCode() == TypeValidation::CODE_ATTESTATION_HONNEUR_CHARTE_DOCTORALE){
            return;
        }
        $this->assertTrue(
            $admissionValidation->getAdmission()->isDossierComplet() === true,
            "Le dossier d'admission doit être en complet"
        );
    }

    /**
     * todo :
     */
    protected function assertAppartenanceAdmission(Admission $admission)
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($role->getRoleId() == Role::ROLE_ID_USER) {
            $individu = $this->userContextService->getIdentityIndividu();

            $this->assertTrue(
                $admission->getIndividu()->getId() === $individu->getId(),
                "Le dossier d'admission n'appartient pas à l'individu " . $individu
            );
        }

        // rôles structure-dépendants : ED, UR
        $role = $this->userContextService->getSelectedIdentityRole();
        if ($role->isStructureDependant()) {
            $structure = null;
            if ($role->getTypeStructureDependant()->isEcoleDoctorale()) {
                $structure = $admission->getInscription()->first()->getEcoleDoctorale()->getStructure();
            } elseif ($role->getTypeStructureDependant()->isUniteRecherche()) {
                $structure = $admission->getInscription()->first()->getUniteRecherche()->getStructure();
            }
            if ($structure !== null) {
                $this->assertTrue(
                    $structure->getId() === $role->getStructure()->getId(),
                    "Le dossier d'admission n'est pas rattaché à la structure '{$role->getStructure()->getCode()}' ({$role->getStructure()->getTypeStructure()})"
                );
            }
        }

        // rôle directeur de thèse
        if ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $individuUtilisateur->getId() == $admission->getInscription()->first()->getDirecteur()->getId(),
                "Le dossier d'admission n'est pas dirigé par " . $individuUtilisateur
            );
        }

        // rôle codirecteur de thèse
        if ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $individuUtilisateur->getId() == $admission->getInscription()->first()->getCoDirecteur()->getId(),
                "Le dossier d'admission n'est pas co-dirigé par " . $individuUtilisateur
            );
        }
    }

    protected function findLastCompletedOperation(Admission $admission): ?AdmissionOperationInterface
    {
        return $this->admissionOperationRule->findLastCompletedOperation($admission);
    }

    protected function findNextExpectedOperation(Admission $admission): ?AdmissionOperationInterface
    {
        return $this->admissionOperationRule->findNextExpectedOperation($admission);
    }

    protected function assertOperationsMatch(AdmissionOperationInterface $operation, ?AdmissionOperationInterface $expectedOperation = null)
    {
        $this->assertTrue(
            $expectedOperation !== null && $operation->matches($expectedOperation),
            "L'opération n'est pas celle attendue pour ce rapport"
        );
    }

    protected function assertOperationIsAllowed(AdmissionOperationInterface $operation)
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        $this->assertFalse(
            $this->admissionOperationRule->isOperationReadonly($operation),
            "L'opération attendue est en lecture seule (non réalisable)"
        );
        $this->assertTrue(
            $this->admissionOperationRule->isOperationAllowedByRole($operation, $role),
            "L'opération attendue pour ce rapport concerne un rôle utilisateur différent"
        );
    }

    protected function assertFollowingOperationCompatible(AdmissionOperationInterface $operation)
    {
        $this->assertTrue(
            $this->admissionOperationRule->isFollowingOperationValueCompatible($operation),
            "La valeur de l'opération réalisée suivante ne permet pas de réaliser cette opération"
        );
    }

    protected function assertPrecedingOperationValueCompatible(AdmissionOperationInterface $operation)
    {
        $this->assertTrue(
            $this->admissionOperationRule->isPrecedingOperationValueCompatible($operation),
            "La valeur de l'opération réalisée précédemment ne permet pas de réaliser cette opération"
        );
    }
}