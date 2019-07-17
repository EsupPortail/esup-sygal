<?php

namespace Application\Controller;

use Application\Controller\Plugin\Uploader\UploaderPlugin;
use Application\Controller\Plugin\Url\UrlThesePlugin;
use Application\Controller\Plugin\UrlDoctorant;
use Application\Controller\Plugin\UrlFichier;
use Application\Controller\Plugin\UrlFichierThese;
use Application\Controller\Plugin\UrlWorkflow;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use UnicaenApp\Controller\Plugin\ConfirmPlugin;
use UnicaenApp\Controller\Plugin\Mail;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * Class AbstractController
 *
 * @method ZfcUserAuthentication zfcUserAuthentication()
 * @method HttpRequest getRequest()
 * @method UploaderPlugin uploader()
 * @method boolean isAllowed($resource, $privilege)
 * @method UrlThesePlugin urlThese()
 * @method UrlDoctorant urlDoctorant()
 * @method UrlFichier urlFichier()
 * @method UrlFichierThese urlFichierThese()
 * @method UrlWorkflow urlWorkflow()
 * @method Mail mail()
 * @method ConfirmPlugin confirm()
 */
class AbstractController extends AbstractActionController
    implements UserContextServiceAwareInterface
{
    use UserContextServiceAwareTrait;

    /**
     * Pseudo-utilisateur correspondant à l'application elle-même.
     *
     * @var Utilisateur
     */
    protected $utilisateurApplication;

    /**
     * Teste si un privilège est accordé sur une ressource.
     * Si ce n'est pas le cas une exception est levée.
     *
     * @param mixed  $resource
     * @param string $privilege
     * @throws UnAuthorizedException
     */
    protected function assertIsAllowed($resource, $privilege)
    {
        if (! $this->isAllowed($resource, $privilege)) {
            throw new UnAuthorizedException("Niet");
        }
    }

    /**
     * Retourne la thèse présente dans la requête courante.
     *
     * @return These
     */
    protected function requestedThese()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getThese();
    }

    /**
     * Spécifie le Pseudo-utilisateur correspondant à l'application elle-même.
     *
     * @param Utilisateur $utilisateurApplication
     * @return self
     */
    public function setUtilisateurApplication(Utilisateur $utilisateurApplication)
    {
        $this->utilisateurApplication = $utilisateurApplication;

        return $this;
    }
}