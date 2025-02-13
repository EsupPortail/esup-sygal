<?php

namespace Application\Service\Email;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Psr\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Acteur\Service\ActeurThese\ActeurTheseService;

class EmailServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EmailService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EmailService
    {
        $service = new EmailService();

        /** @var $roleService \Application\Service\Role\RoleService */
        $roleService = $container->get('RoleService');
        $service->setApplicationRoleService($roleService);

        /** @var $utilisateurService \Application\Service\Utilisateur\UtilisateurService */
        $utilisateurService = $container->get(UtilisateurService::class);
        $service->setUtilisateurService($utilisateurService);

        /** @var $variableService \Application\Service\Variable\VariableService */
        $variableService = $container->get(VariableService::class);
        $service->setVariableService($variableService);

        /** @var $acteurService \Acteur\Service\ActeurThese\ActeurTheseService */
        $acteurService = $container->get(ActeurTheseService::class);
        $service->setActeurTheseService($acteurService);

        /** @var $acteurHDRService ActeurHDRService */
        $acteurHDRService = $container->get(ActeurHDRService::class);
        $service->setActeurHDRService($acteurHDRService);

        /** @var $membreService \Soutenance\Service\Membre\MembreService */
        $membreService = $container->get(MembreService::class);
        $service->setMembreService($membreService);

        /** @var $individuService \Individu\Service\IndividuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        return $service;
    }
}
