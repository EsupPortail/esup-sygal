<?php

namespace Application\Process\Utilisateur;

use Application\Service\Notification\ApplicationNotificationFactory;
use Application\Service\Utilisateur\UtilisateurService;
use Individu\Service\IndividuService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use UnicaenAuth\Service\User as UserService;

class UtilisateurProcessFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): UtilisateurProcess
    {
        $process = new UtilisateurProcess();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $process->setEntityManager($em);

        /** @var \Application\Service\Utilisateur\UtilisateurService $utilisateurService */
        $utilisateurService = $container->get(UtilisateurService::class);
        $process->setUtilisateurService($utilisateurService);

        /** @var UserService $userService */
        $userService = $container->get('unicaen-auth_user_service');
        $process->setUserService($userService);

        /** @var \Individu\Service\IndividuService $service */
        $individuService = $container->get(IndividuService::class);
        $process->setIndividuService($individuService);

        /** @var \Application\Service\Notification\ApplicationNotificationFactory $applicationNotificationFactory */
        $applicationNotificationFactory = $container->get(ApplicationNotificationFactory::class);
        $process->setApplicationNotificationFactory($applicationNotificationFactory);

        /** @var \Notification\Service\NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $process->setNotifierService($notifierService);

        return $process;
    }
}