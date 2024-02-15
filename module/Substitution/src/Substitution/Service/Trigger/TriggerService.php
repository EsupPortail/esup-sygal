<?php

namespace Substitution\Service\Trigger;

use Doctrine\DBAL\Result;
use UnicaenApp\Service\EntityManagerAwareTrait;

class TriggerService
{
    use EntityManagerAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllTriggersForType(string $type): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllTriggers($type)
        );
    }

    private function generateSqlToFindAllTriggers(string $type): string
    {
        return <<<EOS
select 
--        event_object_schema as table_schema,
       event_object_table as table_name,
--        trigger_schema,
       trigger_name,
       string_agg(event_manipulation, ', ') as event,
       action_timing as activation,
--        action_condition as condition,
--        action_statement as definition,
       tgenabled <> 'D' enabled
from information_schema.triggers
join pg_trigger on tgname = trigger_name
where trigger_name in ('substit_trigger_{$type}', 'substit_trigger_on_substit_{$type}')
group by 1,2,4,5
order by trigger_name;
EOS;
    }
}