<?php

namespace Depot\Form\Hydrator;

use Depot\Entity\Db\RdvBu;
use Laminas\Hydrator\HydratorInterface;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PointsDeVigilanceHydrator implements HydratorInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param array $data
     * @param RdvBu $rdvBu
     * @return \Depot\Entity\Db\RdvBu
     */
    public function hydrate(array $data, $rdvBu)
    {
        $rdvBu->setDivers($data['vigilance']);

        return $rdvBu;

    }

    /**
     * @param \Depot\Entity\Db\RdvBu $rdvBu
     * @return array
     */
    public function extract($rdvBu): array
    {
        $data['vigilance']  = $rdvBu->getDivers();

        return $data;
    }
}
