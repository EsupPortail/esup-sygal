<?php

namespace Application;

use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;

/**
 * Substitue le RouteMatch courant par un autre davantage orienté "métier".
 *
 * @package Application
 */
class RouteMatchInjector implements ListenerAggregateInterface, EntityManagerAwareInterface
{
    use ListenerAggregateTrait;
    use EntityManagerAwareTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'injectRouteMatch'], 1);
    }

    /**
     * Recherche de chaque entité spécifiée par son identifiant dans le RouteMatch de la requête courante,
     * et injection de cette entité dans le RouteMatch.
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function injectRouteMatch(MvcEvent $e)
    {
        $routeMatch = new RouteMatch($e->getRouteMatch()->getParams());
        $routeMatch->setMatchedRouteName($e->getRouteMatch()->getMatchedRouteName());
        $routeMatch->setEntityManager($this->getEntityManager());

        $e->setRouteMatch($routeMatch);
    }
}