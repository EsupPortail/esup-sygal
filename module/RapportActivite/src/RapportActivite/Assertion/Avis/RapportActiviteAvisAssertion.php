<?php

namespace RapportActivite\Assertion\Avis;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NoResultException;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use UnicaenAvis\Entity\Db\AvisType;

class RapportActiviteAvisAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use UserContextServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;

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

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
//        $these = $this->getRouteMatch()->getThese();

        $rapportActivite = null;
        $rapportActiviteAvis = null;

        switch ($action) {
            case 'ajouter':
                $id = $this->getRouteMatch()->getParam('rapport');
                    $rapportActivite = $this->rapportActiviteService->findRapportById($id);
                    if ($rapportActivite === null) {
                        return false;
                    }
                    $these = $rapportActivite->getThese();
                break;

            case 'modifier':
            case 'supprimer':
                $id = $this->getRouteMatch()->getParam('rapportAvis');
                try {
                    $rapportActiviteAvis = $this->rapportActiviteAvisService->findRapportAvisById($id);
                    $these = $rapportActiviteAvis->getRapportActivite()->getThese();
                } catch (NoResultException $e) {
                    return false;
                }
                break;

            default:
                throw new InvalidArgumentException("Action inattendue : " . $action);
        }

        try {

            switch ($action) {
                case 'ajouter':
                case 'modifier':
                case 'supprimer':
                    $this->assertEtatThese($these);
                    $this->assertAppartenanceThese($these);
            }

            switch ($action) {
                case 'ajouter':
                    $avisTypeDispo = $this->assertExistsAvisTypeDisponible($rapportActivite);
                    $this->assertAvisTypeMatchesRole($avisTypeDispo);
                    $this->assertMostRecentAvisValeurCompatible($rapportActivite);
                    break;
            }

            switch ($action) {
                case 'modifier':
                    $this->assertNextAvisValeurCompatible($rapportActiviteAvis);
                    $this->assertAvisTypeMatchesRole($rapportActiviteAvis->getAvis()->getAvisType());
                    return true;
            }

            switch ($action) {
                case 'modifier':
                case 'supprimer':
                    $avisTypeDernier = $this->assertIsMostRecentAvisType($rapportActiviteAvis);
                    $this->assertAvisTypeMatchesRole($avisTypeDernier);
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

    /**
     * @param RapportActiviteAvis $entity
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        try {

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertEtatThese($entity->getRapportActivite()->getThese());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertAppartenanceThese($entity->getRapportActivite()->getThese());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $this->assertAucuneValidation($entity->getRapportActivite());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN:
                    $avisTypeDispo = $this->assertExistsAvisTypeDisponible($entity->getRapportActivite());
                    $this->assertAvisTypeMatchesRole($avisTypeDispo);
                    $this->assertMostRecentAvisValeurCompatible($entity->getRapportActivite());
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                    $this->assertNextAvisValeurCompatible($entity);
                    $this->assertAvisTypeMatchesRole($entity->getAvis()->getAvisType());
                    return true;
            }

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN:
                    $avisType = $this->assertIsMostRecentAvisType($entity);
                    $this->assertAvisTypeMatchesRole($avisType);
            }

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

    private function assertExistsAvisTypeDisponible(RapportActivite $rapportActivite): AvisType
    {
        $avisTypeDisponible = $this->getAvisTypeDisponible($rapportActivite);

        $this->assertTrue(
            $avisTypeDisponible !== null,
            "Aucun type d'avis n'est attendu pour ce rapport"
        );

        return $avisTypeDisponible;
    }

    private function getAvisTypeDisponible(RapportActivite $rapportActivite): ?AvisType
    {
        if ($rapportAvisPossible = $rapportActivite->getRapportAvisPossible()) {
            // Le type d'avis attendu/possible peut être spécifié via le RapportActivite (pattern DAO).
            return $rapportAvisPossible->getAvis()->getAvisType();
        } else {
            // Mais dans le cas contraire, faut voir...
            return $this->rapportActiviteAvisService->findExpectedAvisTypeForRapport($rapportActivite);
        }
    }

    private function assertAvisTypeMatchesRole(AvisType $avisType)
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($role->isEcoleDoctoraleDependant()) {
            switch ($avisType->getCode()) {
                case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST:
                    $expectedCodes = [Role::CODE_GEST_ED, Role::CODE_RESP_ED];
                    break;
                case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR:
                    $expectedCodes = [Role::CODE_RESP_ED];
                    break;
                default:
                    throw new InvalidArgumentException("Type d'avis possible inattendu : " . $avisType->getCode());
            }
            $this->assertTrue(
                in_array($role->getCode(), $expectedCodes),
                "Le type d'avis attendu pour ce rapport concerne un rôle utilisateur différent"
            );
        }
    }

    private function assertNextAvisValeurCompatible(RapportActiviteAvis $rapportActiviteAvis)
    {
        // On peut modifier un avis à condition que l'avis d'après est "rapport incomplet".

        $nextRapportAvis = $this->rapportActiviteAvisService->findRapportAvisAfter($rapportActiviteAvis);
        if ($nextRapportAvis === null) {
            return;
        }

        $avisValeur = RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET;

        $this->assertTrue(
            $nextRapportAvis->getAvis()->getAvisValeur()->getCode() === $avisValeur,
            "La valeur de l'avis fourni ensuite ne permet pas de modifier cet avis"
        );
    }

    private function assertIsMostRecentAvisType(RapportActiviteAvis $rapportActiviteAvis)
    {
        $dernierAvisType =
            $this->rapportActiviteAvisService->findMostRecentAvisTypeForRapport($rapportActiviteAvis->getRapportActivite());

        $this->assertTrue(
            $dernierAvisType === $rapportActiviteAvis->getAvis()->getAvisType(),
            "Il ne s'agit pas du dernier type d'avis attendu"
        );

        return $dernierAvisType;
    }

    private function assertMostRecentAvisValeurCompatible(RapportActivite $rapportActivite)
    {
        $dernierAvis = $this->rapportActiviteAvisService->findMostRecentRapportAvisForRapport($rapportActivite);
        if ($dernierAvis === null) {
            return;
        }

        $this->assertTrue(
            $dernierAvis->getAvis()->getAvisValeur()->getValeurBool() === true,
            "La valeur de l'avis précédent ne permet pas de poursuivre"
        );
    }

    private function assertAucuneValidation(RapportActivite $rapportActivite)
    {
        $this->assertTrue(
            $rapportActivite->getRapportValidation() === null,
            "Le rapport ne doit pas avoir été validé"
        );
    }


    protected function getRouteMatch(): RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        return $this->getMvcEvent()->getRouteMatch();
    }
}