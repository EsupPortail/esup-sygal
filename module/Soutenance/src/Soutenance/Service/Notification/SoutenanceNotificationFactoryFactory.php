<?php

namespace Soutenance\Service\Notification;

use Application\Service\Email\EmailTheseService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Url\UrlService;
use Application\Service\Validation\ValidationService;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;
use UnicaenRenderer\Service\Rendu\RenduService;

/**
 * @author Unicaen
 */
class SoutenanceNotificationFactoryFactory extends NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = SoutenanceNotificationFactory::class;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SoutenanceNotificationFactory
    {
        /** @var SoutenanceNotificationFactory $factory */
        $factory = parent::__invoke($container);

        /**
         * @var ActeurService $acteurService
         * @var EmailTheseService $emailTheseService
         * @var MembreService $membreService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var TheseService $theseService
         * @var UtilisateurService $utilisateurService
         * @var ValidationService $validationService
         */
        $acteurService = $container->get(ActeurService::class);
        $emailTheseService = $container->get(EmailTheseService::class);
        $membreService = $container->get(MembreService::class);
        $roleService = $container->get('RoleService');
        $theseService = $container->get('TheseService');
        $validationService = $container->get(ValidationService::class);

        $factory->setActeurService($acteurService);
        $factory->setEmailTheseService($emailTheseService);
        $factory->setMembreService($membreService);
        $factory->setRoleService($roleService);
        $factory->setTheseService($theseService);
        $factory->setValidationService($validationService);

        /** @var RenduService $renduService */
        $renduService = $container->get(RenduService::class);
        $factory->setRenduService($renduService);

        /** @var UrlService $urlService */
        $urlService = $container->get(UrlService::class);
        $factory->setUrlService($urlService);

        return $factory;
    }
}
