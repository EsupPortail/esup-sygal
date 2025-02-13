<?php

namespace Acteur\Assertion\ActeurThese;

use Acteur\Assertion\AbstractActeurAssertion;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Provider\Privilege\ActeurPrivileges;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Entity\Db\Role;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;

/**
 * @property \Acteur\Entity\Db\ActeurThese $acteur
 * @property \These\Entity\Db\These $entity
 */
class ActeurTheseAssertion extends AbstractActeurAssertion
{
    use TheseServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;

    protected function assertPage(array $page): bool
    {
        $this->entity = $this->getRequestedThese();
        if ($this->entity === null) {
            return true;
        }

        try {
            $this->assertAppartenanceThese($this->acteur->getThese(), $this->userContextService);
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

        $this->acteur = $this->getRequestedActeur();
        $this->entity = $this->acteur?->getId() !== null ? $this->acteur->getThese() : $this->getRequestedThese();

        try {
            switch ($action) {
                case 'modifier':
                    $this->assertEtatThese($this->entity);
                    $this->assertAppartenanceThese($this->entity, $this->userContextService);
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
     * @param ActeurThese $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $this->acteur = $entity;
        $this->entity = $this->acteur->getId() !== null ? $this->acteur->getThese() : $this->getRequestedThese();

        try {
            switch ($privilege) {
                case ActeurPrivileges::MODIFIER_ACTEUR_SES_THESES:
                case ActeurPrivileges::MODIFIER_ACTEUR_TOUTES_THESES:
                    if($this->entity){
                        $this->assertEtatThese($this->entity);
                        $this->assertAppartenanceThese($this->entity, $this->userContextService);
                    }
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    private function assertEtatThese(These $these): void
    {
        $this->assertTrue($these->getEtatThese() == These::ETAT_EN_COURS, "La thèse doit être en cours");
    }

    private function getRequestedThese(): ?These
    {
        if ($acteur = $this->getRequestedActeur()) {
            /** @var \Acteur\Entity\Db\ActeurThese $acteur */
            return $acteur->getThese();
        } elseif ($routeMatch = $this->getRouteMatch()) {
            return $routeMatch->getThese();
        } else {
            return null;
        }
    }

    protected function assertAppartenanceThese(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        parent::assertAppartenance();

        if ($role->isDoctorant()) {
            $doctorant = $this->userContextService->getIdentityDoctorant();
            $this->assertTrue(
                $this->entity->getDoctorant()->getId() === $doctorant->getId(),
                "La thèse n'appartient pas au doctorant " . $doctorant
            );
        } elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $this->entity->hasActeurWithRole($individuUtilisateur, Role::CODE_DIRECTEUR_THESE),
                "La thèse n'est pas dirigée par " . $individuUtilisateur
            );
        } elseif ($this->userContextService->getSelectedRoleCodirecteurThese()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $this->entity->hasActeurWithRole($individuUtilisateur, Role::CODE_CODIRECTEUR_THESE),
                "La thèse n'est pas codirigée par " . $individuUtilisateur
            );
        }
    }
}