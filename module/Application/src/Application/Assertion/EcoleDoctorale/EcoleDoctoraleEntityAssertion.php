<?php

namespace Application\Assertion\EcoleDoctorale;

use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Entity\Db\EcoleDoctorale;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Service\UserContextServiceAwareTrait;

class EcoleDoctoraleEntityAssertion implements EntityAssertionInterface
{
    use UserContextServiceAwareTrait;

    /**
     * @var EcoleDoctorale
     */
    private $ecoleDoctorale;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->ecoleDoctorale = $context['ecoleDoctorale'];
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
            case EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION:
                if ($role->isStructureDependant()) {
                    if ($role->isEcoleDoctoraleDependant()) {
                        return $this->ecoleDoctorale->getStructure() === $role->getStructure();
                    }
                }
                break;
            default:
                break;
        }

        return true;
    }
}