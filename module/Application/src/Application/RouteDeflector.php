<?php

namespace Application;

use UnicaenApp\Exception\LogicException;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;

/**
 * Composant permettant de détourner (deflect) une requête lorsque sa route correspond à un motif donné.
 * Le détournement se fait vers une autre route et l'URL initialement demandée est transmise par le pramaètre GET 'return'.
 */
class RouteDeflector implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

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
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'intercept'], -100);
    }
}