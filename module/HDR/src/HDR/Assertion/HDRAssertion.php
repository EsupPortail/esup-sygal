<?php

namespace HDR\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Service\UserContextServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Provider\Privileges\HDRPrivileges;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

class HDRAssertion extends AbstractAssertion
{
    use UserContextServiceAwareTrait;
    use ThrowsFailedAssertionExceptionTrait;
    use HDRServiceAwareTrait;

    private ?HDR $hdr = null;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    private function assertPage(array $page): bool
    {
        return true;
    }

    protected function assertEntity(ResourceInterface $hdr, $privilege = null): bool
    {
        if (!parent::assertEntity($hdr, $privilege)) {
            return false;
        }

        switch (true) {
            case $privilege === HDRPrivileges::HDR_MODIFICATION_SES_HDRS || $privilege === HDRPrivileges::HDR_MODIFICATION_TOUTES_HDRS :
                if ($hdr->getEtatHDR() === HDR::ETAT_SOUTENUE || $hdr->getEtatHDR() === HDR::ETAT_ABANDONNEE) return false;
                return $this->userContextService->isStructureDuRoleRespecteeForHDR($this->hdr);
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (!parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $this->hdr = $this->getRouteMatch()->getHDR();

        if ($this->hdr === null) return true;

        if ($action === 'modifier') {
            if ($this->hdr->getEtatHDR() === HDR::ETAT_SOUTENUE) return false;
        }

        return $this->userContextService->isStructureDuRoleRespecteeForHDR($this->hdr);
    }
}