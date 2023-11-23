<?php

namespace Doctorant\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\Entity\Db\Role;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class DoctorantAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use TheseServiceAwareTrait;
    use DoctorantServiceAwareTrait;

    public function __invoke(array $page): bool
    {
        return $this->assertPage($page);
    }

    private function assertPage(array $page): bool
    {
        return true;
    }

    protected function assertController($controller, $action = null, $privilege = null): bool
    {
        if (!parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        $doctorant = $this->getRequestedDoctorant();

        try {
            switch ($action) {
                case 'lister':
                case 'rechercher':
                    return true;

                case 'voir':
                case 'consulter':
                    if (!$this->assertEntity($doctorant, DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN)) {
                        return false;
                    }

                    return true;
            }
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }

        return true;
    }

    /**
     * @param \Doctorant\Entity\Db\Doctorant $entity
     * @param string $privilege
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null): bool
    {
        if (!parent::assertEntity($entity, $privilege)) {
            return false;
        }

        try {
            switch ($privilege) {
                case DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN:
                    $role = $this->userContextService->getSelectedIdentityRole();

                    if ($role->isDoctorant()) {
                        return $this->userContextService->getIdentityDoctorant() === $entity;
                    }

                    $theses = $this->theseService->getRepository()->findThesesByDoctorant($entity);

                    // todo : cas des acteurs de thèse

                    if ($role->isEtablissementDependant()) {
                        return !empty(
                            array_filter($theses, fn(These $t) => $t->getEtablissement() === $role->getStructure()->getEtablissement())
                        );
                    }
                    if ($role->isEcoleDoctoraleDependant()) {
                        return !empty(
                            array_filter($theses, fn(These $t) => $t->getEcoleDoctorale() === $role->getStructure()->getEcoleDoctorale())
                        );
                    }
                    if ($role->isUniteRechercheDependant()) {
                        return !empty(
                            array_filter($theses, fn(These $t) => $t->getUniteRecherche() === $role->getStructure()->getUniteRecherche())
                        );
                    }

                    return true;

                default:
                    return false;
            }

        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }
    }

    private function getRequestedDoctorant(): ?Doctorant
    {
        if ($routeMatch = $this->getRouteMatch()) {
            return $routeMatch->getDoctorant();
        }

        return null;
    }

    protected function getRouteMatch(): ?RouteMatch
    {
        /** @var \Application\RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();
        return $rm;
    }
}