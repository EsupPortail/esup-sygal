<?php

namespace Soutenance\Service\Justificatif;

use Application\Entity\Db\NatureFichier;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class JustificatifService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function create(Justificatif $justificatif) : Justificatif
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $justificatif->setHistoCreateur($user);
        $justificatif->setHistoCreation($date);
        $justificatif->setHistoModificateur($user);
        $justificatif->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($justificatif);
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function update(Justificatif $justificatif) : Justificatif
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $justificatif->setHistoModificateur($user);
        $justificatif->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function historise(Justificatif $justificatif) : Justificatif
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $justificatif->setHistoModificateur($user);
        $justificatif->setHistoModification($date);
        $justificatif->setHistoDestructeur($user);
        $justificatif->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function restore(Justificatif $justificatif) : Justificatif
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $justificatif->setHistoModificateur($user);
        $justificatif->setHistoModification($date);
        $justificatif->setHistoDestructeur(null);
        $justificatif->setHistoDestruction(null);

        try {
            $this->getEntityManager()->flush($justificatif);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /**
     * @param Justificatif $justificatif
     * @return Justificatif
     */
    public function delete(Justificatif $justificatif) : Justificatif
    {
        try {
            $this->getEntityManager()->remove($justificatif->getFichier());
            $this->getEntityManager()->remove($justificatif);
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en BDD.", $e);
        }
        return $justificatif;
    }

    /** REQUETES ******************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(Justificatif::class)->createQueryBuilder('justificatif')
            ->addSelect('proposition')->join('justificatif.proposition', 'proposition')
            ->addSelect('fichier')->join('justificatif.fichier', 'fichier')
            ->addSelect('membre')->leftJoin('justificatif.membre', 'membre')
        ;
        return $qb;
    }

    public function getJustificatif($id)
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('justificatif.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Justificatif partagent le même identifiant [".$id."].", $e);
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $paramName
     * @return Justificatif
     */
    public function getRequestedJustificatif($controller, $paramName = 'justificatif')
    {
        $id = $controller->params()->fromRoute($paramName);
        $justificatif = $this->getJustificatif($id);
        return $justificatif;
    }


    /**
     * @param Proposition $proposition
     * @param bool $all
     * @return array
     */
    public function generateListeJustificatif($proposition, bool $all = false)
    {
        $justificatifs = [];
        if ($proposition === null) return $justificatifs;

        /**
         * Justificatifs liés à la nature de la thèse ou de la soutenance :
         * - NatureFichier::CODE_DELOCALISATION_SOUTENANCE,
         * - NatureFichier::CODE_DEMANDE_LABEL,
         * - NatureFichier::CODE_DEMANDE_CONFIDENT,
         * - NatureFichier::CODE_LANGUE_ANGLAISE
         */
        if ($proposition->isExterieur()) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_DELOCALISATION_SOUTENANCE,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DELOCALISATION_SOUTENANCE, null),
            ];
        }
        if ($proposition->isLabelEuropeen()) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_DEMANDE_LABEL,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DEMANDE_LABEL, null),
            ];
        }
        if ($proposition->getConfidentialite() !== null) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_DEMANDE_CONFIDENT,
                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DEMANDE_CONFIDENT, null),
            ];
        }
//        if (  $proposition->isManuscritAnglais() OR $proposition->isSoutenanceAnglais()) {
//            $justificatifs[] = [
//                'type' => NatureFichier::CODE_LANGUE_ANGLAISE,
//                'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_LANGUE_ANGLAISE, null),
//            ];
//        }

        /**
         * Justificatifs liés aux membres du jury :
         * - NatureFichier::CODE_DELEGUATION_SIGNATURE,
         * - NatureFichier::CODE_JUSTIFICATIF_HDR,
         * - NatureFichier::CODE_JUSTIFICATIF_EMERITAT
         * @var Membre $membre
         */
        foreach ($proposition->getMembres() as $membre) {
            if ($membre->isVisio() OR $all === true) {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_DELEGUATION_SIGNATURE,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_DELEGUATION_SIGNATURE, $membre),
                ];
            }
            if ($membre->isExterieur() AND $membre->getQualite()->isRangB() AND $membre->getQualite()->isHDR()) {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_JUSTIFICATIF_HDR,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_JUSTIFICATIF_HDR, $membre),
                ];
            }
            if ($membre->isExterieur() AND $membre->getQualite()->isEmeritat()) {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_JUSTIFICATIF_EMERITAT,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_JUSTIFICATIF_EMERITAT, $membre),
                ];
            }
        }

        return $justificatifs;
    }

    /**
     * @param Proposition $proposition
     * @param array $justificatifs
     * @return boolean
     */
    public function isJustificatifsOk($proposition, $justificatifs = [])
    {
        if ($justificatifs === []) {
            $justificatifs = $this->generateListeJustificatif($proposition);
        }

        $justificatifsOk = true;
        foreach ($justificatifs as $justificatif) {
            if ($justificatif['justificatif'] === null) {
                $justificatifsOk = false;
                break;
            }
        }
        return $justificatifsOk;
    }
}