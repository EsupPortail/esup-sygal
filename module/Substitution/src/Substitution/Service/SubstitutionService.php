<?php

namespace Substitution\Service;

use Doctrine\DBAL\Result;
use Substitution\Constants;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SubstitutionService
{
    use EntityManagerAwareTrait;

    private string $tablePrefix = '';

    public function __construct()
    {
        if (Constants::USE_TABLE_PREFIX) {
            $this->tablePrefix = 'pre_';
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsIndividu(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllSubstitutionsIndividu() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsIndividu(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsIndividu() . " limit $limit"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionIndividu(int $substituantId): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsIndividu($substituantId)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsDoctorant(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllSubstitutionsDoctorant() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsDoctorant(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsDoctorant() . " limit $limit"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionDoctorant(int $substituantId): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsDoctorant($substituantId)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsStructure(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllSubstitutionsStructureAbstraite() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsStructure(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureAbstraite() . " limit $limit"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionStructure(int $substituantId): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureAbstraite($substituantId)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsEtablissement(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllSubstitutionsStructureConcrete('etablissement') . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsEtablissement(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureConcrete('etablissement') . " limit $limit"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionEtablissement(int $substituantId): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureConcrete('etablissement', $substituantId)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsEcoleDoct(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllSubstitutionsStructureConcrete('ecole_doct') . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsEcoleDoct(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureConcrete('ecole_doct') . " limit $limit"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionEcoleDoct(int $substituantId): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureConcrete('ecole_doct', $substituantId)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsUniteRech(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllSubstitutionsStructureConcrete('unite_rech') . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsUniteRech(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureConcrete('unite_rech') . " limit $limit"
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionUniteRech(int $substituantId): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllSubstitutionsStructureConcrete('unite_rech', $substituantId)
        );
    }


    //------------------------------------------------------------------------------------------------------------
    // SQL substitutions
    //------------------------------------------------------------------------------------------------------------

    public function generateSqlToFindAllSubstitutionsIndividu(?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    x.nom_patronymique as to_nom_patronymique,
    x.prenom1 as to_prenom1,
    date(x.date_naissance) as to_date_naissance,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from individu_substit sub
    join {$this->tablePrefix}individu px on px.id = sub.from_id
    join source src on src.id = px.source_id
    join individu x on x.id = sub.to_id
--where sub.histo_destruction is null
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

    public function generateSqlToFindAllSubstitutionsDoctorant(?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    x.ine as to_ine,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from doctorant_substit sub
    join {$this->tablePrefix}doctorant px on px.id = sub.from_id
    join source src on src.id = px.source_id
    join doctorant x on x.id = sub.to_id
--where sub.histo_destruction is null
$andWhereToId
group by
    x.id,
    x.ine,
    sub.npd
order by x.ine
EOT;
    }

    public function generateSqlToFindAllSubstitutionsStructureAbstraite(?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    x.code as to_code,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from structure_substit sub
    join {$this->tablePrefix}structure px on px.id = sub.from_id
    join source src on src.id = px.source_id
    join structure x on x.id = sub.to_id
--where sub.histo_destruction is null
$andWhereToId
group by
    x.id,
    x.code,
    sub.npd
order by x.code
EOT;
    }

    private function generateSqlToFindAllSubstitutionsStructureConcrete(string $type, ?int $substituantId = null): string
    {
        $andWhereToId = $substituantId ? 'where sub.to_id = ' . $substituantId : null;
        return <<<EOT
select distinct
    x.id as to_id,
    x.est_substituant_modifiable,
    xs.code as to_code,
    sub.npd,
    string_agg(to_char(sub.histo_creation,'DD/MM/YYYY HH24:MI:SS'), '|' order by sub.histo_creation, from_id) as from_dates_creations,
    string_agg(sub.from_id::varchar, '|' order by sub.histo_creation, from_id) as from_ids,
    string_agg(src.code, '|' order by sub.histo_creation, from_id) as from_sources,
    string_agg(coalesce(px.npd_force, ''), '|' order by sub.histo_creation, from_id) as from_npd_forces
from {$type}_substit sub
    join {$this->tablePrefix}{$type} px on px.id = sub.from_id
    join {$type} x on x.id = sub.to_id
    join source src on src.id = px.source_id
    join structure xs on xs.id = x.structure_id
--where sub.histo_destruction is null
$andWhereToId
group by
    x.id,
    xs.code,
    sub.npd
order by x.id
EOT;
    }
}