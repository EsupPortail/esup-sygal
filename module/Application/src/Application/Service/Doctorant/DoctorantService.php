<?php

namespace Application\Service\Doctorant;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\DoctorantCompl;
use Application\Entity\Db\Repository\DoctorantRepository;
use Application\Service\BaseService;

class DoctorantService extends BaseService
{
    /**
     * @return DoctorantRepository
     */
    public function getRepository()
    {
        /** @var DoctorantRepository $repo */
        $repo = $this->entityManager->getRepository(Doctorant::class);

        return $repo;
    }

    /**
     * @param Doctorant $doctorant
     * @param array     $data
     * @return DoctorantCompl
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDoctorant(Doctorant $doctorant, $data)
    {
        if (! ($complement = $doctorant->getComplement())) {
            $complement = new DoctorantCompl();
            $complement->setDoctorant($doctorant);
            $doctorant->setComplement($complement);

            $this->entityManager->persist($complement);
        }

        if (isset($data['identity'])) {
            $complement->setPersopass($data['identity']);
        }
        if (isset($data['mail'])) {
            $complement->setEmailPro($data['mail']);
        }

        $this->entityManager->flush($complement);

        return $complement;
    }
}