<?php

namespace Formation\Service\EnqueteReponse;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\EnqueteReponse;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EnqueteReponseService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param EnqueteReponse $reponse
     * @return EnqueteReponse
     */
    public function create(EnqueteReponse $reponse) : EnqueteReponse
    {
        try {
            $this->getEntityManager()->persist($reponse);
            $this->getEntityManager()->flush($reponse);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteReponse]",0, $e);
        }
        return $reponse;
    }

    /**
     * @param EnqueteReponse $reponse
     * @return EnqueteReponse
     */
    public function update(EnqueteReponse $reponse) : EnqueteReponse
    {
        try {
            $this->getEntityManager()->flush($reponse);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteReponse]",0, $e);
        }
        return $reponse;
    }

    /**
     * @param EnqueteReponse $reponse
     * @return EnqueteReponse
     */
    public function historise(EnqueteReponse $reponse) : EnqueteReponse
    {
        try {
            $reponse->historiser();
            $this->getEntityManager()->flush($reponse);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteReponse]",0, $e);
        }
        return $reponse;
    }

    /**
     * @param EnqueteReponse $reponse
     * @return EnqueteReponse
     */
    public function restore(EnqueteReponse $reponse) : EnqueteReponse
    {
        try {
            $reponse->dehistoriser();
            $this->getEntityManager()->flush($reponse);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteReponse]",0, $e);
        }
        return $reponse;
    }

    /**
     * @param EnqueteReponse $reponse
     * @return EnqueteReponse
     */
    public function delete(EnqueteReponse $reponse) : EnqueteReponse
    {
        try {
            $this->getEntityManager()->remove($reponse);
            $this->getEntityManager()->flush($reponse);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteReponse]",0, $e);
        }
        return $reponse;
    }

    /** FACADE ********************************************************************************************************/

}