<?php

namespace Acteur\Assertion\ActeurHDR;

use Acteur\Assertion\AbstractActeurAssertion;
use Acteur\Entity\Db\ActeurHDR;
use Acteur\Provider\Privilege\ActeurPrivileges;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Entity\Db\Role;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * @property \Acteur\Entity\Db\ActeurHDR $acteur
 * @property \HDR\Entity\Db\HDR $entity
 */
class ActeurHDRAssertion extends AbstractActeurAssertion
{
    use HDRServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;

    protected function assertPage(array $page): bool
    {
        $this->entity = $this->getRequestedHDR();
        if ($this->entity === null) {
            return true;
        }

        try {
            $this->assertAppartenanceHDR($this->acteur->getHDR(), $this->userContextService);
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
        $this->entity = $this->acteur?->getId() !== null ? $this->acteur->getHDR() : $this->getRequestedHDR();

        try {
            switch ($action) {
                case 'modifier':
                    $this->assertEtatHDR($this->entity);
                    $this->assertAppartenanceHDR($this->entity, $this->userContextService);
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
     * @param ActeurHDR $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $this->acteur = $entity;
        $this->entity = $this->acteur->getId() !== null ? $this->acteur->getHDR() : $this->getRequestedHDR();

        try {
            switch ($privilege) {
                case ActeurPrivileges::MODIFIER_ACTEUR_SES_THESES:
                case ActeurPrivileges::MODIFIER_ACTEUR_TOUTES_THESES:
                    if($this->entity){
                        $this->assertEtatHDR($this->entity);
                        $this->assertAppartenanceHDR($this->entity, $this->userContextService);
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

    private function assertEtatHDR(HDR $hdr): void
    {
        $this->assertTrue($hdr->getEtatHDR() == HDR::ETAT_EN_COURS, "la HDR doit être en cours");
    }

    private function getRequestedHDR(): ?HDR
    {
        if ($acteur = $this->getRequestedActeur()) {
            /** @var \Acteur\Entity\Db\ActeurHDR $acteur */
            return $acteur->getHDR();
        } elseif ($routeMatch = $this->getRouteMatch()) {
            return $routeMatch->getHDR();
        } else {
            return null;
        }
    }

    protected function assertAppartenanceHDR(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return;
        }

        parent::assertAppartenance();

        if ($role->isCandidatHDR()) {
            $candidat = $this->userContextService->getIdentityCandidatHDR();
            $this->assertTrue(
                $this->entity->getCandidat()->getId() === $candidat->getId(),
                "la HDR n'appartient pas au candidat " . $candidat
            );
        } elseif ($this->userContextService->getSelectedRoleGarantHDR()) {
            $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
            $this->assertTrue(
                $this->entity->hasActeurWithRole($individuUtilisateur, Role::CODE_HDR_GARANT),
                "la HDR n'est pas dirigée par " . $individuUtilisateur
            );
        }
    }
}