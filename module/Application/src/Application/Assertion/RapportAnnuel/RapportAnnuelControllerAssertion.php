<?php

namespace Application\Assertion\RapportAnnuel;

use Application\Assertion\ControllerAssertion;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\RapportAnnuel;
use Application\Entity\Db\These;
use Application\Provider\Privilege\RapportAnnuelPrivileges;
use Application\Service\UserContextServiceAwareTrait;

class RapportAnnuelControllerAssertion extends ControllerAssertion
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
     * @inheritDoc
     */
    public function assert($privilege = null)
    {
        // Cas particulier de l'utilisateur Doctorant
        if ($identityDoctorant = $this->userContextService->getIdentityDoctorant()) {
            if (! $this->assertForDoctorant($identityDoctorant, $privilege)) {
                return false;
            }
        }

        if ($privilege === RapportAnnuelPrivileges::RAPPORT_ANNUEL_CONSULTER) {
            return in_array($this->rapportAnnuel->getThese()->getEtatThese(), [
                These::ETAT_EN_COURS,
                These::ETAT_SOUTENUE,
            ]);
        }

        return true;
    }

    public function assertForDoctorant(Doctorant $utilisateur, $privilege = null)
    {
        return $this->rapportAnnuel->getThese()->getDoctorant()->getId() === $utilisateur->getId();
    }
}