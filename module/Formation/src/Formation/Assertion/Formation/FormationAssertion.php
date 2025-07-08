<?php

namespace Formation\Assertion\Formation;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Session;
use Formation\Provider\Privilege\FormationPrivileges;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class FormationAssertion extends AbstractAssertion
    implements AssertionInterface, UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use DoctorantServiceAwareTrait;

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
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (!parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        return true;
    }

    /** @var Session $entity */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        switch ($privilege) {
            case FormationPrivileges::FORMATION_EXPORTER_XLS :
                return $this->canVoirLieuSession($entity);
        }

        return true;
    }

    /**
     * Teste si la structure sur laquelle porte le profil courant de l'utilisateur est compatible avec la thèse spécifiée.
     *
     * @param object $entity
     * @return bool
     */
    public function isStructureDuRoleRespecteeForEntity(object $entity): bool
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($role->isTheseDependant()) {
            if ($role->isDoctorant()) {
                $utilisateurEstAuteurDeLaThese = $entity->getDoctorant()->getId() === $this->getIdentityDoctorant()->getId();
                return $utilisateurEstAuteurDeLaThese;
            }
            elseif ($role->isDirecteurThese()) {
                if ($individu = $this->getIdentityIndividu()) {
                    return $entity->hasActeurWithRole($individu, Role::CODE_DIRECTEUR_THESE);
                }
                return false;
            }
        }

        elseif ($role->isStructureDependant()) {
            if ($role->isEtablissementDependant()) {
                // On ne voit que les thèses de son établissement.
                return $entity->getEtablissement()->getStructure() === $role->getStructure();
            }
            elseif ($role->isEcoleDoctoraleDependant()) {
                // On ne voit que les thèses concernant son ED.
                return $entity->getEcoleDoctorale()->getStructure() === $role->getStructure();
            }
            elseif ($role->isUniteRechercheDependant()) {
                // On ne voit que les thèses concernant son UR.
                return $entity->getUniteRecherche()->getStructure() === $role->getStructure();
            }
        }

        return true;
    }
}