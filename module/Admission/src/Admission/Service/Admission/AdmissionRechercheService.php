<?php

namespace Admission\Service\Admission;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etat;
use Admission\Search\Admission\EtatAdmissionSearchFilterAwareTrait;
use Application\Entity\Db\Role;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\TextCriteriaSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilterAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class AdmissionRechercheService extends SearchService
{
    use EtablissementInscSearchFilterAwareTrait;
    use UniteRechercheSearchFilterAwareTrait;
    use EcoleDoctoraleSearchFilterAwareTrait;
    use EtatAdmissionSearchFilterAwareTrait;

    use UserContextServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use StructureServiceAwareTrait;

    const NAME_etatAdmission = 'etat';

    const SORTER_NAME_titre = 'titreThese';
    const SORTER_NAME_individu = 'individu';

    /**
     * @var Role|null
     */
    private $role;

    /**
     * @inheritDoc
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->admissionService->getRepository()->createQueryBuilder('admission');
        $qb
            ->addSelect('etu')->leftJoin('admission.etudiant', 'etu')
            ->addSelect('inscr')->leftJoin('admission.inscription', 'inscr')
            ->addSelect('finan')->leftJoin('admission.financement', 'finan')
            ->addSelect('doc')->leftJoin('admission.document', 'doc')
            ->addSelect('individu')->leftJoin('admission.individu', 'individu')
            ->addSelect('compRat')->leftJoin('inscr.composanteDoctorat', 'compRat')
            ->addSelect('etab')->leftJoin('inscr.etablissementInscription', 'etab')
            ->addSelect('ed')->leftJoin('inscr.ecoleDoctorale', 'ed')
            ->addSelect('ed_struct')->leftJoin('ed.structure', 'ed_struct')
            ->addSelect('ur')->leftJoin('inscr.uniteRecherche', 'ur')
            ->addSelect('ur_struct')->leftJoin('ur.structure', 'ur_struct')
            ->addSelect('di')->leftJoin('inscr.directeur', 'di')
            ->addSelect('codir')->leftJoin('inscr.coDirecteur', 'codir')

            ->andWhereNotHistorise('admission');

        $role = $this->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();

        if($this->userContextService->getSelectedRoleDirecteurThese() || $role->getRoleId() == Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE){
            $qb->andWhere('inscr.directeur = :individuId')
                ->orWhere('inscr.directeur is null and (inscr.prenomDirecteurThese is not null or inscr.nomDirecteurThese is not null)')
                ->setParameter('individuId', $individu->getId());
        }

        if($this->userContextService->getSelectedRoleCodirecteurThese() || $role->getRoleId() == Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE){
            $qb->andWhere('inscr.coDirecteur = :individuId')
                ->setParameter('individuId', $individu->getId());
        }

        return $qb;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEtablissements();
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'admission') {
                $qb
                    ->andWhere($qb->expr()->orX('etab.sourceCode = :sourceCodeEtab'))
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });
        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEcolesDoctorales();
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'admission') {
                $qb
                    ->andWhere($qb->expr()->orX('ed.sourceCode = :sourceCodeED'))
                    ->setParameter('sourceCodeED', $filter->getValue());
            });
        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchUnitesRecherches();
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'admission') {
                $qb
                    ->andWhere($qb->expr()->orX('ur.sourceCode = :sourceCodeUR'))
                    ->setParameter('sourceCodeUR', $filter->getValue());
            });
        $etatAdmissionSearchFilter = $this->getEtatAdmissionSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEtatsAdmission();
            });
//        $textSearchFilter = $this->getTheseTextSearchFilter()
//            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
//                /** @var TextCriteriaSearchFilter $filter */
//                $this->applyTextFilterToQueryBuilder($filter, $qb, $alias);
//            });

        $this->addFilters([
            $etatAdmissionSearchFilter,
            $etablissementInscrFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
//            $textSearchFilter,
        ]);
        $this->addSorters([
            $this->createSorterEtablissement(),
            $this->createSorterEcoleDoctorale(),
            $this->createSorterUniteRecherche(),
            $this->createSorterTitre(),
            $etatAdmissionSearchFilter->createSorter(),
            $this->createSorterIndividu(),
        ]);
    }

    /**
     * @param TextCriteriaSearchFilter $filter
     * @param QueryBuilder $qb
     * @param string $alias
     */
    private function applyTextFilterToQueryBuilder(TextCriteriaSearchFilter $filter, QueryBuilder $qb, $alias = 'admission')
    {
        $filterValue = $filter->getTextValue();
        $criteria = $filter->getCriteriaValue();
        if (empty($criteria)) { // NB: aucun critère spécifié => texte pas pris en compte.
            return;
        }

        if ($filterValue !== null && strlen($filterValue) > 1) {
            $results = $this->findThesesSourceCodesByText($filterValue, $criteria);
            $sourceCodes = array_unique(array_keys($results));
            if ($sourceCodes) {
                $paramName = 'sourceCodes_' . $filter->getName();
                $qb
                    ->andWhere($qb->expr()->in("$alias.sourceCode", ":$paramName"))
                    ->setParameter($paramName, $sourceCodes);
            }
            else {
                $qb->andWhere("0 = 1"); // i.e. aucune thèse trouvée
            }
        }
    }

    /**
     * @param SearchSorter $sorter
     * @param QueryBuilder $qb
     */
    public function applySorterToQueryBuilder(SearchSorter $sorter, QueryBuilder $qb)
    {
        // todo: permettre la spécification de l'alias Doctrine à utiliser via $sorter->getAlias() ?
        $alias = 'admission';

        $name = $sorter->getName();
        $direction = $sorter->getDirection();

        switch ($name) {

            case self::SORTER_NAME_titre:
                // trim et suppression des guillemets
                $orderBy = "TRIM(REPLACE(inscr.$name, CHR(34), ''))"; // CHR(34) <=> "
                $qb->addOrderBy($orderBy, $direction);
                break;

            case self::SORTER_NAME_individu:
                $qb
                    ->addOrderBy('individu.nomUsuel', $direction)
                    ->addOrderBy('individu.prenom1', $direction);
                break;

            default:
                throw new \InvalidArgumentException("Cas imprévu");
        }
    }

    ////////////////////////////////// Fetch /////////////////////////////////////

    private function fetchEtatsAdmission(): array
    {
        return [
            $v = Etat::CODE_EN_COURS_SAISIE => Admission::$etatsLibelles[$v],
            $v = Etat::CODE_EN_COURS_VALIDATION => Admission::$etatsLibelles[$v],
            $v = Etat::CODE_VALIDE => Admission::$etatsLibelles[$v],
            $v = Etat::CODE_REJETE => Admission::$etatsLibelles[$v],
            $v = Etat::CODE_ABANDONNE => Admission::$etatsLibelles[$v],

        ];
    }

    private function fetchEtablissements(): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.sigle', true, true);
    }

    private function fetchUnitesRecherches(): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE, 'structure.code', true, true);
    }

    /**
     * @return Role|null
     */
    private function getSelectedIdentityRole(): ?Role
    {
        if ($this->role === null) {
            $this->role = $this->userContextService->getSelectedIdentityRole();
        }

        return $this->role;
    }

    /////////////////////////////////////// Sorters /////////////////////////////////////////

    /**
     * @return SearchSorter
     */
    public function createSorterEtablissement(): SearchSorter
    {
        $sorter = new SearchSorter("Établissement<br>d'inscr.", EtablissementSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('etab.sourceCode', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    public function createSorterEcoleDoctorale(): SearchSorter
    {
        $sorter = new SearchSorter("École doctorale", EcoleDoctoraleSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('ed_struct.sigle', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    public function createSorterUniteRecherche(): SearchSorter
    {
        $sorter = new SearchSorter("Unité recherche", UniteRechercheSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('ur_struct.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterTitre(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            AdmissionSorter::NAME_titre
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }

    /**
     * @return SearchSorter
     */
    private function createSorterIndividu(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            AdmissionSorter::NAME_individu
        );
        $sorter->setQueryBuilderApplier([$this, 'applySorterToQueryBuilder']);
        return $sorter;
    }
}
