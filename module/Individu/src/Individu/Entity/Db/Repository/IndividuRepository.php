<?php

namespace Individu\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\Entity\UserWrapper;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Laminas\Mvc\Controller\AbstractActionController;
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
        /** @var Individu $i */
        $i = $this->findOneBy(['sourceCode' => $sourceCode]);

        return $i;
    }

    /**
     * Recherche l'Individu correspondant à un Utilisateur, au sein d'un Etablissement.
     *
     * @param UserWrapper   $userWrapper
     * @param Etablissement $etablissement
     * @return Individu
     */
    public function findOneByUserWrapperAndEtab(UserWrapper $userWrapper, Etablissement $etablissement)
    {
        // C'est l'identifiant trouvé dans le "supann{Ref|Emp|Etu}Id" présent dans les données d'authentification
        // qui nous permet de trouver l'Individu correspodant à l'Utilisateur.
        $supannId = $userWrapper->getSupannId();
        if (! $supannId) {
            // Si on a rien à se mettre sous la dent, on ne cherche pas plus loin !
            return null;
        }

        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($supannId, $etablissement);

        return $this->findOneBySourceCode($sourceCode);
    }

    /**
     * Recherche textuelle d'individus à l'aide de la table INDIVIDU_RECH, en SQL pure.
     *
     * @param string $text
     * @param string|null $type (doctorant, acteur, ...)
     * @param integer $limit
     *
     * @return Individu[]
     */
    public function findByText(string $text, ?string $type = null, int $limit = 100): array
    {
        if (strlen($text) < 2) return [];

        $text = Util::reduce($text);
        $criteres = explode(' ', $text);

        $sql =
            "SELECT * FROM INDIVIDU i " .
            "JOIN INDIVIDU_RECH ir on ir.id = i.id " .
            "WHERE i.HISTO_DESTRUCTION IS NULL";
        if ($type !== null) {
            $sql .= sprintf(" AND i.type = '%s'", $type);
            $tmp = null;
        }

        $sqlCri = [];
        foreach ($criteres as $c) {
            //$sqlCri[] = "ir.haystack LIKE LOWER(q'[%" . $c . "%]')"; // q'[] : double les quotes
            $sqlCri[] = "ir.haystack LIKE str_reduce($$%" . $c . "%$$)";
        }
        $sqlCri = implode(' AND ', $sqlCri);

        $orc = [];
        if ($sqlCri !== '') {
            $orc[] = '(' . $sqlCri . ')';
        }
        $orc = implode(' OR ', $orc);

        $sql .= ' AND (' . $orc . ') ';

        $sql .= sprintf(" LIMIT %d", (int)$limit);

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (Exception $e) {
            throw new RuntimeException(
                "Erreur rencontrée lors de l'exécution de la requête de recherche d'individu", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche d'individu", null, $e);
        }
    }

    /**
     * @param Role|string $role Rôle ou code du rôle
     * @return Individu[]
     */
    public function findByRole($role)
    {
        $repo = $this->getEntityManager()->getRepository(IndividuRole::class);
        $qb = $repo->createQueryBuilder("ir")
            ->addSelect('r, i')
            ->join('ir.individu', 'i')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1');

        if ($role instanceof Role) {
            $qb
                ->join('ir.role', 'r', Join::WITH, 'r = :role')
                ->setParameter('role', $role);
        } else {
            $qb
                ->join('ir.role', 'r', Join::WITH, 'r.code = :code')
                ->setParameter('code', $role);
        }


        /** @var IndividuRole[] $res */
        $res = $qb->getQuery()->execute();

        return array_map(function(IndividuRole $ir) {
            return $ir->getIndividu();
        }, $res);
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Individu|null
     */
    public function findRequestedIndividu(AbstractActionController $controller, string $param='individu') : ?Individu
    {
        $individuId = $controller->params()->fromRoute($param);
        /** @var Individu|null $individu */
        $individu = $this->find($individuId);

        return $individu;
    }
}