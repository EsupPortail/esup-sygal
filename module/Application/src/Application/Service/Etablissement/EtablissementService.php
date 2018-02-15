<?php

namespace Application\Service\Etablissement;

use Application\Entity\Db\Repository\EtablissementRepository;
use Application\Entity\Db\Etablissement;
use Application\Form\EtablissementForm;
use Application\Service\BaseService;

class EtablissementService extends BaseService
{
    /**
     * @return EtablissementRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Etablissement::class);
    }

    public function create(Etablissement $etablissement)
    {
        $this->getEntityManager()->persist($etablissement);
        $this->getEntityManager()->flush($etablissement);

        return $etablissement;
    }

    public function update(Etablissement $etablissement)
    {
        $this->getEntityManager()->flush($etablissement);

        return $etablissement;
    }

    public function delete(Etablissement $etablissement)
    {
        if ($etablissement !== null) {
            $this->entityManager->remove($etablissement);
            $this->entityManager->flush();
        }
    }

    public function setLogo(Etablissement $etablissement, $cheminLogo)
    {
        $etablissement->setCheminLogo($cheminLogo);
        $this->getEntityManager()->flush($etablissement);

        return $etablissement;
    }

    public function deleteLogo(Etablissement $etablissement)
    {
        $etablissement->setCheminLogo(null);
        $this->getEntityManager()->flush($etablissement);

        return $etablissement;
    }

}