<?php

namespace These\Assertion\These;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Entity\Db\Role;
use Depot\Acl\WfEtapeResource;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenApp\Exception\RuntimeException;

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

        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();
        switch (true) {
            case $privilege === ThesePrivileges::THESE_CONSULTATION_SES_THESES:
            case $privilege === ThesePrivileges::THESE_MODIFICATION_SES_THESES:
                $this->canGererThese($role, $individu, $these);
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {if (! parent::assertController($controller, $action, $privilege)) {
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

        if ($action == 'modifier') {
            if ($this->these->getEtatThese() !== These::ETAT_EN_COURS) {
                return false;
            }
        }

        switch ($action) {
            case 'generalites':
            case 'structures':
            case 'direction':
            case 'financements':
                if($this->these->getSource()?->getImportable()) return false;
                break;
        }

//        if (! $this->userContextService->isStructureDuRoleRespecteeForThese($this->these)) {
//            return false;
//        }
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();
        return $this->canGererThese($role, $individu, $this->these);
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

    protected function canGererThese(Role $role, Individu $individu, These $these){
        // doctorant
        if ($role->getCode() === Role::CODE_DOCTORANT) return $these->getDoctorant()->getIndividu() === $individu;
        // directeur
        if ($role->getCode() === Role::CODE_DIRECTEUR_THESE) {
            $directeurs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
            $individus = [];
            foreach ($directeurs as $directeur) $individus[] = $directeur->getIndividu();
            return (array_search($individu, $individus) !== false);
        }
        if ($role->getCode() === Role::CODE_CODIRECTEUR_THESE) {
            $directeurs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
            $individus = [];
            foreach ($directeurs as $directeur) $individus[] = $directeur->getIndividu();
            return (array_search($individu, $individus) !== false);
        }
        // structure
        // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEcoleDoctorale() :
        if (in_array($role->getCode(), [Role::CODE_RESP_ED, Role::CODE_GEST_ED])) {
            return $these->getEcoleDoctorale()->getStructure() === $role->getStructure();
        }
        // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isUniteRecherche() :
        if (in_array($role->getCode(), [Role::CODE_RESP_UR, Role::CODE_GEST_UR])) {
            return $these->getUniteRecherche()->getStructure() === $role->getStructure();
        }
        // todo : remplacer par $role->isStructureDependant() && $role->getTypeStructureDependant()->isEtablissement() :
        if ($role->getCode() === Role::CODE_ADMIN || $role->getCode() === Role::CODE_BDD || $role->getCode() === Role::CODE_BU)
            return $these->getEtablissement()->getStructure() === $role->getStructure();

        return true;
    }
}