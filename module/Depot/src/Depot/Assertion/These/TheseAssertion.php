<?php

namespace Depot\Assertion\These;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Entity\Db\Role;
use Application\Service\UserContextService;
use Depot\Acl\WfEtapeResource;
use Depot\Entity\Db\WfEtape;
use Depot\Provider\Privilege\DepotPrivileges;
use Depot\Provider\Privilege\ValidationPrivileges;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class TheseAssertion extends AbstractAssertion
{
    private TheseEntityAssertion $theseEntityAssertion;
    private ?These $these = null;

    public function setTheseEntityAssertion(TheseEntityAssertion $theseEntityAssertion)
    {
        $this->theseEntityAssertion = $theseEntityAssertion;
    }

    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    private function assertPage(array $page): bool
    {
        if ($this->getRouteMatch() === null) {
            return false;
        }

        $this->these = $this->getRouteMatch()->getThese();

        $etape = $page['etape'] ?? null;
        if (!$etape) {
            return true;
        }

        if ($this->these && ! $this->getServiceAuthorize()->isAllowed(new WfEtapeResource($etape, $this->these))) {
            return false;
        }

        return true;
    }

    protected function assertEntity(ResourceInterface $these, $privilege = null): bool
    {
        if (! parent::assertEntity($these, $privilege)) {
            return false;
        }

        $this->theseEntityAssertion->setContext(['these' => $these]);
        try {
            $this->theseEntityAssertion->assert($privilege);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        /** @var These $these */

        switch (true) {
            case $privilege === DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE:
                return ! $this->isAllowed(new WfEtapeResource(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE, $these));
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $these));
            case $privilege === DepotPrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE:
                return $this->theseEntityAssertion->isStructureDuRoleRespectee();
            case $privilege === DepotPrivileges::THESE_CONSULTATION_CORREC_AUTORISEE_INFORMATIONS:
                if(!$this->theseEntityAssertion->isStructureDuRoleRespectee()) return false;
                $role = $this->userContextService->getSelectedIdentityRole();
                if (!$role) return false;
                if($these->estSoutenue() && $these->getCorrectionEffectuee() === "O"){
                    switch($role->getCode()){
                        case Role::CODE_DOCTORANT:
                        case Role::CODE_DIRECTEUR_THESE:
                        case Role::CODE_CODIRECTEUR_THESE:
                        case Role::CODE_RESP_UR:
                        case Role::CODE_GEST_UR:
                            return false;
                    }
                }
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        if ($this->getRouteMatch() === null) {
            return false;
        }

        $this->these = $this->getRouteMatch()->getThese();

        switch (true) {
            case $this->selectedRoleIsDoctorant():
                if (! $this->assertControllerAsDoctorant()) {
                    return false;
                }
        }

        if ($this->these === null) {
            return true;
        }

//        if (! $this->userContextService->isStructureDuRoleRespecteeForThese($this->these)) {
//            return false;
//        }

        switch (true) {
            case $privilege === ValidationPrivileges::THESE_VALIDATION_RDV_BU:
                return $this->isAllowed(new WfEtapeResource(WfEtape::CODE_RDV_BU_VALIDATION_BU, $this->these));
        }

        return $this->userContextService->isStructureDuRoleRespecteeForThese($this->these);
    }

    protected function assertControllerAsDoctorant(): bool
    {
        $identityDoctorant = $this->getIdentityDoctorant();

        if ($identityDoctorant === null) {
            throw new RuntimeException("Anomalie: le role doctorant est sélectionné mais aucune donnée d'identité doctorant n'est disponible");
        }

        if ($this->these === null) {
            return true;
        }

        return $this->these->getDoctorant()->getId() === $identityDoctorant->getId();
    }
}