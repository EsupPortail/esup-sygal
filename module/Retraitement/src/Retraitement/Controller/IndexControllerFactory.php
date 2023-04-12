<?php

namespace Retraitement\Controller;

use Application\Entity\Db\Utilisateur;
use Application\EventRouterReplacer;
use Application\Service\Utilisateur\UtilisateurService;
use Depot\Service\Notification\DepotNotificationFactory;
use Interop\Container\ContainerInterface;
use Laminas\Router\Http\TreeRouteStack;
use Notification\Service\NotifierService;
use UnicaenApp\Exception\RuntimeException;

class IndexControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);

        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        $controller = new IndexController();
        $controller->setUtilisateurApplication($this->getUtilisateurApp($container));
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setNotifierService($notifierService);

        /** @var \Depot\Service\Notification\DepotNotificationFactory $depotNotificationFactory */
        $depotNotificationFactory = $container->get(DepotNotificationFactory::class);
        $controller->setDepotNotificationFactory($depotNotificationFactory);

        return $controller;
    }

    /**
     * Retourne le pseudo-utilisateur correspondant à l'application.
     *
     * @param ContainerInterface $container
     * @return Utilisateur
     */
    public function getUtilisateurApp(ContainerInterface $container)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $container->get('UtilisateurService');

        $utilisateur = $utilisateurService->fetchAppPseudoUtilisateur();

        if (!$utilisateur) {
            throw new RuntimeException("Pseudo-utilisateur application introuvable");
        }

        return $utilisateur;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    private function getCliConfig(ContainerInterface $container)
    {
        $config = $container->get('Config');

        return [
            'domain' => isset($config['cli_config']['domain']) ? $config['cli_config']['domain'] : null,
            'scheme' => isset($config['cli_config']['scheme']) ? $config['cli_config']['scheme'] : null,
        ];
    }
}