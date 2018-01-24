<?php

namespace Application;

use UnicaenApp\Exception\LogicException;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;

/**
 * Composant permettant de détourner (deflect) une requête lorsque sa route correspond à un motif donné.
 * Le détournement se fait vers une autre route et l'URL initialement demandée est transmise par le pramaètre GET 'return'.
 */
class RouteDeflector implements ListenerAggregateInterface
{
    /**
     * @var string
     */
    protected $matchedRouteNamePattern;

    /**
     * @var array
     */
    protected $redirect;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * Interceptor constructor.
     *
     * @param string $matchedRouteNamePattern Expression régulière spécifiant la ou les routes à intercepter
     * @param array  $redirect                Route vers laquelle rediriger l'utilisateur
     */
    public function __construct($matchedRouteNamePattern, array $redirect)
    {
        $redirect = $this->normalizeRedirect($redirect);

        $this->matchedRouteNamePattern = $matchedRouteNamePattern;
        $this->redirect = $redirect;
    }

    protected function normalizeRedirect(array $redirect)
    {
        if (!array_key_exists('params', $redirect)) {
            $redirect['params'] = [];
        }

        $example = [
            'options' => [
                'name' => 'ma-route',
            ],
            'params' => [
                'these' => 123,
            ]
        ];

        if (!isset($redirect['options']['name'])) {
            throw new LogicException("Exemple de format attendu pour le paramètre 'redirect' : " . print_r($example, true));
        }

        return $redirect;
    }

    /**
     * @param MvcEvent $e
     */
    public function intercept(MvcEvent $e)
    {
        $this->event = $e;

        /** @var Request $request */
        $request = $e->getRequest();
        if ($request instanceof HttpRequest && $request->isXmlHttpRequest()) {
            return;
        }
        if (! $this->routeMatches()) {
            return;
        }
        if (! $this->isActivated()) {
            return;
        }

        $this->prepareRedirectArgument();

        $returnUri = $request->getRequestUri();
        $redirectUri = $this->assembleRedirectUri($returnUri);

        /** @var HttpResponse $response */
        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $redirectUri);
        $response->setStatusCode(302);

        $e->setResponse($response);
    }

    /**
     * @return bool
     */
    protected function routeMatches()
    {
        $routeName = $this->event->getRouteMatch()->getMatchedRouteName();

        return (bool) preg_match($this->matchedRouteNamePattern, $routeName);
    }

    /**
     * À surcharger si besoin pour retourner un booléen indiquant si ce composant est activé ou non.
     *
     * @return bool
     */
    protected function isActivated()
    {
        return true;
    }

    /**
     * À surcharger si besoin pour modifier les données concernant la route vers laquelle est fait le détournement.
     * Exemple :
     * <pre>
     * // injection du paramètre doctorant dans la route
     * $this->redirect['params']['doctorant'] = $this->getDoctorant()->getId();
     * </pre>
     *
     * @return $this
     */
    protected function prepareRedirectArgument()
    {
        return $this;
    }

    /**
     * Génère l'URL de redirection à partir des caractéristiques de l'arguement 'redirect'.
     *
     * @param string $returnUri URL de retour éventuelle
     * @return string
     */
    protected function assembleRedirectUri($returnUri = null)
    {
        $options = $this->redirect['options'];

        if ($returnUri) {
            $options = array_merge_recursive($options, ['query' => ['return' => $returnUri]]);
        }

        return $this->event->getRouter()->assemble($this->redirect['params'], $options);
    }



    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    private $listeners = [];

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'intercept'], -100);
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}