<?php

namespace Formation\Provider;

use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenUtilisateur\Entity\Db\RoleInterface;
use UnicaenAuthentification\Provider\Identity\ChainableProvider;
use UnicaenAuthentification\Provider\Identity\ChainEvent;
use UnicaenAuthentification\Service\Traits\UserContextServiceAwareTrait;

class IdentityProvider implements ProviderInterface, ChainableProvider
{
    use ApplicationRoleServiceAwareTrait;
    use SessionServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function injectIdentityRoles(ChainEvent $e)
    {
        $e->addRoles($this->getIdentityRoles());
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
            $formateur = $this->getApplicationRoleService()->getRepository()->findByCode(Formateur::ROLE);
            $roles[] = $formateur;
        }
        return $roles;
    }

}