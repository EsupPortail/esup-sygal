<?php

namespace Application\Service\Etablissement;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Repository\EtablissementRepository;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use Application\Entity\Db\TypeStructure;

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
        $qb = $this->getEntityManager()->getRepository(Etablissement::class)->createQueryBuilder("et")
            ->leftJoin("et.structure", "str", "WITH", "et.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle")
        ;
        $etablissements = $qb->getQuery()->getResult();
        return $etablissements;
    }

    /**
     * @param string source
     * @param boolean $include (si 'true' alors seulement la source sinon tous sauf la source)
     * @return Etablissement[]
     */
    public function getEtablissementsBySource($source , $include=true) {
        $qb = $this->entityManager->getRepository(Etablissement::class)->createQueryBuilder("e")
            ->join("e.source", "s");

        if ($include) {
            $qb = $qb->andWhere("s.code = :source");
        } else {
            $qb = $qb->andWhere("s.code != :source");
        }
        $qb = $qb->setParameter("source", $source);

        /** @var Etablissement[] $etablissments */
        $etablissments = $qb->getQuery()->execute();
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

    public function getEtablissementByStructureId($id) {
        /** @var Etablissement $etablissement */
        $qb = $this->getRepository()->createQueryBuilder("e")
            ->addSelect("s")
            ->leftJoin("e.structure", "s")
            ->andWhere("s.id = :id")
            ->setParameter("id", $id);
        $etablissement = $qb->getQuery()->getOneOrNullResult();
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
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => 'etablissement']);
        $etablissement->getStructure()->setTypeStructure($typeStructure);
        $etablissement->setSourceCode("SyGAL::" . uniqid());

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

    public function findEtablissementByStructureId($structureId)
    {
        $qb = $this->getRepository()->createQueryBuilder("e")
            ->addSelect("s")
            ->join("e.structure", "s")
            ->andWhere("s.id = :structureId")
            ->setParameter("structureId", $structureId);
        try {
            $etablissement = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie plusieurs établissements avec le même id.", 0, $e);
        }
        return $etablissement;
    }
}