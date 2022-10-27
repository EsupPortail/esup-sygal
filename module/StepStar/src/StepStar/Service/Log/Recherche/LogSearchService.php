<?php

namespace StepStar\Service\Log\Recherche;

use These\Entity\Db\These;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Doctorant\Search\DoctorantSearchFilter;
use Doctrine\ORM\QueryBuilder;
use StepStar\Entity\Db\Log;
use StepStar\Service\Log\LogServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class LogSearchService extends SearchService
{
    use LogServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->addFilter(
            (new TextSearchFilter("Id Thèse", 'these'))
                ->setWhereField('these.id')
        );
        $this->addFilter(
            (new SelectSearchFilter("État Thèse", 'etatThese'))
                ->setWhereField('these.etatThese')
                ->setData([These::ETAT_EN_COURS => "En cours", These::ETAT_SOUTENUE => "Soutenue"])
        );
        $this->addFilter(
            EtablissementSearchFilter::newInstance()
                ->setDataProvider(fn() => $this->fetchEtablissements())
        );
        $this->addFilter(
            DoctorantSearchFilter::newInstance()
        );
        $this->addFilter(
            (new SelectSearchFilter("Opération", 'operation'))
                ->setWhereField('log.operation')
                ->setData([
                    Log::OPERATION__GENERATION_XML => Log::OPERATION__GENERATION_XML,
                    Log::OPERATION__ENVOI => Log::OPERATION__ENVOI,
                ])
        );
        $this->addFilter(
            (new SelectSearchFilter("Succès ?", 'success'))
                ->setWhereField('log.success')
                ->setData([
                    1 => 'Oui',
                    0 => 'Non',
                ])
        );
    }

    /**
     * @inheritDoc
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->logService->getRepository()->createQueryBuilder('log')
            ->leftJoin('log.these', 'these')
            ->leftJoin('these.doctorant', 'doctorant')
            ->orderBy('log.id', 'desc');
    }

    public function fetchEtablissements(): array
    {
        $etablissements = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
        usort($etablissements, fn(Etablissement $a, Etablissement $b) => $a->getStructure()->getCode() <=> $b->getStructure()->getCode());

        return $etablissements;
    }
}