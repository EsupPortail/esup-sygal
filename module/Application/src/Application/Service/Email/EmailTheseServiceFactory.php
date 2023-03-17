<?php

namespace Application\Service\Email;

use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Psr\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use These\Service\Acteur\ActeurService;

class EmailTheseServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EmailTheseService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EmailTheseService
    {
        $service = new EmailTheseService();

        /** @var $roleService \Application\Service\Role\RoleService */
        $roleService = $container->get('RoleService');
        $service->setApplicationRoleService($roleService);

        /** @var $utilisateurService \Application\Service\Utilisateur\UtilisateurService */
        $utilisateurService = $container->get(UtilisateurService::class);
        $service->setUtilisateurService($utilisateurService);

        /** @var $variableService \Application\Service\Variable\VariableService */
        $variableService = $container->get(VariableService::class);
        $service->setVariableService($variableService);

        /** @var $acteurService \These\Service\Acteur\ActeurService */
        $acteurService = $container->get(ActeurService::class);
        $service->setActeurService($acteurService);

        /** @var $membreService \Soutenance\Service\Membre\MembreService */
        $membreService = $container->get(MembreService::class);
        $service->setMembreService($membreService);

        /** @var $individuService \Individu\Service\IndividuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        return $service;
    }
}
