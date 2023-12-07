<?php

namespace Substitution\Service\Doublon\Structure;

use Substitution\Service\Doublon\SpecificDoublonAbstractService;

class StructureDoublonService extends SpecificDoublonAbstractService
{
    protected function generateSqlToFindAllDoublons(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, d.code
from v_structure_doublon d
join structure pre on pre.id = d.id and pre.histo_destruction is null
left join substit_structure sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- aucune substitution existante
order by d.npd, d.code
EOT;
    }
}