<?php

namespace Application\Assertion\RapportActivite;

use Application\Assertion\ControllerAssertion;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;

class RapportActiviteControllerAssertion extends ControllerAssertion
{
    use UserContextServiceAwareTrait;

    /**
     * @var Rapport
     */
    private $rapport;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->rapport = $context['rapport'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function assert($privilege = null)
    {
        if ($this->rapport->getThese() === null) {
            return true;
        }

        // Cas particulier de l'utilisateur Doctorant
        if ($identityDoctorant = $this->userContextService->getIdentityDoctorant()) {
            if (! $this->assertForDoctorant($identityDoctorant, $privilege)) {
                return false;
            }
        }

        return in_array($this->rapport->getThese()->getEtatThese(), [
            These::ETAT_EN_COURS,
            These::ETAT_SOUTENUE,
        ]);
    }

    public function assertForDoctorant(Doctorant $utilisateur, $privilege = null)
    {
        return $this->rapport->getThese()->getDoctorant()->getId() === $utilisateur->getId();
    }
}