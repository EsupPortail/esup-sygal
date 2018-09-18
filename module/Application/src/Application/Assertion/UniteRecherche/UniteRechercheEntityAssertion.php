<?php

namespace Application\Assertion\UniteRecherche;

use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Entity\Db\UniteRecherche;
use Application\Provider\Privilege\UniteRecherchePrivileges;
use Application\Service\UserContextServiceAwareTrait;

class UniteRechercheEntityAssertion implements EntityAssertionInterface
{
    use UserContextServiceAwareTrait;

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->uniteRecherche = $context['uniteRecherche'];
    }

    /**
     * @param string $privilege
     *
     * @return boolean
     */
    public function assert($privilege = null)
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        switch ($privilege) {
            case UniteRecherchePrivileges::UNITE_RECH_MODIFICATION:
                if ($role->isStructureDependant()) {
                    if ($role->isUniteRechercheDependant()) {
                        return $this->uniteRecherche->getStructure() === $role->getStructure();
                    }
                }
                break;
            default:
                break;
        }

        return true;
    }
}