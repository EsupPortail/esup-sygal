<?php

namespace Application;

use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteStackInterface;
use Laminas\Uri\Http as HttpUri;

class EventRouterReplacer
{
    /**
     * @var RouteStackInterface
     */
    private $previousRouter;

    /**
     * @var TreeRouteStack
     */
    private $httpRouter;

    /**
     * @var array
     */
    private $cliConfig;

    /**
     * @var MvcEvent
     */
    private $event;

    /**
     * ConsoleRequestEventRouter constructor.
     *
     * @param TreeRouteStack $httpRouter
     * @param array          $cliConfig
     */
    public function __construct(TreeRouteStack $httpRouter, array $cliConfig = [])
    {
        $this->httpRouter = $httpRouter;
        $this->cliConfig = $cliConfig;
    }

    /**
     * @param MvcEvent $event
     */
    public function replaceEventRouter(MvcEvent $event)
    {
        // S'il s'agit d'une requête de type Console (CLI), le plugin de contrôleur Url utilisé par les vues
        // n'est pas en mesure de construire des URL (normal, le ConsoleRouter ne sait pas ce qu'est une URL!).
        // On injecte donc provisoirement un HttpRouter dans le circuit.
        $previousRouter = $event->getRouter();
        $event->setRouter($this->httpRouter);

        // De plus, pour fonctionner, le HttpRouter a besoin du "prefixe" à utiliser pour assembler les URL
        // (ex: "http://localhost/ose"). Ce prefixe est fourni via 2 paramètres de config : 'scheme' et 'domain'.
        $httpUri = new HttpUri();
        $httpUri
            ->setHost($this->cliConfig['domain'])// ex: "/sodoct.local", "sodoct.unicaen.fr"
            ->setScheme($this->cliConfig['scheme']);
        $this->httpRouter->setRequestUri($httpUri);

        $this->event = $event;
        $this->previousRouter = $previousRouter;
    }

    /**
     * @return RouteStackInterface
     */
    public function restoreEventRouter()
    {
        // Rétablissement du router initial (cf. commentaires plus haut).
        $this->event->setRouter($this->previousRouter);
    }
}