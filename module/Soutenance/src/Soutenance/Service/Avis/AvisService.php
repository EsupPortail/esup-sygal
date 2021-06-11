<?php

namespace Soutenance\Service\Avis;

use Application\Entity\DateTimeAwareTrait;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Filter\NomAvisFormatter;
use UnicaenApp\Entity\UserInterface;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class AvisService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use DateTimeAwareTrait;

    /** GESTION DES ENTITÉS *******************************************************************************************/

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function create(Avis $avis)
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $avis->setHistoCreation($date);
        $avis->setHistoCreateur($user);
        $avis->setHistoModification($date);
        $avis->setHistoModificateur($user);

        try {
            $this->getEntityManager()->persist($avis);
            $this->getEntityManager()->flush($avis);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la création de l\'avis', $e);
        }

        return $avis;
    }

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function update(Avis $avis)
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $avis->setHistoModification($date);
        $avis->setHistoModificateur($user);

        try {
            $this->getEntityManager()->flush($avis);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la mise à jour de l\'avis', $e);
        }

        return $avis;
    }

    /**
     * @param Avis $avis
     */
    public function delete(Avis $avis)
    {
        try {
            $this->getEntityManager()->remove($avis);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de l\'effacement de l\'avis', $e);
        }
    }

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function historiser(Avis $avis)
    {
        /**
         * @var UserInterface $user
         * @var DateTime $date
         */
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $avis->getValidation()->setHistoDestruction($date);
        $avis->getValidation()->setHistoDestructeur($user);
        $avis->getFichier()->setHistoDestruction($date);
        $avis->getFichier()->setHistoDestructeur($user);
        $avis->setHistoDestruction($date);
        $avis->setHistoDestructeur($user);

        try {
            $this->getEntityManager()->flush($avis->getValidation());
            $this->getEntityManager()->flush($avis->getFichier());
            $this->getEntityManager()->flush($avis);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'historisation de l'avis (id:".$avis->getId().").");
        }

        return $avis;
    }

    /** REQUETE *******************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->getEntityManager()->getRepository(Avis::class)->createQueryBuilder('avis')
            ->addSelect('proposition')->join('avis.proposition', 'proposition')
            ->addSelect('membre')->join('avis.membre', 'membre')
        ;

        return $qb;
    }

    /**
     * @param int $id
     * @return Avis
     */
    public function getAvis($id)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('avis.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs avis partagent le même identifiant ['.$id.']', $e);
        }

        return $result;
    }

    /**
     * @param These $these
     * @return Avis[]
     */
    public function getAvisByThese(These $these)
    {
        $qb =$this->createQueryBuilder()
            ->andWhere('1 = pasHistorise(avis)')
            ->andWhere('proposition.these = :these')
            ->setParameter('these', $these)
        ;
        $result = $qb->getQuery()->getResult();

        $avis = [];
        /** @var Avis $entry */
        foreach ($result as $entry) {
            if ($entry->getRapporteur() AND $entry->getRapporteur()->getIndividu()) $avis[$entry->getRapporteur()->getIndividu()->getId()] = $entry;
        }
        return $avis;
    }

    /**
     * @param Membre $membre
     * @return Avis
     */
    public function getAvisByMembre(Membre $membre)
    {
        if ($membre === null OR $membre->getActeur() === null) return null;
        $qb = $this->createQueryBuilder()
            ->andWhere('1 = pasHistorise(avis)')
            ->andWhere('avis.membre = :membre')
            ->setParameter('membre', $membre);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs avis sont associés au rapporteur ['.$membre->getId().' - '.$membre->getIndividu()->getNomComplet().']');
        }

        return $result;
    }

    public function createAvisFromUpload($files, $membre)
    {
        $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
        $fichiers = $this->fichierService->createFichiersFromUpload($files, $nature, $version, new NomAvisFormatter($membre->getIndividu()));
        $this->fichierService->saveFichiers($fichiers);

        return current($fichiers);
    }
}