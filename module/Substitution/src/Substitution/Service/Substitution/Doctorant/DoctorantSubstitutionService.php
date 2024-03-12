<?php

namespace Substitution\Service\Substitution\Doctorant;

use Application\Service\BaseService;
use Doctrine\DBAL\Exception;
use Substitution\Constants;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Service\Substitution\SpecificSubstitutionAbstractService;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class DoctorantSubstitutionService extends SpecificSubstitutionAbstractService
{
    protected string $type = Constants::TYPE_doctorant;

    /**
     * @var \Doctorant\Service\DoctorantService
     */
    protected BaseService $entityService;

    /**
     * Recherche textuelle de doctorants à l'aide de la table INDIVIDU_RECH, en SQL pur.
     */
    protected function findEntitiesByText(string $text, string $npd, int $limit = 0): array
    {
        if (strlen($text) < 2) return [];

        $text = Util::reduce($text);
        $criteres = explode(' ', $text);

        $sql =
            "SELECT i.*, " .
            "       t.* /* doit absolument être après */, " .
            "       src.libelle    as source_libelle, " .
            "       src.importable as source_importable " .
            "FROM DOCTORANT t " .
            "JOIN INDIVIDU i on t.individu_id = i.id " .
            "JOIN INDIVIDU_RECH ir on ir.id = i.id " .
            "JOIN SOURCE src on src.id = t.source_id and src.importable = true " . // enregistrement importé
            "WHERE t.HISTO_DESTRUCTION IS NULL ";

        // autres conditions pour être substituable
        $ors = implode(' OR ', [
            // S'il n'est pas historisé et n'a pas de NPD forcé : il est substituable.
            't.histo_destruction is null and t.npd_force is null',
            // S'il est historisé et s'il a un NPD forcé et qu'il diffère du NPD de la substitution,
            // c'est un enregistrement déclaré faux-doublon manuellement : il est substituable.
            "t.npd_force is not null and t.npd_force <> $$" . $npd . "$$",
        ]);
        $sql .= ' AND (' . $ors . ') ';

        $sqlCri = [];
        foreach ($criteres as $c) {
            $sqlCri[] = "(concat(ir.haystack, ' ', t.ine)) LIKE str_reduce($$%" . $c . "%$$)";
        }
        $sqlCri = implode(' AND ', $sqlCri);

        $sql .= ' AND (' . $sqlCri . ') ';

        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        try {
            $stmt = $this->entityService->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (Exception $e) {
            throw new RuntimeException(
                "Erreur rencontrée lors de l'exécution de la requête de recherche de doctorant", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche de doctorant", null, $e);
        }
    }

    protected function formatSubstituables(array $substituables): array
    {
        $result = [];
        foreach ($substituables as $row) {
            // mise en forme attendue par l'aide de vue FormSearchAndSelect
            $label = $row['nom_usuel'] . ' ' . $row['prenom1'];
            $extra = implode(', ', array_filter([
                $row['ine'] ?? null,
                $row['email'] ?: $row['source_code']
            ]));
            $result[] = array(
                'id' => $row['id'], // identifiant unique de l'item
                'label' => $label,  // libellé de l'item
                'extra' => $extra,  // infos complémentaires (facultatives) sur l'item
            );
        }
        usort($result, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $result;
    }

    public function saveEntity(SubstitutionAwareEntityInterface $entity): void
    {
        /** @var \Doctorant\Entity\Db\Doctorant $entity */
        $this->entityService->saveDoctorant($entity);
    }

    public function generateSqlToFindSubstitutionsBySubstituant(?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    x.ine as ine,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(substit_npd_doctorant(px.*), '|' order by sub.histo_creation, from_id) as from_npd_calcs,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from substit_doctorant sub
    join doctorant px on px.id = sub.from_id
    join source src on src.id = px.source_id
    join doctorant x on x.id = sub.to_id
$andWhereToId
group by
    x.id,
    x.ine,
    sub.npd
order by x.ine
EOT;
    }

    public function getEntityNpdAttributes(): array
    {
        return [
            'ine' => 'INE',
        ];
    }
}