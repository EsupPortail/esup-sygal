<?php

namespace Substitution\Service;

use Doctrine\DBAL\Result;
use Substitution\Constants;
use UnicaenApp\Service\EntityManagerAwareTrait;

class DoublonService
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
    public function countAllDoublonsIndividu(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublonsIndividu() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsIndividu(): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublonsIndividu()
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublonsDoctorant(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublonsDoctorant() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsDoctorant(): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublonsDoctorant()
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublonsStructure(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublonsStructureAbstraite() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsStructure(): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublonsStructureAbstraite()
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublonsEtablissement(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublonsStructureConcrete('etablissement') . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsEtablissement(): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublonsStructureConcrete('etablissement')
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublonsUniteRech(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublonsStructureConcrete('ecole_doct') . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsEcoleDoct(): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublonsStructureConcrete('ecole_doct')
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublonsEcoleDoct(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublonsStructureConcrete('unite_rech') . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsUniteRech(): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublonsStructureConcrete('unite_rech')
        );
    }


    //------------------------------------------------------------------------------------------------------------
    // SQL doublons non substitués
    //------------------------------------------------------------------------------------------------------------

    private function generateSqlToFindAllDoublonsIndividu(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, d.nom_patronymique, d.prenom1, to_char(d.date_naissance,'DD/MM/YYYY') date_naissance
from v_individu_doublon d
join {$this->tablePrefix}individu pre on pre.id = d.id and pre.histo_destruction is null
left join individu_substit sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- sans substitution 
order by d.npd, d.nom_patronymique, d.prenom1, d.date_naissance
limit 100
EOT;
    }

    private function generateSqlToFindAllDoublonsDoctorant(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, pre.ine
from v_doctorant_doublon d
join {$this->tablePrefix}doctorant pre on pre.id = d.id and pre.histo_destruction is null
left join doctorant_substit sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- sans substitution 
order by d.npd, pre.ine
limit 100
EOT;
    }

    private function generateSqlToFindAllDoublonsStructureAbstraite(): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, d.code
from v_structure_doublon d
join {$this->tablePrefix}structure pre on pre.id = d.id and pre.histo_destruction is null
left join structure_substit sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- aucune substitution existante
order by d.npd, d.code
limit 100
EOT;
    }

    private function generateSqlToFindAllDoublonsStructureConcrete(string $type): string
    {
        return <<<EOT
select d.npd, d.id, pre.source_code, pres.libelle
from v_{$type}_doublon d
join {$this->tablePrefix}{$type} pre on d.id = pre.id and pre.histo_destruction is null
join {$this->tablePrefix}structure pres on pre.structure_id = pres.id and pres.histo_destruction is null
left join {$type}_substit sub on d.id = sub.from_id --and sub.histo_destruction is null
where sub.id is null -- aucune substitution existante
order by d.npd, pres.libelle
limit 100
EOT;
    }
}