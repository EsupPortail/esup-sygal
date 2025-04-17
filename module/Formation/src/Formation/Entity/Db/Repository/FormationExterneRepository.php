<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctorant\Entity\Db\Doctorant;
use Formation\Entity\Db\FormationExterne;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationExterneRepository extends DefaultEntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @return FormationExterne[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('f')->getQuery()->getResult();
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return FormationExterne|null
     */
    public function getRequestedFormationExterne(AbstractActionController $controller, string $param = 'formationExterne') : ?FormationExterne
    {
        $id = $controller->params()->fromRoute($param);
        /** @var FormationExterne|null $formation */
        $formation = $this->find($id);
        return $formation;
    }

    /**
     * @param Doctorant|null $doctorant
     * @param string $champ
     * @param string $ordre
     * @param bool $keep_histo
     * @return array
     */
    public function fetchFormationsExternesByDoctorant(?Doctorant $doctorant, string $champ='libelle', string $ordre='ASC', bool $keep_histo = false) : array
    {
        $qb = $this->createQueryBuilder('formationExterne')
            ->orderBy('formationExterne.' . $champ, $ordre);

        if ($doctorant !== null) $qb = $qb->andWhere('formationExterne.doctorant = :doctorant')->setParameter('doctorant', $doctorant);

        if (!$keep_histo) $qb = $qb->andWhere('formationExterne.histoDestruction IS NULL');

        return $qb->getQuery()->getResult();
    }
}