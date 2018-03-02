<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\IndividuRepository;
use Application\Service\BaseService;
use UnicaenImport\Entity\Db\Source;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    /**
     * @return IndividuRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Individu::class);
    }

    /**
     * @param People $people
     * @return Individu
     */
    public function createFromPeople(People $people)
    {
        $sns = (array)$people->get('sn');
        $usuel = array_pop($sns);
        $patro = array_pop($sns);
        if ($patro === null) $patro = $usuel;

        $entity = new Individu();
        $entity->setNomUsuel($usuel);
        $entity->setNomPatronymique($patro);
        $entity->setPrenom($people->get('givenName'));
        $entity->setCivilite($people->get('supannCivilite'));
        $entity->setEmail($people->get('mail'));

        /** @var Source $source */
        $entity->setSourceCode($people->get('supannEmpId'));

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        return $entity;
    }
}