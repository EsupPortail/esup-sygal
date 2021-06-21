<?php

namespace Application\Entity\Db;

use UnicaenAuthToken\Entity\Db\AbstractUserToken;

/**
 * Class UtilisateurToken
 *
 * @method Utilisateur getUser()UnicaenAuthToken/Controller/TokenController.php 77
 * @method self setUser(Utilisateur $user)
 */
class UtilisateurToken extends AbstractUserToken
{
    const RESOURCE_ID = AbstractUserToken::RESOURCE_ID; // ne pas modifier !!

    /**
     * @var \Application\Entity\Db\Utilisateur
     */
    protected $user;
}