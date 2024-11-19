<?php

namespace Formation\Assertion\Session;

use Application\Assertion\AbstractAssertion;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareInterface;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Privilege\SessionPrivileges;
use JetBrains\PhpStorm\Pure;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class SessionAssertion extends AbstractAssertion implements  AssertionInterface,  UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use DoctorantServiceAwareTrait;

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

        return true;
    }

    /** @var Session $entity */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        switch ($privilege) {
            case SessionPrivileges::SESSION_VOIR_LIEU :
                return $this->canVoirLieuSession($entity);
        }

        return true;
    }

    private function canVoirLieuSession(?Session $session): bool
    {
        if(!$session) return false;
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();
        if (!$role) {
            return false;
        }

        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $userDoctorant = $this->doctorantService->getRepository()->findOneByIndividu($individu);
                $inscritsPrincipale = $session->getInscriptionsByListe(Inscription::LISTE_PRINCIPALE);
                /** @var Inscription $inscription */
                $doctorantInListePrincipale = array_filter($inscritsPrincipale, fn($inscription) => $inscription->getDoctorant()->getIndividu() === $individu);
                return $session->estInscrit($userDoctorant) && $doctorantInListePrincipale;
        }
        return true;
    }
}