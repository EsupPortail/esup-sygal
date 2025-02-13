<?php

namespace Application\Controller;

use Application\Entity\Db\Utilisateur;
use Application\RouteMatch;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use Candidat\Controller\Plugin\UrlCandidat;
use Depot\Controller\Plugin\Url\UrlDepotPlugin;
use Depot\Controller\Plugin\UrlFichierHdr;
use Depot\Controller\Plugin\UrlFichierThese;
use Depot\Controller\Plugin\UrlWorkflow;
use Doctorant\Controller\Plugin\UrlDoctorant;
use Fichier\Controller\Plugin\Uploader\UploaderPlugin;
use Fichier\Controller\Plugin\UrlFichier;
use HDR\Entity\Db\HDR;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use These\Controller\Plugin\Url\UrlThesePlugin;
use These\Entity\Db\These;
use UnicaenApp\Controller\Plugin\AppInfos;
use UnicaenApp\Controller\Plugin\ConfirmPlugin;
use UnicaenApp\Controller\Plugin\Mail;
use UnicaenApp\Exception\RuntimeException;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * Class AbstractController
 *
 * @method ZfcUserAuthentication zfcUserAuthentication()
 * @method HttpRequest getRequest()
 * @method UploaderPlugin uploader()
 * @method boolean isAllowed($resource, $privilege = null)
 * @method UrlDepotPlugin urlDepot()
 * @method UrlThesePlugin urlThese()
 * @method UrlDoctorant urlDoctorant()
 * @method UrlCandidat urlCandidat()
 * @method UrlFichier urlFichier()
 * @method UrlFichierThese urlFichierThese()
 * @method UrlFichierHDR urlFichierHDR()
 * @method UrlWorkflow urlWorkflow()
 * @method Mail mail()
 * @method ConfirmPlugin confirm()
 * @method FlashMessenger flashMessenger()
 * @method AppInfos appInfos()
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
    protected function requestedThese(): These
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        $these = $routeMatch->getThese();
        if ($these === null) {
            throw new RuntimeException("Thèse introuvable");
        }

        return $these;
    }

    /**
     * Retourne la HDR présente dans la requête courante.
     *
     * @return HDR
     */
    protected function requestedHDR(): HDR
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        $hdr = $routeMatch->getHDR();
        if ($hdr === null) {
            throw new RuntimeException("HDR introuvable");
        }

        return $hdr;
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

    /**
     * Alimente le flashMessenger avec les éventuels messages/logs présents dans les paramètres de l'événement.
     *
     * @param \Laminas\EventManager\EventInterface $event Evénement en question
     * @param string $paramName Nom du paramètre contenant potentiellement les messages/logs
     */
    protected function flashMessengerAddMessagesFromEvent(EventInterface $event, string $paramName = 'logs')
    {
        if ($messages = $event->getParam($paramName, [])) {
            foreach ($messages as $namespace => $message) {
                $this->flashMessenger()->addMessage($message, $namespace);
            }
        }
    }
}