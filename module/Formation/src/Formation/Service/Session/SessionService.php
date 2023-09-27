<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation\Service\Session;

use Application\Entity\Db\Utilisateur;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Repository\SessionRepository;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Formation\Entity\Db\SessionEtatHeurodatage;
use Formation\Service\Formation\FormationServiceAwareTrait;
use RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionService {
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return SessionRepository
     */
    public function getRepository() : SessionRepository
    {
        /** @var SessionRepository $repo */
        try {
            $repo = $this->entityManager->getRepository(Session::class);
        } catch (NotSupported $e) {
            throw new RuntimeException("Un problème est survenu lors de la création du QueryBuilder de [".Session::class."]",0,$e);
        }
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Session $session
     * @return Session
     */
    public function create(Session $session) : Session
    {
        $index = $this->getFormationService()->getRepository()->fetchIndexMax($session->getFormation()) + 1;
        $session->setIndex($index);
        try {
            $this->getEntityManager()->persist($session);
            $this->getEntityManager()->flush($session);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]",0, $e);
        }
        return $session;
    }

    /**
     * @param Session $session
     * @return Session
     */
    public function update(Session $session) : Session
    {
        try {
            $this->getEntityManager()->flush($session);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]",0, $e);
        }
        return $session;
    }

    /**
     * @param Session $session
     * @return Session
     */
    public function historise(Session $session) : Session
    {
        try {
            $session->historiser();
            $this->getEntityManager()->flush($session);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]",0, $e);
        }
        return $session;
    }

    /**
     * @param Session $session
     * @return Session
     */
    public function restore(Session $session) : Session
    {
        try {
            $session->dehistoriser();
            $this->getEntityManager()->flush($session);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]",0, $e);
        }
        return $session;
    }

    /**
     * @param Session $session
     * @return Session
     */
    public function delete(Session $session) : Session
    {
        try {
            $this->getEntityManager()->remove($session);
            $this->getEntityManager()->flush($session);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]",0, $e);
        }
        return $session;
    }

    public function setValeurParDefaut(Session $session) : Session
    {
        $module = $session->getFormation();
        if ($module !== null) {
            $session->setSite($module->getSite());
            $session->setResponsable($module->getResponsable());
            $session->setModalite($module->getModalite());
            $session->setType($module->getType());
            $session->setTypeStructure($module->getTypeStructure());
            $session->setTailleListePrincipale($module->getTailleListePrincipale());
            $session->setTailleListeComplementaire($module->getTailleListeComplementaire());
        }
        return $session;
    }

    /** FACADE ********************************************************************************************************/

    /**
     * Terminaison des sessions dont toutes les séances sont passées.
     *
     * @return int[] Id des sessions mises à jour
     * @throws \Doctrine\ORM\ORMException
     */
    public function terminerSessionsDontToutesSeancesPassees(): array
    {
        $qb = $this->getRepository()->createQueryBuilder('sess')
            //->addSelect('seances') // pas besoin de sélectionner la relation
            ->join('sess.seances', 'seances', Join::WITH, 'seances.histoDestruction is null') // sessions AYANT des séances
            ->andWhere('sess.histoDestruction is null')
            ->andWhere('sess.etat <> :etat')->setParameter('etat', Session::ETAT_CLOS_FINAL)
            ->andWhere(
                'NOT EXISTS (' .
                'select s from ' . Seance::class . ' s ' .
                'where s.session = sess AND s.histoDestruction is null AND s.fin >= CURRENT_TIMESTAMP()' .
                ')'
            );

        /** @var array[] $sessionsSansSeancesAVenir */
        $sessionsSansSeancesAVenir = $qb->getQuery()->getArrayResult();

        if (empty($sessionsSansSeancesAVenir)) {
            return [];
        }

        $sessionIds = [];
        foreach ($sessionsSansSeancesAVenir as $session) {
            $sessionIds[] = $session['id'];
        }

        $this->getEntityManager()->createQueryBuilder();
        $qb
            ->update(Session::class, 's')
            ->set('s.etat', ':etat')
            ->setParameter('etat', Session::ETAT_CLOS_FINAL)
            ->where($qb->expr()->in('s.id', $sessionIds));
        $qb->getQuery()->execute();

        $this->getEntityManager()->flush();

        return $sessionIds;
    }

    public function addHeurodatage(Session $session, Etat $etat) : SessionEtatHeurodatage
    {

        $heurodatage = new SessionEtatHeurodatage();
        $heurodatage->setSession($session);
        $heurodatage->setEtat($etat);
        $heurodatage->setHeurodatage(new DateTime());
        $user = $this->userContextService->getIdentityDb();
        if ($user === null) {
            try {
                $user = $this->getEntityManager()->getRepository(Utilisateur::class)->find(1);
            } catch (NotSupported $e) {
                throw new RuntimeException("Un erreur est survenu lors de la récupération de l'utilisateur d'id 1 (sygal-app)",0,$e);
            }
        }
        $heurodatage->setUtilisateur($user);

        try {
            $this->getEntityManager()->persist($heurodatage);
            $this->getEntityManager()->flush($heurodatage);
        } catch (ORMException $e) {
            throw new RuntimeException("Une erreur s'est produite en bd.", 0, $e);
        }

        return $heurodatage;
    }
}