<?php

namespace Doctorant\Assertion\These;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Depot\Acl\WfEtapeResource;
use Doctorant\Entity\Db\Doctorant;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class TheseAssertion extends AbstractAssertion
{
    const DOCTORANT_CONTROLLER = 'Application\Controller\Doctorant';

    private TheseEntityAssertion $theseEntityAssertion;
    private ?Doctorant $doctorant = null;
    private ?These $these = null;

    public function setTheseEntityAssertion(TheseEntityAssertion $theseEntityAssertion)
    {
        $this->theseEntityAssertion = $theseEntityAssertion;
    }

    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    private function assertPage(array $page): bool
    {
        if ($this->getRouteMatch() === null) {
            return false;
        }

        $this->these = $this->getRouteMatch()->getThese();
        $this->doctorant = $this->getRouteMatch()->getDoctorant();

        $etape = $page['etape'] ?? null;
        if (!$etape) {
            return true;
        }

        if ($this->these && ! $this->getServiceAuthorize()->isAllowed(new WfEtapeResource($etape, $this->these))) {
            return false;
        }

        return true;
    }

    protected function assertEntity(ResourceInterface $these, $privilege = null): bool
    {
        if (! parent::assertEntity($these, $privilege)) {
            return false;
        }

        $this->theseEntityAssertion->setContext(['these' => $these]);
        try {
            $this->theseEntityAssertion->assert($privilege);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $this->these = $this->getRouteMatch()->getThese();
        $this->doctorant = $this->getRouteMatch()->getDoctorant();

        switch (true) {
            case $this->selectedRoleIsDoctorant():
                if (! $this->assertControllerAsDoctorant()) {
                    return false;
                }
        }

        return true;
    }

    protected function assertControllerAsDoctorant(): bool
    {
        $identityDoctorant = $this->getIdentityDoctorant();

        if ($identityDoctorant === null) {
            throw new RuntimeException("Anomalie: le role doctorant est sélectionné mais aucune donnée d'identité doctorant n'est disponible");
        }

        switch (true) {
            case $this->actionIs(self::DOCTORANT_CONTROLLER, 'modifier-email-contact'):
                return $this->doctorant && $this->doctorant->getId() === $identityDoctorant->getId();
        }

        if ($this->these === null) {
            return true;
        }

        return $this->these->getDoctorant()->getId() === $identityDoctorant->getId();
    }
}