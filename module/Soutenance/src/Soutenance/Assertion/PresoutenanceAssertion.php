<?php

namespace Soutenance\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class PresoutenanceAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface {

    use UserContextServiceAwareTrait;
    use MessageCollectorAwareTrait;
    use TheseServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;

    private ?These $these = null;

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
     * @param These $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $this->these = $entity;

        try {

            switch ($privilege) {
                case PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU:
                case PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION:
                    $role = $this->userContextService->getSelectedIdentityRole();
                    return ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $this->these->getEtablissement()->getStructure());
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

        $this->these = $this->getRequestedThese();

        try {
            switch ($action) {
                case 'presoutenance':
                    if ($this->these !== null) {
                        $this->assertCanAccessInformationsPresoutenance($this->these);
                    }
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

    private function assertCanAccessInformationsPresoutenance(These $these){
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();

        if (!$role) {
            return;
        }

        if ($role->isDoctorant()) {
            $doctorant = $this->userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $these->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        }
        if ($roleMaisonDoctorat = $this->userContextService->getSelectedRoleBDD()) {
            $this->assertTrue(
                $these->getEtablissement()->getStructure()->getId() === $roleMaisonDoctorat->getStructure()->getId(),
                "La thèse n'appartient pas à la maison du doctorat " . $roleMaisonDoctorat->getStructure()->getId()
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
        } elseif ($role->isDirecteurThese()) {
            $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
            $individus = [];
            foreach ($directeurs as $directeur) $individus[] = $directeur->getIndividu();
            $this->assertTrue(array_search($individu, $individus) !== false,
                $individu." n'est pas le directeur de cette thèse");
        } elseif ($role->getCode() === Role::CODE_CODIRECTEUR_THESE) {
            $directeurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
            $individus = [];
            foreach ($directeurs as $directeur) $individus[] = $directeur->getIndividu();
            $this->assertTrue(array_search($individu, $individus) !== false,
                $individu." n'est pas le co-directeur de cette thèse");
        }
    }

    protected function getRequestedThese(): ?These
    {
        $these = null;
        if (($routeMatch = $this->getRouteMatch()) && $id = $routeMatch->getParam('these')) {
            $these = $this->theseService->getRepository()->find($id);
        }

        return $these;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}