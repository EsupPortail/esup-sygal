<?php

namespace Acteur\Assertion;

use Acteur\Entity\Db\AbstractActeur;
use Acteur\Service\AbstractActeurService;
use Application\Assertion\AbstractAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use HDR\Entity\Db\HDR;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;

abstract class AbstractActeurAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;

    protected AbstractActeurService $acteurService;
    protected ?AbstractActeur $acteur;
    protected These|HDR|null $entity;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    protected function getRequestedActeur(): ?AbstractActeur
    {
        if ($id = $this?->getRouteMatch()?->getParam('acteur')) {
            return $this->acteurService->getRepository()->find($id);
        }/*else if($id = $this?->getRouteMatch()?->getParam('co-encadrant')){
            return $this->coEncadrantService->getCoEncadrant($id);
        }*/

        return null;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }

    protected function assertAppartenance(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        if ($role->getCode() === Role::CODE_BDD || $role->getCode() === Role::CODE_GEST_HDR) {
            $structure = $role->getStructure();
            $this->assertTrue(
                $this->entity->getEtablissement()->getStructure() === $structure,
                $this->getEntityLibelle() . " n'appartient pas à la structure  " . $structure
            );
        } elseif ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
            $this->assertTrue(
                $this->entity->getEcoleDoctorale()->getStructure()->getId() === $roleEcoleDoctorale->getStructure()->getId(),
                $this->getEntityLibelle() . " n'est pas rattachée à l'ED " . $roleEcoleDoctorale->getStructure()->getCode()
            );
        } elseif ($roleUniteRech = $this->userContextService->getSelectedRoleUniteRecherche()) {
            $this->assertTrue(
                $this->entity->getUniteRecherche()->getStructure()->getId() === $roleUniteRech->getStructure()->getId(),
                $this->getEntityLibelle() . " n'est pas rattachée à l'UR " . $roleUniteRech->getStructure()->getCode()
            );
        }
    }

    protected function getEntityLibelle(): string
    {
        return $this->entity instanceof These ? "La thèse" : "la HDR";
    }
}