<?php

namespace Application\Service\RapportAnnuel;

use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\RapportAnnuel;
use Application\Entity\Db\These;
use Application\Entity\Db\TheseAnneeUniv;
use Application\Filter\NomFichierRapportAnnuelFormatter;
use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

class RapportAnnuelService extends BaseService
{
    use FichierServiceAwareTrait;
    use FileServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use NatureFichierServiceAwareTrait;

    /**
     * @param int $id
     * @return RapportAnnuel
     * @throws NoResultException
     */
    public function findRapportAnnuel($id)
    {
        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('t, f')
            ->join('ra.these', 't')
            ->join('ra.fichier', 'f')
            ->where('ra = :id')->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException $e) {
            // impossible
        }
    }

    /**
     * @param These $these
     * @return RapportAnnuel[]
     */
    public function findRapportsAnnuelsForThese(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('ra')
            ->addSelect('t, f')
            ->join('ra.these', 't', Join::WITH, 't =:these')->setParameter('these', $these)
            ->join('ra.fichier', 'f')
            ->orderBy('ra.anneeUniv');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param TheseAnneeUniv[] $theseAnneeUnivs
     * @param RapportAnnuel[] $rapportsAnnuelsExistants
     * @return TheseAnneeUniv[]
     */
    public function computeAvailableTheseAnneeUniv(array $theseAnneeUnivs, array $rapportsAnnuelsExistants)
    {
        $rapportsAnnuelsExistantsAnneesUnivs = array_map(function (RapportAnnuel $rapportAnnuel) {
            return $rapportAnnuel->getAnneeUniv();
        }, $rapportsAnnuelsExistants);

        return array_filter($theseAnneeUnivs, function(TheseAnneeUniv $theseAnneeUniv) use ($rapportsAnnuelsExistantsAnneesUnivs) {
            return !in_array($theseAnneeUniv->getAnneeUniv(), $rapportsAnnuelsExistantsAnneesUnivs);
        });
    }

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(RapportAnnuel::class);
    }

    /**
     * Enregistre le rapport annuel spécifié, après avoir créé le fichier à partir des données d'upload fournies.
     *
     * @param RapportAnnuel $rapportAnnuel
     * @param array $uploadData Données résultant de l'upload de fichiers
     * @return RapportAnnuel Rapport annuel créé
     */
    public function saveRapportAnnuel(RapportAnnuel $rapportAnnuel, array $uploadData)
    {
        $this->fichierService->setNomFichierFormatter(new NomFichierRapportAnnuelFormatter($rapportAnnuel));
        $fichiers = $this->fichierService->createFichiersFromUpload($uploadData, NatureFichier::CODE_RAPPORT_ANNUEL);

        $this->entityManager->beginTransaction();
        try {
            $this->fichierService->saveFichiers($fichiers);

            $fichier = array_pop($fichiers); // il n'y a en fait qu'un seul fichier
            $rapportAnnuel->setFichier($fichier);

            $these = $rapportAnnuel->getThese();
            $these->addRapportAnnuel($rapportAnnuel);

            $this->entityManager->persist($rapportAnnuel);
            $this->entityManager->flush($rapportAnnuel);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement des rapports annuels, rollback!", 0, $e);
        }

        return $rapportAnnuel;
    }

    /**
     * @param RapportAnnuel $rapportAnnuel
     */
    public function deleteRapportAnnuel(RapportAnnuel $rapportAnnuel)
    {
        $fichier = $rapportAnnuel->getFichier();
        $these = $rapportAnnuel->getThese();

        $this->entityManager->beginTransaction();
        try {
            $this->fichierService->supprimerFichiers([$fichier]);
            $these->removeRapportAnnuel($rapportAnnuel);
            $this->entityManager->remove($rapportAnnuel);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des rapports annuels, rollback!", 0, $e);
        }
    }
}