<?php

namespace StepStar\Service\Fetch;

use DateInterval;
use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use These\QueryBuilder\TheseQueryBuilder;
use These\Service\These\TheseServiceAwareTrait;

class FetchService
{
    use TheseServiceAwareTrait;

    /**
     * @var string[]
     */
    private array $criteriaToStrings;

    /**
     * @return string[]
     */
    public function getCriteriaToStrings(): array
    {
        return $this->criteriaToStrings;
    }

    /**
     * Fetch et hydrate au format array une thèse spécifiée par son id,
     * avec toutes les jointures requises pour l'export XML.
     *
     * @param int $theseId Id de la thèse concernée
     * @return array Thèse trouvée *hydratée au format array*
     */
    public function fetchTheseById(int $theseId): array
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->where('t.id = :id')
            ->setParameter('id', $theseId);

        $theses = $qb->getQuery()->getArrayResult();

        if (empty($theses)) {
            throw new RuntimeException("Thèse introuvable avec l'id spécifié");
        }

        return $theses[0];
    }

    /**
     * Recherche et hydrate au format array les thèses répondant aux critères spécifiés,
     * avec toutes les jointures requises pour l'export XML.
     *
     * @param array $criteria
     * @return array[]
     */
    public function fetchThesesByCriteria(array $criteria): array
    {
        if (empty($criteria)) {
            throw new InvalidArgumentException("Une liste de critères vide n'est pas acceptée");
        }

        $qb = $this->createQueryBuilder();
        $this->applyCriteriaToQb($criteria, $qb);

        return $qb->getQuery()->getArrayResult();
    }

    private function dateFromSpec(string $dateSpec): string
    {
        // La contrainte de date peut au choix :
        //   - être de la forme 'AAAA-MM-DD' (rien à faire) ;
        //   - commencer par 'P', '+P' ou '-P' auquel cas on construira un DateInterval.
        //     ex : '+P6M' ou 'P6M' est traduit en "date du jour + 6 mois"
        //     ex : '-P6M' est traduit en "date du jour - 6 mois"

        $isInterval = str_starts_with($dateSpec, 'P') || str_starts_with($dateSpec, '+P') || str_starts_with($dateSpec, '-P');

        if (!$isInterval) {
            return $dateSpec;
        }

        if (str_starts_with($dateSpec, 'P')) {
            $method = 'add';
        } elseif (str_starts_with($dateSpec, '+P')) {
            $dateSpec = substr($dateSpec, 1);
            $method = 'add';
        } else {
            $dateSpec = substr($dateSpec, 1);
            $method = 'sub';
        }

        try {
            $period = new DateInterval($dateSpec);
        } catch (Exception $e) {
            throw new InvalidArgumentException("La valeur '$dateSpec' ne permet pas de construire un DateInterval", null, $e);
        }

        return (new DateTime('today'))->$method($period)->format('Y-m-d');
    }

    /**
     * Création du QB avec les jointures nécessaires.
     *
     * @return \These\QueryBuilder\TheseQueryBuilder
     */
    private function createQueryBuilder(): TheseQueryBuilder
    {
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb
            ->addSelect('e, d, di, disc, dip, dimc, ed, ur, f, orf, m, rdv, mel, es, eds, urs, a, ai, r')
            ->join('t.etablissement', 'e')
            ->join('t.doctorant', 'd')
            ->join('d.individu', 'di')
            ->join('e.structure', 'es')
            ->leftJoin('t.discipline', 'disc')
            ->leftJoin('di.paysNationalite', 'dip')
            ->leftJoin('di.mailsConfirmations', 'dimc')
            ->leftJoin('t.ecoleDoctorale', 'ed')
            ->leftJoin('t.uniteRecherche', 'ur')
            ->leftJoin('t.financements', 'f', Join::WITH, 'f.histoDestruction is null')
            ->leftJoin('f.origineFinancement', 'orf', Join::WITH, 'orf.histoDestruction is null')
            ->leftJoin('t.metadonnees', 'm')
            ->leftJoin('t.rdvBus', 'rdv', Join::WITH, 'rdv.histoDestruction is null')
            ->leftJoin('t.miseEnLignes', 'mel', Join::WITH, 'mel.histoDestruction is null')
            ->leftJoin('ed.structure', 'eds')
            ->leftJoin('ur.structure', 'urs')
            ->leftJoin('t.acteurs', 'a', Join::WITH, 'a.histoDestruction is null')
            ->leftJoin('a.individu', 'ai', Join::WITH, 'ai.histoDestruction is null')
            ->leftJoin('a.role', 'r', Join::WITH, 'r.histoDestruction is null')
            ->andWhereNotHistorise()
            ->orderBy('es.sourceCode, t.id');

        try {
            $qb->indexBy('t', 't.id');
        } catch (QueryException $e) {
            throw new LogicException("Requête invalide", null, $e);
        }

        return $qb;
    }

    private function applyCriteriaToQb(array $criteria, QueryBuilder $qb): void
    {
        $these = $criteria['these'] ?? null; // ex : '12345' ou '12345,12346'
        $etat = $criteria['etat'] ?? null; // ex : 'E' ou 'E,S'
        $dateSoutenanceNull = $criteria['dateSoutenanceNull'] ?? false;
        $dateSoutenanceMin = $criteria['dateSoutenanceMin'] ?? null; // ex : '2022-03-11' ou '+P6M' ou '-P7D'
        $dateSoutenanceMax = $criteria['dateSoutenanceMax'] ?? null; // idem
        $etablissement = $criteria['etablissement'] ?? null; // ex : 'UCN' ou 'UCN,URN'

        $this->criteriaToStrings = [];

        if ($these !== null) {
            $thesesIds = array_map('trim', explode(',', $these));
            $qb->where($qb->expr()->in('t.id', $thesesIds));
            $this->criteriaToStrings[] = 'Ids thèses : ' . $these;
        } else {
            if ($etat !== null) {
                $etats = array_map('trim', explode(',', $etat));
                $qb->andWhereEtatIn($etats);
                $this->criteriaToStrings[] = 'Etats thèses : ' . $etat;
            }
            if ($dateSoutenanceNull) {
                $qb->andWhere('t.dateSoutenance is null');
                $this->criteriaToStrings[] = 'Date de soutenance : null';
            } else {
                if ($dateSoutenanceMin !== null) {
                    $qb
                        ->andWhere('t.dateSoutenance >= :dateSoutMin')
                        ->setParameter('dateSoutMin', $dateSoutMin = $this->dateFromSpec($dateSoutenanceMin));
                    $this->criteriaToStrings[] = 'Date de soutenance >= ' . $dateSoutMin;
                }
                if ($dateSoutenanceMax !== null) {
                    $qb
                        ->andWhere('t.dateSoutenance <= :dateSoutMax')
                        ->setParameter('dateSoutMax', $dateSoutMax = $this->dateFromSpec($dateSoutenanceMax));
                    $this->criteriaToStrings[] = 'Date de soutenance <= ' . $dateSoutMax;
                }
            }
            if ($etablissement !== null) {
                $codesEtabs = array_map('trim', explode(',', $etablissement));
                $qb->andWhere($qb->expr()->in('es.sourceCode', $codesEtabs));
                $this->criteriaToStrings[] = 'Etablissements : ' . $etablissement;
            }
        }
    }
}