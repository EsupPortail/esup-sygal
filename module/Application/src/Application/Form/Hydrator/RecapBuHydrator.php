<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RecapBu;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class RecapBuHydrator implements HydratorInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param array $data
     * @param RecapBu $recap
     * @return RecapBu
     * @throws OptimisticLockException
     */
    public function hydrate(array $data, $recap) {

        $recap->setIdOrcid($data['orcid']);
        $recap->setNNT($data['nnt']);
        $recap->setVigilance($data['vigilance']);
        return $recap;

    }

    /**
     * @param RecapBu $recap
     * @return array
     */
    public function extract($recap) {

        $data['orcid']      = $recap->getIdOrcid();
        $data['nnt']        = $recap->getNNT();
        $data['vigilance']  = $recap->getVigilance();

        return $data;
    }
}
