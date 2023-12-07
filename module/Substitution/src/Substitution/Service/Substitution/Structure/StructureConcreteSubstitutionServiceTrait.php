<?php

namespace Substitution\Service\Substitution\Structure;

use Doctrine\ORM\Query\Expr\Join;

trait StructureConcreteSubstitutionServiceTrait
{
    protected string $type;

    protected function findEntitiesByText(string $text, string $npd, int $limit = 0): array
    {
        if (strlen($text) < 2) return [];

        $qb = $this->entityService->getRepository()->createQueryBuilder("t");

        // il doit s'agir d'un enregistrement importé
        $qb->andWhere('src.importable = true');

        // autres conditions pour être substituable
        $qb->andWhere($qb->expr()->orX(
            // S'il n'est pas historisé et n'a pas de NPD forcé : il est substituable.
            't.histoDestruction is null and t.npdForce is null',
            // S'il est historisé et s'il a un NPD forcé et qu'il diffère du NPD de la substitution,
            // c'est un enregistrement déclaré faux-doublon manuellement : il est substituable.
            't.npdForce is not null and t.npdForce <> :npd',
        ))->setParameter('npd', $npd);

        foreach (array_filter(explode(' ', $text)) as $term) {
            $paramName = uniqid('p_');
            $qb->andWhere($qb->expr()->orX(
                "strReduce(structure.libelle) LIKE strReduce(:$paramName)",
                "strReduce(structure.sigle)   LIKE strReduce(:$paramName)",
            ));
            $qb->setParameter($paramName, '%' . trim($term) . '%');
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getArrayResult();
    }

    protected function formatSubstituables(array $substituables): array
    {
        $result = [];
        foreach ($substituables as $row) {
            // mise en forme attendue par l'aide de vue FormSearchAndSelect
            $label = $row['structure']['libelle'];
            $extra = $row['structure']['sigle'] ?? null;
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

    public function generateSqlToFindSubstitutions(?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    xs.code as code,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(substit_npd_{$this->type}(px.*), '|' order by sub.histo_creation, from_id) as from_npd_calcs,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from substit_{$this->type} sub
    join {$this->type} px on px.id = sub.from_id
    join {$this->type} x on x.id = sub.to_id
    join source src on src.id = px.source_id
    join structure xs on xs.id = x.structure_id
$andWhereToId
group by
    x.id,
    xs.code,
    sub.npd
order by x.id
EOT;
    }

    public function getEntityNpdAttributes(): array
    {
        return [
            'code' => 'Code',
        ];
    }
}