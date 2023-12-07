<?php

namespace Substitution\Service\Doublon\Individu;

use Application\Service\BaseService;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuService;
use Substitution\Entity\Db\DoublonAwareEntityInterface;
use Substitution\Service\Doublon\SpecificDoublonAbstractService;

class IndividuDoublonService extends SpecificDoublonAbstractService
{
    protected function generateSqlToFindAllDoublons(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, d.nom_patronymique, d.prenom1, to_char(d.date_naissance,'DD/MM/YYYY') date_naissance
from v_individu_doublon d
join individu pre on pre.id = d.id and pre.histo_destruction is null
left join substit_individu sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- sans substitution 
order by d.npd, d.nom_patronymique, d.prenom1, d.date_naissance
EOT;
    }
}