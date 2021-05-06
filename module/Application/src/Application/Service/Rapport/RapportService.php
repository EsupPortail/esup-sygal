<?php

namespace Application\Service\Rapport;

use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\These;
use Application\Entity\Db\TheseAnneeUniv;
use Application\Entity\Db\TypeRapport;
use Application\Filter\NomFichierRapportActiviteFormatter;
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

class RapportService extends BaseService
{
    use FichierServiceAwareTrait;
    use FileServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use NatureFichierServiceAwareTrait;

    /**
     * @param int $id
     * @return Rapport
     * @throws NoResultException
     */
    public function findRapport($id)
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('t, f')
//            ->addSelect('tr')->join('ra.typeRapport', 'tr', Join::WITH, $qb->expr()->in('tr.code', [
//                TypeRapport::RAPPORT_ACTIVITE_ANNUEL,
//                TypeRapport::RAPPORT_ACTIVITE_FINTHESE,
//            ]))
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
     * @return Rapport[]
     */
    public function findRapportsActiviteForThese(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('t, f')
            ->addSelect('tr')->join('ra.typeRapport', 'tr', Join::WITH, $qb->expr()->in('tr.code', [
                TypeRapport::RAPPORT_ACTIVITE_ANNUEL,
                TypeRapport::RAPPORT_ACTIVITE_FINTHESE,
            ]))
            ->join('ra.these', 't', Join::WITH, 't =:these')->setParameter('these', $these)
            ->join('ra.fichier', 'f')
            ->orderBy('ra.anneeUniv');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param TheseAnneeUniv[] $theseAnneeUnivs
     * @param Rapport[] $rapportsActiviteAnnuelsExistants
     * @return TheseAnneeUniv[]
     */
    public function computeAvailableTheseAnneeUniv(array $theseAnneeUnivs, array $rapportsActiviteAnnuelsExistants)
    {
        $rapportsAnnuelsExistantsAnneesUnivs = array_filter(array_map(function (Rapport $rapport) {
            return $rapport->getAnneeUniv();
        }, $rapportsActiviteAnnuelsExistants));

        return array_filter($theseAnneeUnivs, function(TheseAnneeUniv $theseAnneeUniv) use ($rapportsAnnuelsExistantsAnneesUnivs) {
            return !in_array($theseAnneeUniv->getAnneeUniv(), $rapportsAnnuelsExistantsAnneesUnivs);
        });
    }

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Rapport::class);
    }

    /**
     * Enregistre le rapport spécifié, après avoir créé le fichier à partir des données d'upload fournies.
     *
     * @param Rapport $rapport
     * @param array $uploadData Données résultant de l'upload de fichiers
     * @return Rapport Rapport annuel créé
     */
    public function saveRapportActivite(Rapport $rapport, array $uploadData)
    {
        $typeRapportCode = $rapport->getEstFinal() ?
            TypeRapport::RAPPORT_ACTIVITE_FINTHESE :
            TypeRapport::RAPPORT_ACTIVITE_ANNUEL;
        $typeRapport = $this->findTypeRapportByCode($typeRapportCode);
        $rapport->setTypeRapport($typeRapport);

        $this->fichierService->setNomFichierFormatter(new NomFichierRapportActiviteFormatter($rapport));
        $fichiers = $this->fichierService->createFichiersFromUpload($uploadData, NatureFichier::CODE_RAPPORT_ACTIVITE);

        $this->entityManager->beginTransaction();
        try {
            $this->fichierService->saveFichiers($fichiers);

            $fichier = array_pop($fichiers); // il n'y a en fait qu'un seul fichier
            $rapport->setFichier($fichier);

            $these = $rapport->getThese();
            $these->addRapport($rapport);

            $this->entityManager->persist($rapport);
            $this->entityManager->flush($rapport);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement des rapports annuels, rollback!", 0, $e);
        }

        return $rapport;
    }

    /**
     * @param Rapport $rapport
     */
    public function deleteRapport(Rapport $rapport)
    {
        $fichier = $rapport->getFichier();
        $these = $rapport->getThese();

        $this->entityManager->beginTransaction();
        try {
            $this->fichierService->supprimerFichiers([$fichier]);
            $these->removeRapport($rapport);
            $this->entityManager->remove($rapport);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des rapports annuels, rollback!", 0, $e);
        }
    }

    /**
     * @param bool $cacheable
     * @return array
     */
    public function findDistinctAnnees($cacheable = false)
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->distinct()
            ->select("ra.anneeUniv")
            ->join('ra.typeRapport', 'tr', Join::WITH, 'tr.code = :code')
            ->setParameter('code', TypeRapport::RAPPORT_ACTIVITE_ANNUEL)
            ->orderBy("ra.anneeUniv", 'desc');

        $qb->setCacheable($cacheable);

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }

    /**
     * @param string $typeRapportCode
     * @return TypeRapport
     */
    public function findTypeRapportByCode(string $typeRapportCode)
    {
        $qb = $this->getEntityManager()->getRepository(TypeRapport::class);

        /** @var TypeRapport $type */
        $type = $qb->findOneBy(['code' => $typeRapportCode]);

        return $type;
    }
}