<?php

namespace RapportActivite\Assertion\Validation;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Rule\Validation\RapportActiviteValidationRuleAwareTrait;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;

class RapportActiviteValidationAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use MessageCollectorAwareTrait;

    use UserContextServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;

    use RapportActiviteValidationRuleAwareTrait;

    private ?RapportActivite $rapportActivite = null;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    /**
     * @param array $page
     * @return bool
     */
    private function assertPage(array $page): bool
    {



        return true;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null): bool
    {





        return true;
    }

    /**
     * @param RapportActivite $entity
     * @param string $privilege
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        $this->rapportActivite = $entity;

        try {

            switch ($privilege) {
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN:
                case RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT:
                    $this->assertValidationPossible();
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    private function assertValidationPossible()
    {
        $this->rapportActiviteValidationRule
            ->setRapportActivite($this->rapportActivite)
            ->execute();

        $this->assertTrue(
            $this->rapportActiviteValidationRule->isValidationPossible(),
            "La valeur de l'avis précédent ne permet pas de poursuivre"
        );
    }





    protected function getRouteMatch(): RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}