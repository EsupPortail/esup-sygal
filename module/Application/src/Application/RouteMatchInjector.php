<?php

namespace Application;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Utilisateur;
use Doctorant\Entity\Db\Doctorant;
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
        $routeMatch->setEntityClassNamesMapping([
            'these' => These::class,
            'doctorant' => Doctorant::class,
            'fichier' => Fichier::class,
            'utilisateur' => Utilisateur::class,
            'role' => Role::class,
            'ecoleDoctorale' => EcoleDoctorale::class,
            'uniteRecherche' => UniteRecherche::class,
            'etablissement' => Etablissement::class,
            'structure' => Structure::class,
            'rapport' => Rapport::class,
        ]);


        $e->setRouteMatch($routeMatch);
    }
}