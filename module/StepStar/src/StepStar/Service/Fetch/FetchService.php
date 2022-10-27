<?php

namespace StepStar\Service\Fetch;

use These\QueryBuilder\TheseQueryBuilder;
use These\Service\These\TheseServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\QueryException;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class FetchService
{
    use TheseServiceAwareTrait;

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

        $these = $criteria['these'] ?? null; // ex : '12345' ou '12345,12346'
        $etat = $criteria['etat'] ?? null; // ex : 'E' ou 'E,S'
        $etablissement = $criteria['etablissement'] ?? null; // ex : 'UCN' ou 'UCN,URN'

        $qb = $this->createQueryBuilder();

        if ($these !== null) {
            $thesesIds = array_map('trim', explode(',', $these));
            $qb->where($qb->expr()->in('t.id', $thesesIds));
        } else {
            if ($etat !== null) {
                $etats = array_map('trim', explode(',', $etat));
                $qb->andWhereEtatIn($etats);
            }
            if ($etablissement !== null) {
                $codesEtabs = array_map('trim', explode(',', $etablissement));
                $qb->andWhere($qb->expr()->in('es.code', $codesEtabs));
            }
        }

        return $qb->getQuery()->getArrayResult();
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
            ->addSelect('e, d, di, dip, ed, ur, f, orf, m, rdv, mel, es, eds, urs, a, ai, r')
            ->join('t.etablissement', 'e')
            ->join('t.doctorant', 'd')
            ->join('d.individu', 'di')
            ->join('e.structure', 'es')
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
            ->orderBy('es.code, t.id');

        $qb
            ->addSelect('et_ss')->leftJoin('es.structureSubstituante', 'et_ss')
            ->addSelect('ed_ss')->leftJoin('eds.structureSubstituante', 'ed_ss')
            ->addSelect('ur_ss')->leftJoin('urs.structureSubstituante', 'ur_ss');

        try {
            $qb->indexBy('t', 't.id');
        } catch (QueryException $e) {
            throw new LogicException("Requête invalide", null, $e);
        }

        return $qb;
    }
}