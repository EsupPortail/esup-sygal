<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\UserWrapper;
use Application\SourceCodeStringHelper;
use Doctrine\DBAL\DBALException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class IndividuRepository extends DefaultEntityRepository
{
    /**
     * @param int $id
     * @return Individu
     */
    public function find($id) {

        /** @var Individu $individu */
        $individu = $this->findOneBy(["id"=>$id]);
        return $individu;
    }

    /**
     * @param string $sourceCode
     * @return Individu
     */
    public function findOneBySourceCode($sourceCode)
    {
        /** @var Individu $i */
        $i = $this->findOneBy(['sourceCode' => $sourceCode]);

        return $i;
    }

    /**
     * @param UserWrapper   $userWrapper
     * @param Etablissement $etablissement
     * @return Individu
     */
    public function findOneByUserWrapperAndEtab(UserWrapper $userWrapper, Etablissement $etablissement)
    {
        $sourceCodeHelper = new SourceCodeStringHelper();
        $sourceCode = $sourceCodeHelper->addPrefixEtablissementTo($userWrapper->getSupannId(), $etablissement);

        return $this->findOneBySourceCode($sourceCode);

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

        $sql = sprintf('SELECT * FROM INDIVIDU i JOIN INDIVIDU_RECH ir on ir.id = i.id WHERE i.HISTO_DESTRUCTION IS NULL AND rownum <= %s ', (int)$limit);
        if ($type !== null) {
            $sql = sprintf('SELECT * FROM INDIVIDU i JOIN INDIVIDU_RECH ir on ir.id = i.id WHERE i.HISTO_DESTRUCTION IS NULL AND  i.type = \'%s\' AND  rownum <= %s ', $type, (int)$limit);
            $tmp = null;
        }
        $sqlCri  = [];

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
            -> join (Individu::class, "in")
            -> andWhere("ir.role = :role")
            ->setParameter("role", $role)
        ;
        $query = $qb->getQuery();
        /** @var IndividuRole[] $res */
        $res = $query->execute();

        return $res;
    }
}