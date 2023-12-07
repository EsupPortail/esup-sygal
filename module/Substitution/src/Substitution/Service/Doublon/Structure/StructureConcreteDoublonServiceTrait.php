<?php

namespace Substitution\Service\Doublon\Structure;

use Substitution\TypeAwareTrait;

trait StructureConcreteDoublonServiceTrait
{
    use TypeAwareTrait;

    protected function generateSqlToFindAllDoublons(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, pres.libelle
from v_{$this->type}_doublon d
join {$this->type} pre on d.id = pre.id and pre.histo_destruction is null
join structure pres on pre.structure_id = pres.id and pres.histo_destruction is null
left join substit_{$this->type} sub on d.id = sub.from_id
where sub.id is null /* aucune substitution existante */
order by d.npd, pres.libelle
EOT;
    }
}