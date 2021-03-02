<?php

namespace Application\Assertion\RapportAnnuel;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Entity\Db\RapportAnnuel;
use Application\Provider\Privilege\RapportAnnuelPrivileges;
use Application\Service\UserContextServiceAwareTrait;

class RapportAnnuelEntityAssertion implements EntityAssertionInterface
{
    use UserContextServiceAwareTrait;

    /**
     * @var RapportAnnuel
     */
    private $rapportAnnuel;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->rapportAnnuel = $context['rapportAnnuel'];
    }

    /**
     * @param string $privilege
     * @return boolean
     * @throws FailedAssertionException
     */
    public function assert($privilege = null)
    {
        switch (true) {
            case $privilege === RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELECHARGER:
            case $privilege === RapportAnnuelPrivileges::RAPPORT_ANNUEL_SUPPRIMER:
                if ($identityDoctorant = $this->userContextService->getIdentityDoctorant()) {
                    return $this->rapportAnnuel->getThese()->getDoctorant()->getId() === $identityDoctorant->getId();
                }
        }

        return true;
    }

}