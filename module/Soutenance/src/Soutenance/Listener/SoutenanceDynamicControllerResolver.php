<?php

namespace Soutenance\Listener;

use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Soutenance\Controller\HDR\Presoutenance\PresoutenanceHDRController;
use Soutenance\Controller\HDR\Proposition\PropositionHDRController;
use Soutenance\Controller\These\Presoutenance\PresoutenanceTheseController;
use Soutenance\Controller\These\PropositionThese\PropositionTheseController;

class SoutenanceDynamicControllerResolver
{
    public function __invoke(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();

        if (!$routeMatch instanceof RouteMatch) {
            return;
        }

        // Récupérer le type depuis les paramètres de la route
        $type = $routeMatch->getParam('type');
        $routeName = $routeMatch->getMatchedRouteName();

        // Vérifier si la route contient 'proposition' en tant que sous-route
        if (str_contains($routeName, 'proposition')) {
            // Changer dynamiquement le contrôleur en fonction du type
            if ($type === 'hdr') {
                $routeMatch->setParam('controller', PropositionHDRController::class);
            } elseif ($type === 'these') {
                $routeMatch->setParam('controller', PropositionTheseController::class);
            }
        }

        // Vérifier si la route contient 'presoutenance' en tant que sous-route
        if (str_contains($routeName, 'presoutenance')) {
            // Changer dynamiquement le contrôleur en fonction du type
            if ($type === 'hdr') {
                $routeMatch->setParam('controller', PresoutenanceHDRController::class);
            } elseif ($type === 'these') {
                $routeMatch->setParam('controller', PresoutenanceTheseController::class);
            }
        }
    }
}
