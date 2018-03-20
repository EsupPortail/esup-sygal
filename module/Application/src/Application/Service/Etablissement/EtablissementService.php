<?php

namespace Application\Service\Etablissement;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Repository\EtablissementRepository;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

class EtablissementService extends BaseService
{
    /**
     * @return EtablissementRepository
     */
    public function getRepository()
    {
        /** @var EtablissementRepository $repo */
        $repo = $this->entityManager->getRepository(Etablissement::class);

        return $repo;
    }

    /**
     * @return Etablissement[]
     */
    public function getEtablissements() {
        /** @var Etablissement[] $etablissments */
        $etablissments = $this->getRepository()->findAll();
        return $etablissments;
    }

    /**
     * @param int $id
     * @return null|Etablissement
     */
    public function getEtablissementById($id) {
        /** @var Etablissement $etablissement */
        $etablissement = $this->getRepository()->findOneBy(["id" => $id]);
        return $etablissement;
    }

    /**
     * @param Etablissement $etablissement
     * @param Utilisateur $createur
     * @return Etablissement
     */
    public function create(Etablissement $etablissement, Utilisateur $createur)
    {
        $etablissement->setHistoCreateur($createur);

        $this->persist($etablissement);
        $this->flush($etablissement);

        return $etablissement;
    }

    /**
     * @param Etablissement $etablissement
     * @param Utilisateur $destructeur
     */
    public function deleteSoftly(Etablissement $etablissement, Utilisateur $destructeur)
    {
        $etablissement->historiser($destructeur);

        $this->flush($etablissement);
    }

    /**
     * @param Etablissement $etablissement
     */
    public function undelete(Etablissement $etablissement)
    {
        $etablissement->dehistoriser();

        $this->flush($etablissement);
    }

    /**
     * @param Etablissement $etablissement
     * @return Etablissement
     */
    public function update(Etablissement $etablissement)
    {
        $this->flush($etablissement);

        return $etablissement;
    }



    public function setLogo(Etablissement $etablissement, $cheminLogo)
    {
        $etablissement->setCheminLogo($cheminLogo);
        $this->flush($etablissement);

        return $etablissement;
    }

    public function deleteLogo(Etablissement $etablissement)
    {
        $etablissement->setCheminLogo(null);
        $this->flush($etablissement);

        return $etablissement;
    }

    private function persist(Etablissement $etablissement)
    {
        $this->getEntityManager()->persist($etablissement);
        $this->getEntityManager()->persist($etablissement->getStructure());
    }

    private function flush(Etablissement $etablissement)
    {
        try {
            $this->getEntityManager()->flush($etablissement);
            $this->getEntityManager()->flush($etablissement->getStructure());
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'Etablissement", null, $e);
        }
    }

}