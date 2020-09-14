<?php


namespace Application\Service\Actualite;


use Interop\Container\ContainerInterface;

class ActualiteServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return ActualiteService
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        $actualite = $config['actualite'];
        $url = $actualite['flux'] ?? null;
        $actif = $actualite['actif'] ?? false;

        $service = new ActualiteService();
        $service->setActif($actif);
        $service->setUrl($url);

        return $service;
    }
}