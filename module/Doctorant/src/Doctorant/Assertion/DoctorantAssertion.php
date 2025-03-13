<?php

namespace Doctorant\Assertion;

use Application\Assertion\AbstractAssertion;
use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\ThrowsFailedAssertionExceptionTrait;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;

class DoctorantAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use ThrowsFailedAssertionExceptionTrait;
    use TheseServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use ActeurServiceAwareTrait;

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

        if ($privilege === DoctorantPrivileges::DOCTORANT_CONSULTER_TOUS) {
            return true;
        }

        $entity = $this->getRequestedDoctorant();

        try {
            switch ($action) {
                case 'lister':
                case 'rechercher':
                    return true;

                case 'voir':
                case 'consulter':
                    if (!$this->assertEntity($entity, DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN)) {
                        return false;
                    }
                    return true;

                case 'modifier-email-contact':
                case 'modifier-email-contact-consent':
                    if (! $this->isUtilisateurEgaleCeDoctorant($entity)) {
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

                    if ($role->isTheseDependant()) {
                        return in_array(true, array_map(
                            fn(These $these) => !empty($this->acteurService->getRepository()->findActeursByTheseAndRole($these, $role)),
                            $theses
                        ));
                    }

                    if ($role->isEtablissementDependant()) {
                        return
                            $entity->getEtablissement() === $role->getStructure()->getEtablissement() ||
                            !empty(
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

                case DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT:
                case DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT: // NB : privilègie uitlisé aussi pour la modif du consentement
                    if (! $this->isUtilisateurEgaleCeDoctorant($entity)) {
                        return false;
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

    protected function isUtilisateurEgaleCeDoctorant(Doctorant $doctorant): bool
    {
        if (($identity = $this->getIdentityDoctorant()) === null) {
            return false;
        }

        return $doctorant->getId() === $identity->getId();
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