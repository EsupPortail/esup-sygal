<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\UserWrapper;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class IndividuRepository extends DefaultEntityRepository
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * Recherche d'un Individu à partir de son SOURCE_CODE.
     *
     * @param string $sourceCode
     * @return Individu
     */
    public function findOneBySourceCode($sourceCode)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.sourceCode = :sourceCode')
            ->setParameter('sourceCode', $sourceCode);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Anomalie : plusieurs individus trouvés ayant le même source_code '%s'", $sourceCode),
                null,
                $e);
        }
    }

    /**
     * Recherche l'Individu correspondant à un Utilisateur, au sein d'un Etablissement.
     *
     * @param UserWrapper   $userWrapper
     * @param Etablissement $etablissement
     * @return Individu|null
     */
    public function findOneByUserWrapperAndEtab(UserWrapper $userWrapper, Etablissement $etablissement)
    {
        // C'est le "supann{Emp|Etu}Id" présent dans les données d'authentification qu'on utilise dans le source_code
        $supannId = $userWrapper->getSupannId();

        if ($supannId === null) {
            // Si aucun supannId n'est dispo dans les données d'authentification, on colle l'EPPN dans
            // le source_code de l'individu (ex: "UCN::tartempion@unicaen.fr", "INCONNU::machin@unicaen.fr")
            // ce qui permettra si besoin de retrouver l'individu dans le cas où il se reconnecterait plus tard
            // et qu'un un supannId soit dispo dans les données d'authentification.
            $value = $userWrapper->getEppn();
        } else {
            $value = $supannId;
        }

        $qb = $this->createQueryBuilder('i')
            ->where('i.supannId = :supannId')
            ->andWhere('i.etablissement = :etab')
            ->setParameters(['supannId' => $value, 'etab' => $etablissement]);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Anomalie : plusieurs individus trouvés pour l'établissement '%s' et le supannId '%s'", $etablissement, $value),
                null,
                $e);
        }
    }

    /**
     * Recherche d'individu, en SQL pure.
     *
     * @param string  $text
     * @param string  $type (doctorant, acteur, ...)
     * @param integer $limit
     *
     * @return array
     */
    public function findByText($text, $type = null, $limit = 100)
    {
        if (strlen($text) < 2) return [];

        $text = Util::reduce($text);
        $criteres = explode(' ', $text);

        $sqlTemplate =
            "SELECT * FROM INDIVIDU i " .
            "JOIN INDIVIDU_RECH ir on ir.id = i.id " .
            "WHERE i.HISTO_DESTRUCTION IS NULL AND rownum <= %d";
        if ($type !== null) {
            $sqlTemplate .= " AND i.type = '%s'";
            $sql = sprintf($sqlTemplate, $type, (int)$limit);
            $tmp = null;
        } else {
            $sql = sprintf($sqlTemplate, (int)$limit);
        }

        $sqlCri = [];
        foreach ($criteres as $c) {
            $sqlCri[] = "ir.haystack LIKE LOWER(q'[%" . $c . "%]')"; // q'[] : double les quotes
        }
        $sqlCri = implode(' AND ', $sqlCri);

        $orc = [];
        if ($sqlCri !== '') {
            $orc[] = '(' . $sqlCri . ')';
        }
        $orc = implode(' OR ', $orc);

        $sql .= ' AND (' . $orc . ') ';

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur rencontrée dans la requête de recherche d'individu", null, $e);
        }

        return $stmt->fetchAll();
    }

    public function findByRole($role)
    {
        $repo = $this->getEntityManager()->getRepository(IndividuRole::class);
        $qb = $repo->createQueryBuilder("ir")
            ->join(Individu::class, "in")
            ->andWhere("ir.role = :role")
            ->setParameter("role", $role);
        $query = $qb->getQuery();
        /** @var IndividuRole[] $res */
        $res = $query->execute();

        return $res;
    }
}