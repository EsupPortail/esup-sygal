<?php

namespace Formation\Assertion\Justificatif;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Justificatif;
use Formation\Provider\Privilege\JustificatifPrivileges;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Justificatif\JustificatifServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use JetBrains\PhpStorm\Pure;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class JustificatifAssertion extends AbstractAssertion implements  AssertionInterface,  UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use DoctorantServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use SessionServiceAwareTrait;
    use InscriptionServiceAwareTrait;

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

        $inscription = null;
        $inscriptionId = (($this->getMvcEvent()->getRouteMatch()->getParam('inscription')));
        if($inscriptionId){
            /** @var Inscription $inscription */
            $inscription = $this->inscriptionService->getRepository()->find($inscriptionId);
        }else{
            $justificatifId = (($this->getMvcEvent()->getRouteMatch()->getParam('justificatif')));
            /** @var Justificatif $justificatif */
            $justificatif = $this->justificatifService->getRepository()->find($justificatifId);
            if($justificatif) $inscription = $justificatif->getInscription();
        }

        if($inscription === null) return false;

        switch($action) {
            case 'justifier-absence' :
            case 'ajouter-justificatif-absence-seance':
            case 'supprimer-justificatif-absence-seance':
            case 'telecharger-justificatif-absence-seance':
                return $this->scopeValide($inscription);
        }
        return true;
    }

    /** @var Justificatif $entity */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        $justificatif = $entity;
        $inscription = $justificatif->getInscription();

        switch($privilege) {
            case JustificatifPrivileges::INSCRIPTION_ACCEDER_JUSTIFICATIF_ABSENCE :
            case JustificatifPrivileges::INSCRIPTION_GERER_JUSTIFICATIF_ABSENCE :
                //si aucun justificatif n'est enregistré en BDD
                if($justificatif->getInscription() === null) return true;
                return $this->scopeValide($inscription);
        }

        return true;
    }

    public function scopeValide(?Inscription $inscription): bool
    {
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return false;
        }

        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $doctorant = $inscription->getDoctorant();
                $userDoctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
                return $doctorant === $userDoctorant;
            case Role::CODE_GEST_ED :
                return $inscription->getSession()->getFormation()->getResponsable() === $individu;
            case Role::CODE_BDD :
                $session = $this->sessionService->getRepository()->findOneBy(["id" => $inscription->getSession()->getId()]);
                return $session && $session->getSite()->getCode() === $role->getStructure()->getCode();
        }
        return true;
    }
}