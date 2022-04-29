<?php

namespace RapportActivite\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class RapportActiviteAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use UserContextServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;

    private ?RapportActivite $rapportActivite = null;

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
        $these = $this->getRouteMatch()->getThese();
        if ($these === null) {
            return true;
        }

        try {
//            $this->assertEtatThese($these);
            $this->assertAppartenanceThese($these);
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

        if ($id = $this->getRouteMatch()->getParam('rapport')) {
            try {
                $rapportActivite = $this->rapportActiviteService->findRapportById($id);
                $these = $rapportActivite->getThese();
            } catch (NoResultException $e) {
                return false;
            }
        } else {
            $these = $this->getRouteMatch()->getThese();
        }

        if ($these === null) {
            return true;
        }

        try {

            $this->assertAppartenanceThese($these);

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
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
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertEtatThese($this->rapportActivite->getThese());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertAppartenanceThese($this->rapportActivite->getThese());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertAucuneValidation();
            }

//            switch ($privilege) {
//                case RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
//                case RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
//                    $this->assertAppartenanceThese($this->rapportActivite->getThese());
//            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
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
        // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEcoleDoctorale() :
        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $these->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                "La thèse n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        }
        if ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE) ||
                $these->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        }
    }

    private function assertAucuneValidation()
    {
        $this->assertTrue(
            $this->rapportActivite->getRapportValidation() === null,
            "Le rapport ne doit pas avoir été validé"
        );
    }


    protected function getRouteMatch(): RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        return $this->getMvcEvent()->getRouteMatch();
    }
}