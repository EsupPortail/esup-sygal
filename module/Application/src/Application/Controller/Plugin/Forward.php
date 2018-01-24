<?php

namespace Application\Controller\Plugin;

use Application\RouteMatch;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\Plugin\Forward as ForwardPlugin;
use Zend\Mvc\Exception\DomainException;
use Zend\Mvc\InjectApplicationEventInterface;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 09/06/16
 * Time: 16:48
 */
class Forward extends ForwardPlugin implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * Redéfinition pour instancier un RouteMatch maison.
     */
    public function dispatch($name, array $params = null)
    {
        $event   = clone($this->getEvent());

        $controller = $this->controllers->get($name);
        if ($controller instanceof InjectApplicationEventInterface) {
            $controller->setEvent($event);
        }

        // Allow passing parameters to seed the RouteMatch with & copy matched route name
        if ($params !== null) {
            // début modif
//            $routeMatch = new RouteMatch($params);
//            $routeMatch->setMatchedRouteName($event->getRouteMatch()->getMatchedRouteName());
            $routeMatch = $this->createRouteMatch($params, $event->getRouteMatch()->getMatchedRouteName());
            $event->setRouteMatch($routeMatch);
            // fin modif
        }

        if ($this->numNestedForwards > $this->maxNestedForwards) {
            throw new DomainException("Circular forwarding detected: greater than $this->maxNestedForwards nested forwards");
        }
        $this->numNestedForwards++;

        // Detach listeners that may cause problems during dispatch:
        $sharedEvents = $event->getApplication()->getEventManager()->getSharedManager();
        $listeners = $this->detachProblemListeners($sharedEvents);

        $return = $controller->dispatch($event->getRequest(), $event->getResponse());

        // If we detached any listeners, reattach them now:
        $this->reattachProblemListeners($sharedEvents, $listeners);

        $this->numNestedForwards--;

        return $return;
    }

    private function createRouteMatch($params, $matchedRouteName)
    {
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName($matchedRouteName);
        $routeMatch->setEntityManager($this->getEntityManager());

        return $routeMatch;
    }
}