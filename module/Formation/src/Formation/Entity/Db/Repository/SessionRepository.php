<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\Query\Expr\Join;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use These\Entity\Db\These;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionRepository extends DefaultEntityRepository
{
    use EntityManagerAwareTrait;

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy)
            ->join($alias . '.responsable', 'resp')->addSelect('resp')
            ->join($alias . '.formation', "formation")->addSelect("formation")
            ->join('formation.module', "module")->addSelect("module")
            ->join($alias. '.site', 'site')->addSelect("site")
            ->leftJoin($alias . '.typeStructure', 'struct')->addSelect("struct")
            ->leftJoin($alias . '.inscriptions', "inscription")->addSelect("inscription")
            ->leftJoin($alias . '.seances', 'seance')->addSelect("seance")
            ->leftJoin($alias . '.etat', 'etat')->addSelect("etat")
            ->leftJoin($alias . '.structuresValides', 'complement')->addSelect("complement")
            ->leftJoin("complement.structure", "structureSessionInscription")->addSelect("structureSessionInscription")
            ->andWhere("complement.histoDestruction IS NULL");

        $qb
            ->leftJoin('site.structure', 'site_structure')->addSelect('site_structure');

        return $qb;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Session|null
     */
    public function getRequestedSession(AbstractActionController $controller, string $param = 'session') : ?Session
    {
        $id = $controller->params()->fromRoute($param);
        $session = null;
        /** @var Session|null $session */
        if ($id !== null) $session = $this->find($id);
        return $session;
    }

    /**
     * @param Doctorant $doctorant
     * @return Session[]
     */
    public function findSessionsDisponiblesByDoctorant(Doctorant $doctorant) : array
    {
        $structures = [];
        foreach ($doctorant->getTheses() as $these) {
            if ($these->estNonHistorise() AND $these->getEtatThese() === These::ETAT_EN_COURS) {
                if ($etablissement = $these->getEtablissement()) {
                    $structures[] = $etablissement->getStructure()->getId(); // structure originale
                    $structures[] = $etablissement->getStructure()->getId(); // structure substituante éventuelle
                }
                if ($ecoleDoctorale = $these->getEcoleDoctorale()) {
                    $structures[] = $ecoleDoctorale->getStructure()->getId();
                    $structures[] = $ecoleDoctorale->getStructure()->getId();
                }
                if ($uniteRecherche = $these->getUniteRecherche()) {
                    $structures[] = $uniteRecherche->getStructure()->getId();
                    $structures[] = $uniteRecherche->getStructure()->getId();
                }
            }
        }

        $qb = $this->createQueryBuilder('session')
            ->andWhere('complement.structure in (:structures)')
            ->andWhere('etat.code = :ouverte OR etat.code = :preparation')
            ->setParameter('structures', array_unique($structures))
            ->setParameter('ouverte', Etat::CODE_OUVERTE)
            ->setParameter('preparation', Etat::CODE_PREPARATION)
            ->orderBy("seance.debut", "DESC");
        ;

        /** TODO SOMETHING WITH IT*/
        $now = new DateTime();
        $mois = ((int) $now->format('m'));
        $annee =  ((int) $now->format('Y'));
        if ($mois < 9) $annee -= 1;
        if (! $doctorant->hasMissionEnseignementFor($annee)) {
            $qb = $qb->andWhere('module.requireMissionEnseignement = :false')->setParameter('false', false);
        }

        $result =  $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsByFormateur(Individu $individu) : array
    {
        $qb = $this->createQueryBuilder('session')
            ->leftJoin('session.formateurs', 'formateur')
            ->leftJoin('session.formateurs', 'aformateur')->addSelect('aformateur')
            ->andWhere('formateur.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('session.histoDestruction IS NULL')
            ->andWhere('formateur.histoDestruction IS NULL')
            ->orderBy('session.id', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsPasseesByFormateur(Individu $individu) : array
    {
        $etats = [ Etat::CODE_CLOTURER ];

        $qb = $this->createQueryBuilder('session')
            ->leftJoin('session.formateurs', 'formateur')
            ->leftJoin('session.formateurs', 'aformateur')->addSelect('aformateur')
            ->andWhere('formateur.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('session.etat in (:etats)')
            ->andWhere('session.histoDestruction IS NULL')
            ->andWhere('formateur.histoDestruction IS NULL')
            ->setParameter('etats', $etats)
            ->orderBy('session.id', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsCourantesByFormateur(Individu $individu) : array
    {
        $etats = [ Etat::CODE_FERME ];

        $qb = $this->createQueryBuilder('session')
            ->leftJoin('session.formateurs', 'formateur')
            ->leftJoin('session.formateurs', 'aformateur')->addSelect('aformateur')
            ->andWhere('formateur.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('session.etat in (:etats)')
            ->andWhere('session.histoDestruction IS NULL')
            ->andWhere('formateur.histoDestruction IS NULL')
            ->setParameter('etats', $etats)
            ->orderBy('session.id', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsFuturesByFormateur(Individu $individu) : array
    {
        $etats = [ Etat::CODE_OUVERTE, Etat::CODE_PREPARATION ];

        $qb = $this->createQueryBuilder('session')
            ->leftJoin('session.formateurs', 'formateur')
            ->leftJoin('session.formateurs', 'aformateur')->addSelect('aformateur')
            ->andWhere('formateur.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('session.etat in (:etats)')
            ->andWhere('session.histoDestruction IS NULL')
            ->andWhere('formateur.histoDestruction IS NULL')
            ->setParameter('etats', $etats)
            ->orderBy('session.id', 'ASC')
        ;

       return $qb->getQuery()->getResult();
    }

    /**
     * @param Formation|null $formation
     * @param string $champ
     * @param string $ordre
     * @param bool $keep_histo
     * @return array
     */
    public function fetchSessionsByFormation(?Formation $formation, string $champ='id', string $ordre='ASC', bool $keep_histo = false) : array
    {
        $qb = $this->createQueryBuilder('session')
            ->orderBy('session.' . $champ, $ordre);

        if ($formation !== null)  $qb = $qb->andWhere('session.formation = :formation')->setParameter('formation', $formation);
        else                      $qb = $qb->andWhere('session.formation IS NULL');

        if (!$keep_histo) $qb = $qb->andWhere('session.histoDestruction IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function fetchDistinctAnneesUnivSessions(string $champ='id', string $ordre='ASC', bool $keep_histo = false) : array
    {
        $qb = $this->createQueryBuilder('session')
            ->distinct()
            ->select("YEAR(seance.debut) as annee")
            ->orderBy("annee", $ordre);

        if (!$keep_histo) $qb = $qb->andWhere('session.histoDestruction IS NULL');

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }
}