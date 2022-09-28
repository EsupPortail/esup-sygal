<?php

namespace Structure\Service\UniteRecherche;

use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\EtablissementRattachement;
use Structure\Entity\Db\Repository\UniteRechercheRepository;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method UniteRecherche|null findOneBy(array $criteria, array $orderBy = null)
 */
class UniteRechercheService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return UniteRechercheRepository
     */
    public function getRepository(): UniteRechercheRepository
    {
        /** @var UniteRechercheRepository $repo */
        $repo = $this->entityManager->getRepository(UniteRecherche::class);

        return $repo;
    }

    /**
     * @param UniteRecherche $unite
     * @return EtablissementRattachement[]
     */
    public function findEtablissementRattachement(UniteRecherche $unite): array
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->addSelect("e, s")
            ->join("er.etablissement", "e")
            ->join("e.structure", "s")
            ->andWhere("er.unite = :unite")
            ->orderBy("s.libelle")
            ->setParameter("unite", $unite);

        return $qb->getQuery()->getResult();
    }

    public function existEtablissementRattachement($unite, $etablissement): ?EtablissementRattachement
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : Plusieurs EtablissementRattachement trouvés pour une ED et un Etab donnés");
        }
    }

    /**
     * Historise une ED.
     *
     * @param UniteRecherche $ur
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(UniteRecherche $ur, Utilisateur $destructeur)
    {
        $ur->historiser($destructeur);
        $ur->getStructure()->historiser($destructeur);

        $this->flush($ur);
    }

    public function undelete(UniteRecherche $ur)
    {
        $ur->dehistoriser();
        $ur->getStructure()->dehistoriser();

        $this->flush($ur);
    }

    public function create(UniteRecherche $structureConcrete, Utilisateur $createur): UniteRecherche
    {
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => 'etablissement']);

        $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        $structureConcrete->setSourceCode($sourceCode);
        $structureConcrete->setHistoCreateur($createur);

        $structure = $structureConcrete->getStructure();
        $structure->setTypeStructure($typeStructure);
        $structure->setSourceCode($sourceCode);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($structure);
            $this->entityManager->persist($structureConcrete);
            $this->entityManager->flush($structure);
            $this->entityManager->flush($structureConcrete);
            $this->entityManager->commit();
        } catch (ORMException $e) {
            $this->rollback();
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR '$structure'", null, $e);
        }

        return $structureConcrete;
    }

    public function update(UniteRecherche $ur)
    {
        $this->flush($ur);

        return $ur;
    }

    public function setLogo(UniteRecherche $unite, $cheminLogo)
    {
        $unite->setCheminLogo($cheminLogo);
        $this->flush($unite);

        return $unite;
    }

    public function deleteLogo(UniteRecherche $unite)
    {
        $unite->setCheminLogo(null);
        $this->flush($unite);

        return $unite;
    }

    private function flush(UniteRecherche $ur)
    {
        try {
            $this->getEntityManager()->flush($ur);
            $this->getEntityManager()->flush($ur->getStructure());
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }

    /** ETABLISSEMENT DE RATTACHEMENT **/

    /**
     * @param UniteRecherche $unite
     * @param Etablissement  $etablissement
     * @throws OptimisticLockException
     */
    public function addEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $er = new EtablissementRattachement();
        $er->setUniteRecherche($unite);
        $er->setEtablissement($etablissement);
        $this->getEntityManager()->persist($er);
        $this->getEntityManager()->flush($er);
    }

    /**
     * @param UniteRecherche $unite
     * @param Etablissement  $etablissement
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);
        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            $this->getEntityManager()->remove($result);
            $this->getEntityManager()->flush($result);
        }
    }

    //todo faire les filtrage et considerer que les UR internes
    public function getUnitesRecherchesAsOptions() : array
    {
        $unites = $this->getRepository()->findAll(true);

        $options = [];
        foreach ($unites as $unite) {
            $options[$unite->getId()] = $unite->getLibelle() . " " ."<span class='badge'>".$unite->getSigle()."</span>";
        }
        return $options;
    }
}