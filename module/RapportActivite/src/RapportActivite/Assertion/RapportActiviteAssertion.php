<?php

namespace RapportActivite\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Rule\Creation\RapportActiviteCreationRuleAwareTrait;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use RuntimeException;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class RapportActiviteAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use UserContextServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;

    use RapportActiviteCreationRuleAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;

    private ?RapportActivite $rapportActivite = null;
    private ?These $these;

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
        $this->these = $this->getRequestedThese();
        if ($this->these === null) {
            return true;
        }

        try {
//            $this->assertEtatThese($this->these);
            $this->assertAppartenanceThese($this->these);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

//        $these = $this->getRequestedThese();
//        if ($these === null) {
//            return true;
//        }

        if (!$this->initForControllerAction($action)) {
            return false;
        }

        try {
            switch ($action) {
                case 'lister':
                    // je ne comprends pas pourquoi : on peut arriver ici sans thèse spécifiée dans l'URL !
                    if ($this->these !== null) {
                        $this->assertAppartenanceThese($this->these);
                    }
                    break;

                case 'ajouter':
                case 'modifier':
                case 'supprimer':
                case 'consulter':
                case 'telecharger':
                case 'generer':
                    $this->assertAppartenanceThese($this->rapportActivite->getThese());
                    break;
            }

            switch ($action) {
                case 'ajouter':
                case 'modifier':
                case 'supprimer':
                    $this->assertEtatThese($this->rapportActivite->getThese());
                    break;
            }

            switch ($action) {
                case 'ajouter':
                    $this->assertCreationPossible();
                    break;

                case 'modifier':
                case 'supprimer':
                    $this->assertModificationPossible($this->rapportActivite);
                    break;
            }

            switch ($action) {
                case 'telecharger':
                    $this->assertRapportEstNonDematerialise($this->rapportActivite);
                    break;
            }

            switch ($action) {
                case 'consulter':
                case 'modifier':
                case 'generer':
                    $this->assertRapportEstDematerialise($this->rapportActivite);
                    break;
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    private function initForControllerAction(string $action): bool
    {
        switch ($action) {
            case 'lister':
                $this->these = $this->getRequestedThese();
                break;

            case 'consulter':
            case 'modifier':
            case 'supprimer':
            case 'generer':
            case 'telecharger':
                $this->rapportActivite = $this->getRequestedRapport();
                break;

            case 'ajouter':
                $this->rapportActivite = $this->rapportActiviteService->newRapportActivite($this->getRequestedThese());
                break;

            default:
                throw new InvalidArgumentException(__METHOD__ . " : Action inattendue : " . $action);
        }

        return true;
    }

    /**
     * @param RapportActivite $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $this->rapportActivite = $entity;

        try {

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                    $this->assertEtatThese($this->rapportActivite->getThese());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_SIEN:
                    $this->assertAppartenanceThese($this->rapportActivite->getThese());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_SIEN:
                    $this->assertRapportEstDematerialise($this->rapportActivite);
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
                    $this->assertRapportEstNonDematerialise($this->rapportActivite);
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                    $this->assertModificationPossible($this->rapportActivite);
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    private function assertCreationPossible()
    {
        $these = $this->getRequestedThese();
        $rapportsTeleverses = $this->rapportActiviteService->findRapportsForThese($these);

        $this->rapportActiviteCreationRule->setRapportsExistants($rapportsTeleverses);
        $this->rapportActiviteCreationRule->execute();

        if (!$this->rapportActiviteCreationRule->isCreationPossible()) {
            throw new FailedAssertionException("La création n'est pas possible.");
        }
    }

    private function assertModificationPossible(RapportActivite $rapportActivite)
    {
        if ($rapportActivite->getRapportValidations()->count()) {
            // modif impossible si une validation existe
            throw new FailedAssertionException("La modification n'est plus possible car le rapport a été validé.");
        }
    }

    private function assertEtatThese(These $these)
    {
        $this->assertTrue(
            in_array($these->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]),
            "La thèse doit être en cours ou soutenue"
        );
    }

    private function assertAppartenanceThese(These $these)
    {
        if ($doctorant = $this->userContextService->getIdentityDoctorant()) {
            $this->assertTrue(
                $these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        }
        elseif ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        }
        elseif ($roleUniteRech = $this->userContextService->getSelectedRoleUniteRecherche()) {
            $this->assertTrue(
                $these->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'UR " . $roleUniteRech->getStructure()->getCode()
            );
        }
        elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        }
        elseif ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas codirigée par " . $individuUtilisateur
            );
        }
    }

    private function assertRapportEstDematerialise(RapportActivite $rapportActivite)
    {
        $this->assertTrue(
            $rapportActivite->getFichier() === null,
            "Ce rapport date de l'ancienne version du module car il a fait l'objet d'un téléversement de fichier."
        );
    }

    private function assertRapportEstNonDematerialise(RapportActivite $rapportActivite)
    {
        $this->assertTrue(
            $rapportActivite->getFichier() !== null,
            "Ce rapport doit être fourni de façon dématérialisée (càd via un formulaire et sans téléversement de fichier)."
        );
    }

    private function getRequestedThese(): ?These
    {
        if ($rapportActivite = $this->getRequestedRapport()) {
            return $rapportActivite->getThese();
        } elseif ($routeMatch = $this->getRouteMatch()) {
            return $routeMatch->getThese();
        } else {
            return null;
        }
    }

    private function getRequestedRapport(): ?RapportActivite
    {
        $rapportActivite = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('rapport')) {
            $rapportActivite = $this->rapportActiviteService->fetchRapportById($id);
        }

        return $rapportActivite;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}