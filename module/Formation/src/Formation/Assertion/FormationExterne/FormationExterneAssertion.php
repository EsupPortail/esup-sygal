<?php

namespace Formation\Assertion\FormationExterne;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\FormationExterne;
use Formation\Provider\Privilege\FormationPrivileges;
use Formation\Service\Formation\FormationExterne\FormationExterneServiceAwareTrait;
use JetBrains\PhpStorm\Pure;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class FormationExterneAssertion extends AbstractAssertion implements  AssertionInterface,  UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use DoctorantServiceAwareTrait;
    use FormationExterneServiceAwareTrait;

    /**
     * @param array $page
     * @return bool
     */
    #[Pure] public function __invoke(array $page): bool
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

        $formationExterne = null;
        $formationExterneId = (($this->getMvcEvent()->getRouteMatch()->getParam('formationExterne')));
        if($formationExterneId) $formationExterne = $this->formationExterneService->getRepository()->find($formationExterneId);

        switch($action) {
            case 'modifier' :
            case 'historiser' :
            case 'restaurer' :
            case 'supprimer' :
            case 'supprimer-attestation' :
            case 'telecharger-attestation' :
            case 'valider-formation-externe' :
            case 'refuser-formation-externe' :
            case 'revoquer-formation-externe' :
                if ($formationExterne) {
                    return $this->scopeValide($formationExterne);
                }
        }
        return true;
    }

    /** @var FormationExterne $entity */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $formationExterne = $entity;

        switch($privilege) {
            case FormationPrivileges::FORMATION_FORMATIONEXTERNE_MODIFIER :
            case FormationPrivileges::FORMATION_FORMATIONEXTERNE_SUPPRIMER :
            case FormationPrivileges::FORMATION_FORMATIONEXTERNE_HISTORISER :
            case FormationPrivileges::FORMATION_FORMATIONEXTERNE_TELECHARGER_ATTESTATION :
            case FormationPrivileges::FORMATION_FORMATIONEXTERNE_GERER_VALIDATION :
                return $this->scopeValide($formationExterne);
        }

        return true;
    }
    public function scopeValide(?FormationExterne $formationExterne): bool
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return false;
        }

        $doctorant = $formationExterne->getDoctorant();
        $theses = array_filter($doctorant->getTheses(), function (These $t) { return (in_array($t->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]) AND $t->estNonHistorise());});
        /** @var These $these */
        $these = (!empty($theses))?current($theses):null;
        return $this->userContextService->isStructureDuRoleRespecteeForThese($these);
    }
}