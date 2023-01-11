<?php

namespace Soutenance\Service\Notifier;

use Application\Service\Email\EmailTheseService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url as UrlHelper;
use Soutenance\Service\Membre\MembreService;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;

class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory
{
    protected string $notifierServiceClass = NotifierService::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotifierService
    {
        /** @var NotifierService $service */
        $service = parent::__invoke($container);

        /**
         * @var ActeurService $acteurService
         * @var MembreService $membreService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var TheseService $theseService
         * @var UtilisateurService $utilisateurService
         * @var \Individu\Service\IndividuService $individuService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);
        $roleService = $container->get('RoleService');
        $theseService = $container->get('TheseService');
        $emailTheseService = $container->get(EmailTheseService::class);
        $individuService = $container->get(IndividuService::class);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        $service->setUrlHelper($urlHelper);
        $service->setActeurService($acteurService);
        $service->setMembreService($membreService);
        $service->setRoleService($roleService);
        $service->setTheseService($theseService);
        $service->setEmailTheseService($emailTheseService);
        $service->setIndividuService($individuService);

        return $service;
    }
}