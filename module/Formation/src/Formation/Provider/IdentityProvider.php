<?php

namespace Formation\Provider;

use Application\Service\Role\RoleServiceAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenAuth\Entity\Db\RoleInterface;
use UnicaenAuth\Provider\Identity\ChainableProvider;
use UnicaenAuth\Provider\Identity\ChainEvent;
use UnicaenAuth\Service\Traits\UserContextServiceAwareTrait;

class IdentityProvider implements ProviderInterface, ChainableProvider
{
    use RoleServiceAwareTrait;
    use SessionServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function injectIdentityRoles(ChainEvent $event)
    {
        $event->addRoles($this->getIdentityRoles());
    }

    /**
     * @return string[]|RoleInterface[]
     */
    public function getIdentityRoles()
    {
        return $this->computeRolesFormations();
    }

    private function computeRolesFormations() : array
    {
        $roles = [];

        $user = $this->serviceUserContext->getDbUser();
        if ($user === null) return [];

        $individu = $user->getIndividu();
        if ($individu === null) return [];

        $sessions = $this->getSessionService()->getEntityManager()->getRepository(Session::class)->findSessionsByFormateur($individu);
        if (!empty($sessions)) {
            $formateur = $this->getRoleService()->getRepository()->findByCode(Formateur::ROLE);
            $roles[] = $formateur;
        }
        return $roles;
    }

}