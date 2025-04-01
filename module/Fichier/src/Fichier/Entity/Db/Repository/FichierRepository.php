<?php

namespace Fichier\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\DBAL\Exception;
use Individu\Entity\Db\Individu;
use UnicaenApp\Exception\RuntimeException;

class FichierRepository extends DefaultEntityRepository
{

    /**
     * Recherche textuelle d'individus à l'aide de la table INDIVIDU_RECH, en SQL pur.
     *
     * @param string $text
     * @param string|null $type Ex : 'doctorant'
     * @param integer $limit
     *
     * @return array[]
     */
    public function findByText(string $text, ?string $type = null, int $limit = 100): array
    {
        if (strlen($text) < 2) return [];

        $criteres = explode(' ', $text);

        $sql =
            "SELECT i.*, src.libelle as source_libelle, src.importable as source_importable " .
            "FROM INDIVIDU i " .
            "JOIN INDIVIDU_RECH ir on ir.id = i.id " .
            "JOIN SOURCE src on src.id = i.source_id " .
            "left join doctorant id on id.individu_id = i.id and id.histo_destruction is null " .
            "WHERE i.HISTO_DESTRUCTION IS NULL";

        if ($type !== null) {
            if ($type === Individu::TYPE_DOCTORANT) {
                $sql .= sprintf(" AND (i.type = '%s' or id.id is not null)", $type);
            } else {
                $sql .= sprintf(" AND i.type = '%s'", $type);
            }
        }

        $sqlCri = [];
        foreach ($criteres as $c) {
            $sqlCri[] = "ir.haystack LIKE str_reduce($$%" . $c . "%$$)";
        }
        $sqlCri = implode(' AND ', $sqlCri);

        $sql .= ' AND (' . $sqlCri . ') ';
        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (Exception $e) {
            throw new RuntimeException(
                "Erreur rencontrée lors de l'exécution de la requête de recherche d'individu", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche d'individu", null, $e);
        }
    }
}