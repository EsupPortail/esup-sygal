<?php

namespace Application\Service\UniteRecherche;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\EtablissementRattachement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\UniteRechercheRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use Application\Entity\Db\TypeStructure;

/**
 * @method UniteRecherche|null findOneBy(array $criteria, array $orderBy = null)
 */
class UniteRechercheService extends BaseService implements RoleServiceAwareInterface
{
    use RoleServiceAwareTrait;
    /**
     * @return UniteRechercheRepository
     */
    public function getRepository()
    {
        /** @var UniteRechercheRepository $repo */
        $repo = $this->entityManager->getRepository(UniteRecherche::class);

        return $repo;
    }

    /**
     * @return UniteRecherche[]
     */
    public function getUnitesRecherches() {
        /** @var UniteRecherche[] $unites */
        $qb = $this->getEntityManager()->getRepository(UniteRecherche::class)->createQueryBuilder("ur")
            ->leftJoin("ur.structure", "str", "WITH", "ur.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle")
        ;
        $unites = $qb->getQuery()->getResult();
        return $unites;
    }

    /**
     * @param int $id
     * @return null|UniteRecherche
     */
    public function getUniteRechercheById($id) {
        /** @var UniteRecherche $unite */
        $unite = $this->getRepository()->findOneBy(["id" => $id]);
        return $unite;
    }

    /**
     * @param int $id
     * @return Individu[]
     */
    public function getIndividuByUniteRechercheId($id) {
        $unite = $this->getUniteRechercheById($id);
        $individus = $this->roleService->getIndividuByStructure($unite->getStructure());
        return $individus;
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

        $this->flush($ur);
    }

    public function undelete(UniteRecherche $ur)
    {
        $ur->dehistoriser();

        $this->flush($ur);
    }

    public function create(UniteRecherche $ur, Utilisateur $createur)
    {
        $ur->setHistoCreateur($createur);
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => 'unite-recherche']);
        $ur->getStructure()->setTypeStructure($typeStructure);


        $this->persist($ur);
        $this->flush($ur);

        return $ur;
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

    private function persist(UniteRecherche $ur)
    {
        $this->getEntityManager()->persist($ur);
        $this->getEntityManager()->persist($ur->getStructure());
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
     * @return EtablissementRattachement[]
     */
    public function findEtablissementRattachement(UniteRecherche $unite)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->setParameter("unite", $unite);

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param UniteRecherche $unite
     * @param Etablissement $etablissement
     * @throws OptimisticLockException
     */
    public function addEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $er = new EtablissementRattachement();
        $er->setUnite($unite);
        $er->setEtablissement($etablissement);
        $er->setPrincipal(false);
        $this->getEntityManager()->persist($er);
        $this->getEntityManager()->flush($er);
    }

    /**
     * @param UniteRecherche $unite
     * @param Etablissement $etablissement
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

    public function setEtablissementRattachementPrincipal(UniteRecherche $unite, Etablissement $etablissement) {
        $ers = $this->findEtablissementRattachement($unite);

        foreach($ers as $er) {
            if ($er->getEtablissement()->getId() === $etablissement->getId()) {
                $er->setPrincipal(true);
            } else {
                $er->setPrincipal(false);
            }
            $this->getEntityManager()->flush($er);
        }
    }

    public function existEtablissementRattachement($unite, $etablissement)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }


}