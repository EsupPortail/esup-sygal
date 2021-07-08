<?php

namespace Application\Entity\Db;

use UnicaenAuthToken\Entity\Db\AbstractUserToken;

/**
 * Class UtilisateurToken
 *
 * @method Utilisateur getUser()
 * @method self setUser(Utilisateur $user)
 */
class UtilisateurToken extends AbstractUserToken
{
    const RESOURCE_ID = AbstractUserToken::RESOURCE_ID; // ne pas modifier !!

    /**
     * @var \Application\Entity\Db\Utilisateur
     */
    protected $user;

    /**
     * Proxy pour {@see getUser()}.
     *
     * @return \Application\Entity\Db\Utilisateur|null
     */
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->getUser();
    }
}