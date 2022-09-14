<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation\Entity\Db\Repository;

use These\Entity\Db\These;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Session;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

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
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->join($alias.".formation", "formation")->addSelect("formation")
            ->leftJoin($alias.".inscriptions", "inscription")->addSelect("inscription")
            ->leftJoin($alias.".seances", "seance")->addSelect("seance")
            ->leftJoin($alias.".etat", "etat")->addSelect("etat")
            ->leftJoin($alias.".structuresValides", "complement")->addSelect("complement")
            ->leftJoin("complement.structure", "structureSessionInscription")->addSelect("structureSessionInscription")
            ->andWhere("complement.histoDestruction IS NULL")
        ;
        return $qb;
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
                $etablissement  = ($these->getEtablissement())?$these->getEtablissement()->getStructure():null;
                $ecoleDoctorale = ($these->getEcoleDoctorale())?$these->getEcoleDoctorale()->getStructure():null;
                $uniteRecherche = ($these->getUniteRecherche())?$these->getUniteRecherche()->getStructure():null;
                if ($etablissement) $structures[] = $etablissement;
                if ($ecoleDoctorale) $structures[] = $ecoleDoctorale;
                if ($uniteRecherche) $structures[] = $uniteRecherche;
            }
        }

        $qb = $this->createQB('session')
            ->andWhere('complement.structure in (:structures)')
            ->andWhere('etat.code = :ouverte OR etat.code = :preparation')
            ->setParameter('structures', $structures)
            ->setParameter('ouverte', Etat::CODE_OUVERTE)
            ->setParameter('preparation', Etat::CODE_PREPARATION)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsByFormateur(Individu $individu) : array
    {
        $qb = $this->createQB('session')
            ->leftJoin('session.formateurs', 'formateur')
            ->leftJoin('session.formateurs', 'aformateur')->addSelect('aformateur')
            ->andWhere('formateur.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('session.histoDestruction IS NULL')
            ->andWhere('formateur.histoDestruction IS NULL')
            ->orderBy('session.id', 'ASC')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsPasseesByFormateur(Individu $individu) : array
    {
        $etats = [ Etat::CODE_CLOTURER ];

        $qb = $this->createQB('session')
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
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsCourantesByFormateur(Individu $individu) : array
    {
        $etats = [ Etat::CODE_FERME ];

        $qb = $this->createQB('session')
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
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function findSessionsFuturesByFormateur(Individu $individu) : array
    {
        $etats = [ Etat::CODE_OUVERTE, Etat::CODE_PREPARATION ];

        $qb = $this->createQB('session')
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
        $result = $qb->getQuery()->getResult();
        return $result;
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
        $qb = $this->createQB('session')
            ->orderBy('session.' . $champ, $ordre);

        if ($formation !== null)  $qb = $qb->andWhere('session.formation = :formation')->setParameter('formation', $formation);
        else                      $qb = $qb->andWhere('session.formation IS NULL');

        if (!$keep_histo) $qb = $qb->andWhere('session.histoDestruction IS NULL');

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}