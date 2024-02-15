<?php

namespace Substitution\Service\Substitution\Individu;

use Application\Service\BaseService;
use Doctrine\DBAL\Exception;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Service\Substitution\SpecificSubstitutionAbstractService;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class IndividuSubstitutionService extends SpecificSubstitutionAbstractService
{
    /**
     * @var \Individu\Service\IndividuService
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
            "SELECT t.*, " .
            "       src.libelle    as source_libelle, " .
            "       src.importable as source_importable " .
            "FROM INDIVIDU t " .
            "JOIN INDIVIDU_RECH ir on ir.id = t.id " .
            "JOIN SOURCE src on src.id = t.source_id and src.importable = true " . // enregistrement importé
            "left join doctorant d on d.individu_id = t.id " . // pour exclusion des individus doctorants
            "WHERE t.HISTO_DESTRUCTION IS NULL " .
            "and d.id is null"; // exclusion des individus doctorants (car substitutions dédiées)

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
            $sqlCri[] = "ir.haystack LIKE str_reduce($$%" . $c . "%$$)";
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
                "Erreur rencontrée lors de l'exécution de la requête de recherche d'individu", null, $e);
        }

        try {
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException(
                "Impossible d'obtenir les résultats de la requête de recherche d'individu", null, $e);
        }
    }

    protected function formatSubstituables(array $substituables): array
    {
        $result = [];
        foreach ($substituables as $row) {
            // mise en forme attendue par l'aide de vue FormSearchAndSelect
            $prenoms = implode(' ', array_filter([$row['prenom1'], $row['prenom2'], $row['prenom3']]));
            $label = $row['nom_usuel'] . ' ' . $prenoms;
            $extra = $row['email'] ?: $row['source_code'];
            $result[] = array(
                'id' => $row['id'], // identifiant unique de l'item
                'label' => $label,     // libellé de l'item
                'extra' => $extra,     // infos complémentaires (facultatives) sur l'item
            );
        }
        usort($result, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $result;
    }

    public function saveEntity(SubstitutionAwareEntityInterface $entity): void
    {
        /** @var \Individu\Entity\Db\Individu $entity */
        $this->entityService->saveIndividu($entity);
    }

    public function generateSqlToFindSubstitutions(?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    x.nom_patronymique as nom_patronymique,
    x.prenom1 as prenom1,
    date(x.date_naissance) as date_naissance,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(substit_npd_individu(px.*), '|' order by sub.histo_creation, from_id) as from_npd_calcs,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from substit_individu sub
    join individu px on px.id = sub.from_id
    join source src on src.id = px.source_id
    join individu x on x.id = sub.to_id
$andWhereToId
group by
    x.id,
    x.nom_patronymique,
    x.prenom1,
    x.date_naissance,
    sub.npd
order by x.nom_patronymique, x.prenom1
EOT;
    }

    public function getEntityNpdAttributes(): array
    {
        return [
            'nom_patronymique' => 'Nom patronymique',
            'prenom1' => 'Prénom 1',
            'date_naissance' => 'Date de naissance',
        ];
    }
}