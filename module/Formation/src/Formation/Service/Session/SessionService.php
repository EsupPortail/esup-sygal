<?php

namespace Formation\Service\Session;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Session $session
     * @return Session
     */
    public function create(Session $session) : Session
    {
        $index = $this->getEntityManager()->getRepository(Formation::class)->fetchIndexMax($session->getFormation()) + 1;
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
}