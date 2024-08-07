<?php

namespace Formation\Service\Formation;

use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Repository\FormationRepository;
use Formation\Entity\Db\Seance;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationService {
    use EntityManagerAwareTrait;
    use AnneeUnivServiceAwareTrait;

    /**
     * @return FormationRepository
     */
    public function getRepository() : FormationRepository
    {
        /** @var FormationRepository $repo */
        $repo = $this->entityManager->getRepository(Formation::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function create(Formation $formation) : Formation
    {
        try {
            $this->getEntityManager()->persist($formation);
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function update(Formation $formation) : Formation
    {
        try {
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function historise(Formation $formation) : Formation
    {
        try {
            $formation->historiser();
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function restore(Formation $formation) : Formation
    {
        try {
            $formation->dehistoriser();
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function delete(Formation $formation) : Formation
    {
        try {
            $this->getEntityManager()->remove($formation);
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /** FACADE ********************************************************************************************************/

    public function getAnneesUnivAsOptions(array $sessions): array
    {
        $anneesUniv = [];

        /** @var Seance $seance */
        foreach($sessions as $session){
            /** @var Seance $sessionSeance */
            foreach($session->getSeances() as $sessionSeance){
                $anneeUniv = $this->anneeUnivService->fromDate($sessionSeance->getDebut());
                $anneesUniv[$anneeUniv->getPremiereAnnee()] = $anneeUniv->getAnneeUnivToString();
            }
        }
        //Année universitaire courante par défaut présente dans les options
        $anneeUnivCourante = $this->anneeUnivService->courante();
        $anneesUniv[$anneeUnivCourante->getPremiereAnnee()] = $anneeUnivCourante->getAnneeUnivToString();

        uksort($anneesUniv, function($a, $b) {
            return $a <=> $b;
        });
        return $anneesUniv;
    }
}