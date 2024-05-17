<?php

namespace StepStar\Service\Log\Recherche;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Doctorant\Search\DoctorantSearchFilter;
use Doctrine\ORM\QueryBuilder;
use StepStar\Entity\Db\Log;
use StepStar\Service\Log\LogServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;

class LogSearchService extends SearchService
{
    use LogServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function init(): void
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
                ->setLabel("Étab. Thèse")
                ->setWhereField('etab_structure.code')
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
                    Log::OPERATION__SYNTHESE => Log::OPERATION__SYNTHESE,
                ])
        );
        $this->addFilter(
            (new SelectSearchFilter("Succès ?", 'success'))
                ->setWhereField('log.success')
                ->setData([
                    '1' => 'Oui',
                    '0' => 'Non',
                ])
        );
        $this->addFilter(
            (new StrReducedTextSearchFilter("Log", 'log'))
                ->useLikeOperator()
                ->setWhereField('log.log')
        );
        $this->addFilter(
            (new StrReducedTextSearchFilter("Tag", 'tag'))
                ->useLikeOperator()
                ->setWhereField('log.tag')
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
            ->leftJoin('these.etablissement', 'etab')
            ->leftJoin('etab.structure', 'etab_structure')
            ->orderBy('log.id', 'desc');
    }

    public function fetchEtablissements(): array
    {
        $etablissements = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
        usort($etablissements, fn(Etablissement $a, Etablissement $b) => $a->getStructure()->getSourceCode() <=> $b->getStructure()->getSourceCode());

        return $etablissements;
    }
}