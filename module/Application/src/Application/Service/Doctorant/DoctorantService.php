<?php

namespace Application\Service\Doctorant;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\DoctorantCompl;
use Application\Service\BaseService;
use Doctrine\DBAL\DBALException;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class DoctorantService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Doctorant::class);
    }

    /**
     * @param Doctorant $doctorant
     * @param array     $data
     * @return DoctorantCompl
     * @throws DBALException Si un doctorant existe déjà avec le persopass spécifé
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