<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\RecapBu;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class PointsDeVigilanceHydrator implements HydratorInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param array $data
     * @param RdvBu $rdvBu
     * @return RdvBu
     * @throws OptimisticLockException
     */
    public function hydrate(array $data, $rdvBu) {
        $rdvBu->setDivers($data['vigilance']);

        return $rdvBu;

    }

    /**
     * @param RdvBu $rdvBu
     * @return array
     */
    public function extract($rdvBu) {
        $data['vigilance']  = $rdvBu->getDivers();

        return $data;
    }
}
