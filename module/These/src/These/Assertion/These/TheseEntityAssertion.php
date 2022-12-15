<?php

namespace These\Assertion\These;

use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Service\UserContextServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareInterface;
use These\Service\These\TheseServiceAwareTrait;

class TheseEntityAssertion extends GeneratedTheseEntityAssertion
    implements TheseServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use TheseServiceAwareTrait;

    private ?These $these = null;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->these = $context['these'];
    }

    /**
     * @param string|null $privilege
     * @return boolean
     */
    public function assert(?string $privilege = null): bool
    {
        $allowed = $this->assertAsBoolean($privilege);

        $this->assertTrue($allowed, $this->failureMessage);

        return true;
    }

    protected function isStructureDuRoleRespectee(): bool
    {
        return $this->userContextService->isStructureDuRoleRespecteeForThese($this->these);
    }

    protected function isUtilisateurEstAuteurDeLaThese(): bool
    {
        if ($this->getIdentityDoctorant() === null) return false;
        return $this->these->getDoctorant()->getId() === $this->getIdentityDoctorant()->getId();
    }

    /**
     * @return bool
     */
    protected function isTheseEnCours(): bool
    {
        return $this->these->getEtatThese() === These::ETAT_EN_COURS;
    }

    protected function isTheseSoutenue(): bool
    {
        return $this->these->estSoutenue();
    }

    /**
     * @return bool
     */
    protected function isRoleDoctorantSelected(): bool
    {
        return (bool) $this->userContextService->getSelectedRoleDoctorant();
    }

    protected ?Doctorant $identityDoctorant = null;

    /**
     * @return Doctorant
     */
    private function getIdentityDoctorant(): ?Doctorant
    {
        if (null === $this->identityDoctorant) {
            $this->identityDoctorant = $this->userContextService->getIdentityDoctorant();
        }

        return $this->identityDoctorant;
    }
}