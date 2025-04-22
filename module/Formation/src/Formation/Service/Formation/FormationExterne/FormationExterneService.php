<?php

namespace Formation\Service\Formation\FormationExterne;

use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Formation\Entity\Db\FormationExterne;
use Formation\Entity\Db\Repository\FormationExterneRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationExterneService {
    use EntityManagerAwareTrait;
    use AnneeUnivServiceAwareTrait;

    /**
     * @return FormationExterneRepository
     */
    public function getRepository() : FormationExterneRepository
    {
        /** @var FormationExterneRepository $repo */
        $repo = $this->entityManager->getRepository(FormationExterne::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param FormationExterne $formationExterne
     * @return FormationExterne
     */
    public function create(FormationExterne $formationExterne) : FormationExterne
    {
        try {
            $this->getEntityManager()->persist($formationExterne);
            $this->getEntityManager()->flush($formationExterne);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [FormationExterne]",0, $e);
        }
        return $formationExterne;
    }

    /**
     * @param FormationExterne $formationExterne
     * @return FormationExterne
     */
    public function update(FormationExterne $formationExterne) : FormationExterne
    {
        try {
            $this->getEntityManager()->flush($formationExterne);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [FormationExterne]",0, $e);
        }
        return $formationExterne;
    }

    /**
     * @param FormationExterne $formationExterne
     * @return FormationExterne
     */
    public function historise(FormationExterne $formationExterne) : FormationExterne
    {
        try {
            $formationExterne->historiser();
            $this->getEntityManager()->flush($formationExterne);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [FormationExterne]",0, $e);
        }
        return $formationExterne;
    }

    /**
     * @param FormationExterne $formationExterne
     * @return FormationExterne
     */
    public function restore(FormationExterne $formationExterne) : FormationExterne
    {
        try {
            $formationExterne->dehistoriser();
            $this->getEntityManager()->flush($formationExterne);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [FormationExterne]",0, $e);
        }
        return $formationExterne;
    }

    /**
     * @param FormationExterne $formationExterne
     * @return FormationExterne
     */
    public function delete(FormationExterne $formationExterne) : FormationExterne
    {
        try {
            $this->getEntityManager()->remove($formationExterne);
            $this->getEntityManager()->flush($formationExterne);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [FormationExterne]",0, $e);
        }
        return $formationExterne;
    }

    public function getAnneesUnivAsOptions(array $formationsExternes): array
    {
        $anneesUniv = [];

        /** @var FormationExterne $formationsExterne */
        foreach($formationsExternes as $formationsExterne){
            $anneeUniv = $this->anneeUnivService->fromDate($formationsExterne->getDebut());
            $anneesUniv[$anneeUniv->getPremiereAnnee()] = $anneeUniv->getAnneeUnivToString();
        }

        uksort($anneesUniv, function($a, $b) {
            return $b <=> $a;
        });
        return $anneesUniv;
    }
}