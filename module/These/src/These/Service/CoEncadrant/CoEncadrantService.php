<?php

namespace These\Service\CoEncadrant;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\UniteRecherche;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

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
     * @param EcoleDoctorale|UniteRecherche $structureConcrete
     * @param bool $encours
     * @return array
     */
    public function findCoEncadrantsByStructureConcrete(StructureConcreteInterface $structureConcrete, bool $encours = true): array
    {
        /** @var \Application\QueryBuilder\DefaultQueryBuilder $qb */
        $qb = $this->createQueryBuilder()
            ->addSelect('these')->join('acteur.these', 'these')
            ->andWhere('these.histoDestruction is null')
            ->andWhere('acteur.histoDestruction is null');

        if ($structureConcrete instanceof EcoleDoctorale) {
            $qb
                ->join('these.ecoleDoctorale', 'ecoleDoctorale')->addSelect('ecoleDoctorale')
                ->join('ecoleDoctorale.structure', 'ecoleDoctorale_structure')->addSelect('ecoleDoctorale_structure')
                ->andWhereStructureOuSubstituanteIs($structureConcrete->getStructure(), 'ecoleDoctorale_structure');
        } elseif ($structureConcrete instanceof UniteRecherche) {
            $qb
                ->join('these.uniteRecherche', 'uniteRecherche')->addSelect('uniteRecherche')
                ->join('uniteRecherche.structure', 'uniteRecherche_structure')->addSelect('uniteRecherche_structure')
                ->andWhereStructureOuSubstituanteIs($structureConcrete->getStructure(), 'uniteRecherche_structure');
        } else {
            throw new InvalidArgumentException("Type de structure spécifié inattendu : " . get_class($structureConcrete));
        }

        if ($encours) {
            $qb->andWhere('these.etatThese = :encours')
                ->setParameter('encours', These::ETAT_EN_COURS);
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