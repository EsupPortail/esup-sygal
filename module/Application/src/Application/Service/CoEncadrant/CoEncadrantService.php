<?php

namespace Application\Service\CoEncadrant;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class CoEncadrantService {
    use EntityManagerAwareTrait;

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('role.code = :code')
            ->setParameter('code', 'B')
        ;
        return $qb;
    }

    /**
     * @param string $term
     * @return Acteur[]
     */
    public function findByText(string $term)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere("LOWER(concat(concat(concat(concat(individu.prenom1, ' '),individu.nomUsuel), ' '), individu.prenom1)) like :term")
            ->setParameter('term', '%'.strtolower($term).'%')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int|null $id
     * @return Acteur|null
     */
    public function getCoEncadrant(?int $id) : ?Acteur
    {
        if ($id === null) return null;

        $qb = $this->createQueryBuilder()
            ->andWhere('acteur.id = :id')
            ->setParameter('id', $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Acteur partagent le même id [".$id."].");
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return ?Acteur
     */
    public function getRequestedCoEncadrant(AbstractActionController $controller, string $param = 'co-encadrant') : ?Acteur
    {
        $id = $controller->params()->fromRoute($param);
        $result = $this->getCoEncadrant($id);
        return $result;
    }

    /**
     * @param EcoleDoctorale|UniteRecherche $structure
     * @param bool $encours
     * @return array
     */
    public function getCoEncadrantsByStructureConcrete($structure, $encours = true)
    {
        $qb = $this->createQueryBuilder()
            ->addSelect('these')->join('acteur.these', 'these')
            ->andWhere('these.histoDestruction is null')
            ->andWhere('acteur.histoDestruction is null')
        ;

        $structureOk = false;
        if ($structure instanceof EcoleDoctorale) {
            $qb = $qb->andWhere('these.ecoleDoctorale = :structure');
            $structureOk = true;
        }
        if ($structure instanceof UniteRecherche) {
            $qb = $qb->andWhere('these.uniteRecherche = :structure');
            $structureOk = true;
        }

        if ($structureOk === false OR $structure === null) {
            throw new RuntimeException('Pas de structure concrete de trouvée');
        }
        $qb = $qb->setParameter('structure', $structure);

        if ($encours) {
            $qb = $qb->andWhere('these.etatThese = :encours')
                ->setParameter('encours', These::ETAT_EN_COURS)
            ;
        }
        $result = $qb->getQuery()->getResult();

        //todo integer dans la requete
        $grouped = [];
        foreach ($result as $acteur) {
            $grouped[$acteur->getIndividu()->getId()]['co-encadrant'] = $acteur;
            $grouped[$acteur->getIndividu()->getId()]['theses'][] = $acteur->getThese();
        }
        return $grouped;
    }
}