<?php

namespace Soutenance\Service\Notifier;

use Application\Service\Email\EmailTheseService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url as UrlHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Membre\MembreService;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;

class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory
{
    protected string $notifierServiceClass = NotifierService::class;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotifierService
    {
        /** @var NotifierService $service */
        $service = parent::__invoke($container);

        /**
         * @var ActeurService $acteurService
         * @var EmailTheseService $emailTheseService
         * @var MembreService $membreService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var TheseService $theseService
         * @var UtilisateurService $utilisateurService
         * @var IndividuService $individuService
         */
        $acteurService = $container->get(ActeurService::class);
        $emailTheseService = $container->get(EmailTheseService::class);
        $membreService = $container->get(MembreService::class);
        $roleService = $container->get('RoleService');
        $theseService = $container->get('TheseService');
        $individuService = $container->get(IndividuService::class);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        $service->setUrlHelper($urlHelper);
        $service->setActeurService($acteurService);
        $service->setEmailTheseService($emailTheseService);
        $service->setMembreService($membreService);
        $service->setRoleService($roleService);
        $service->setTheseService($theseService);
        $service->setIndividuService($individuService);

        return $service;
    }
}