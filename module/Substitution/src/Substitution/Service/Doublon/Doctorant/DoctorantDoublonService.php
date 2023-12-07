<?php

namespace Substitution\Service\Doublon\Doctorant;

use Substitution\Service\Doublon\SpecificDoublonAbstractService;

class DoctorantDoublonService extends SpecificDoublonAbstractService
{
    protected function generateSqlToFindAllDoublons(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, pre.ine
from v_doctorant_doublon d
join doctorant pre on pre.id = d.id and pre.histo_destruction is null
left join substit_doctorant sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- sans substitution 
order by d.npd, pre.ine
EOT;
    }

}