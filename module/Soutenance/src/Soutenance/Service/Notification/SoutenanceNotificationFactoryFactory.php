<?php

namespace Soutenance\Service\Notification;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Email\EmailService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Url\UrlService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Validation\Service\ValidationThese\ValidationTheseService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use These\Service\These\TheseService;
use UnicaenRenderer\Service\Rendu\RenduService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

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
         * @var ActeurTheseService $acteurService
         * @var ActeurHDRService $acteurHDRService
         * @var EmailService $emailService
         * @var MembreService $membreService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var TheseService $theseService
         * @var UtilisateurService $utilisateurService
         * @var ValidationTheseService $validationService
         * @var ValidationHDRService $validationHDRService
         */
        $acteurService = $container->get(ActeurTheseService::class);
        $acteurHDRService = $container->get(ActeurHDRService::class);
        $emailService = $container->get(EmailService::class);
        $membreService = $container->get(MembreService::class);
        $roleService = $container->get('RoleService');
        $theseService = $container->get('TheseService');
        $validationService = $container->get(ValidationTheseService::class);
        $validationHDRService = $container->get(ValidationHDRService::class);

        $factory->setActeurTheseService($acteurService);
        $factory->setActeurHDRService($acteurHDRService);
        $factory->setEmailService($emailService);
        $factory->setMembreService($membreService);
        $factory->setApplicationRoleService($roleService);
        $factory->setTheseService($theseService);
        $factory->setValidationTheseService($validationService);
        $factory->setValidationHDRService($validationHDRService);

        /** @var RenduService $renduService */
        $renduService = $container->get(RenduService::class);
        $factory->setRenduService($renduService);

        /** @var UrlService $urlService */
        $urlService = $container->get(UrlService::class);
        $factory->setUrlService($urlService);

        /** @var TemplateVariablePluginManager $rapm */
        $rapm = $container->get(TemplateVariablePluginManager::class);
        $factory->setTemplateVariablePluginManager($rapm);

        return $factory;
    }
}
