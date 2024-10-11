<?php

namespace Admission\Assertion;

use Admission\Entity\Db\Inscription;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Admission\Entity\Db\Admission;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class AdmissionAbstractAssertion extends AbstractAssertion
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

    protected function assertEtatAdmission(Admission $admission): void
    {
        $this->assertTrue(
            in_array($admission->getEtat()->getCode(), [Admission::ETAT_EN_COURS_SAISIE, Admission::ETAT_EN_COURS_VALIDATION]),
            "Le dossier d'admission doit être en cours"
        );
    }

    protected function assertAppartenanceAdmission(Admission $admission)
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        $individu = $this->userContextService->getIdentityIndividu();
        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first() ? $admission->getInscription()->first() : null;
        //Si le rôle connecté est Candidat
        if ($role->getRoleId() == Role::ROLE_ID_ADMISSION_CANDIDAT) {
            //Si l'étudiant attaché au dossier n'est pas celui de l'individu connecté
            if($admission->getIndividu()->getId() !== $individu->getId()){
                throw new FailedAssertionException("Le dossier d'admission n'appartient pas à l'individu " . $individu);
            }
        } elseif($role->getRoleId() == Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE){
            $message = "Le dossier d'admission n'est pas dirigé par " . $individu;
            if ($inscription && $inscription->getDirecteur()) {
                $this->assertTrue(
                    $inscription->getDirecteur()->getId() === $individu->getId(),
                    $message
                );
            } else if (empty($inscription) || empty($inscription->getDirecteur())) {
                return true;
            } else {
                throw new FailedAssertionException($message);
            }
        } elseif($role->getRoleId() == Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE){
            if($inscription){
                //Si le co-directeur attaché au dossier n'est pas celui de l'individu connecté
                if (!$inscription->getCoDirecteur() || $inscription->getCoDirecteur() && $individu->getId() !== $inscription->getCoDirecteur()->getId()) {
                    throw new FailedAssertionException("Le dossier d'admission n'est pas co-dirigé par " . $individu);
                }
            }
        } elseif ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $message = "Le dossier d'admission n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode();
            if ($inscription && $inscription->getEcoleDoctorale()) {
                $this->assertTrue(
                    $inscription->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                    $message
                );
            } else {
                throw new FailedAssertionException($message);
            }
        } elseif ($roleUniteRech = $this->userContextService->getSelectedRoleUniteRecherche()) {
            $message = "Le dossier d'admission n'est pas rattaché à l'UR " . $roleUniteRech->getStructure()->getCode();
            if ($inscription && $inscription->getUniteRecherche()) {
                $this->assertTrue(
                    $inscription->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                    $message
                );
            } else {
                throw new FailedAssertionException($message);
            }
            // Si les informations d'inscription n'ont pas encore été saisies, ou
            // que les informations ont été saisies mais que le directeur n'a pas encore été renseigné
            // on laisse le droit à un n'importe quel directeur (qui à ce rôle pour l'instant) de n'importe quel établissement
            // de pouvoir rentrer les informations du dossier à la place de l'étudiant
        } elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $message = "Le dossier d'admission n'est pas dirigé par " . $individuUtilisateur;
            if ($inscription && $inscription->getDirecteur()) {
                $this->assertTrue(
                    $inscription->getDirecteur()->getId() === $individuUtilisateur->getId(),
                    $message
                );
            } else if (empty($inscription) || empty($inscription->getDirecteur())) {
                return true;
            } else {
                throw new FailedAssertionException($message);
            }
        } elseif ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $message = "Le dossier d'admission n'est pas codirigé par " . $individuUtilisateur;
            if ($inscription && $inscription->getCoDirecteur()) {
                $this->assertTrue(
                    $inscription->getCoDirecteur()->getId() === $individuUtilisateur->getId(),
                    $message
                );
            } else {
                throw new FailedAssertionException($message);
            }
        }else if($role->getCode() !== Role::CODE_ADMIN_TECH && $role->getCode() !== Role::CODE_BDD){
            throw new FailedAssertionException("Vous ne pouvez pas accéder au module admission");
        }
    }
    protected function getRequestedAdmission(): ?Admission
    {
        $admission = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('individu')) {
            $admission = $this->admissionService->getRepository()->findOneByIndividu($id);
        }

        if (empty($admission)) {
            if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('admission')) {
                $admission = $this->admissionService->getRepository()->find($id);
            }
        }

        return $admission;
    }
    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}