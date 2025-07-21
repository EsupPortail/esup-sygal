<?php

namespace Application\Controller\Plugin;

use Application\RouteMatch;
use Laminas\Mvc\Controller\Plugin\Forward as ForwardPlugin;
use Laminas\Mvc\Exception\DomainException;
use Laminas\Mvc\InjectApplicationEventInterface;
use Laminas\Stdlib\Parameters;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

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
            $routeMatch = new RouteMatch($params);
            $routeMatch->setMatchedRouteName($event->getRouteMatch()->getMatchedRouteName());
            ///////////// debut ajout /////////////
            $routeMatch->setEntityManager($this->getEntityManager());
            ///////////// fin ajout /////////////
            $event->setRouteMatch($routeMatch);
        }

        if ($this->numNestedForwards > $this->maxNestedForwards) {
            throw new DomainException("Circular forwarding detected: greater than $this->maxNestedForwards nested forwards");
        }
        $this->numNestedForwards++;

        // Detach listeners that may cause problems during dispatch:
        $sharedEvents = $event->getApplication()->getEventManager()->getSharedManager();
        $listeners = $this->detachProblemListeners($sharedEvents);

        ///////////// debut modif /////////////
//        $return = $controller->dispatch($request, $event->getResponse());
        // Incroyable mais vrai : les query params passent à l'as !!
        /** @var \Laminas\Http\Request $request */
        $request = $event->getRequest();
        $request->setQuery(new Parameters($params['query'] ?? []));

        $return = $controller->dispatch($request, $event->getResponse());
        ///////////// fin modif /////////////

        // If we detached any listeners, reattach them now:
        $this->reattachProblemListeners($sharedEvents, $listeners);

        $this->numNestedForwards--;

        return $return;
    }
}