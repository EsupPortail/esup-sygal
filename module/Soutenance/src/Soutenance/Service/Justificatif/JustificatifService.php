<?php

namespace Soutenance\Service\Justificatif;

use Fichier\Entity\Db\NatureFichier;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

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
            $justificatif->historiser();
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
            $justificatif->dehistoriser();
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
            ->addSelect('fichierthese')->join('justificatif.fichier', 'fichierthese')
            ->addSelect('fichier')->join('fichierthese.fichier', 'fichier')
            ->addSelect('nature')->join('fichier.nature', 'nature')
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
     * @return Justificatif|null
     */
    public function getRequestedJustificatif(AbstractActionController $controller, string $paramName = 'justificatif') : ?Justificatif
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
            if ($membre->isExterieur() AND $membre->getQualite()->isRangB() AND $membre->getQualite()->isHDR() AND $membre->getQualite()->getJustificatif() !== 'O') {
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
            if ($membre->isExterieur() AND $membre->getQualite()->getJustificatif() === 'O') {
                $justificatifs[] = [
                    'type' => NatureFichier::CODE_JUSTIFICATIF_ETRANGER,
                    'membre' => $membre,
                    'justificatif' => $proposition->getJustificatif(NatureFichier::CODE_JUSTIFICATIF_ETRANGER, $membre),
                ];
            }


        }


        $listes = $proposition->getJustificatifs();
        $listes = array_filter($listes, function (Justificatif $a) { return $a->getFichier()->getFichier()->getNature()->getCode() === NatureFichier::CODE_AUTRES_JUSTIFICATIFS;});
        foreach ($listes as $element) {
            $justificatifs[] = [
                'type' => NatureFichier::CODE_AUTRES_JUSTIFICATIFS,
                'justificatif' => $element,
            ];
        }
        return $justificatifs;
    }

    /**
     * @param Proposition $proposition
     * @param array $justificatifs
     * @return boolean|null
     */
    public function isJustificatifsOk(Proposition $proposition, array $justificatifs = []) : ?bool
    {
        $non_bloquant = ['DELEGUATION_SIGNATURE', 'DEMANDE_LABEL_EUROPEEN'];

        if ($justificatifs === []) {
            $justificatifs = $this->generateListeJustificatif($proposition);
        }

        $justificatifsOk = true;
        foreach ($justificatifs as $justificatif) {
            if ($justificatif['justificatif'] === null) {
                if (array_search($justificatif['type'], $non_bloquant) === false) {
                    $justificatifsOk = false;
                    break;
                } else {
                    $justificatifsOk = null;
                }
            }
        }
        return $justificatifsOk;
    }

    /** @return Justificatif[] */
    public function getJustificatifsByPropositionAndNature(Proposition $proposition, string $natureCode, bool $histo = false) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('justificatif.proposition = :proposition')->setParameter('proposition', $proposition)
            ->andWhere('nature.code = :nature')->setParameter('nature', $natureCode)
            ->orderBy('justificatif.histoCreation', 'DESC')
        ;
        if (!$histo) $qb = $qb->andWhere('justificatif.histoDestruction IS NULL');

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}