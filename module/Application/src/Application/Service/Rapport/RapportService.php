<?php

namespace Application\Service\Rapport;

use Application\Command\Exception\TimedOutCommandException;
use Fichier\Command\Pdf\PdfMergeShellCommandQpdf;
use Application\Command\ShellCommandRunnerTrait;
use Application\Entity\AnneeUniv;
use Fichier\Entity\Db\NatureFichier;
use Application\Entity\Db\Rapport;
use These\Entity\Db\These;
use Application\Entity\Db\TypeRapport;
use Application\Filter\NomFichierRapportFormatter;
use Application\Service\BaseService;
use InvalidArgumentException;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use These\Service\FichierThese\PdcData;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Depot\Service\PageDeCouverture\PageDeCouverturePdfExporterAwareTrait;
use Application\Service\RapportValidation\RapportValidationServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Closure;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf;

class RapportService extends BaseService
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use RapportValidationServiceAwareTrait;
    use PageDeCouverturePdfExporterAwareTrait;
    use ShellCommandRunnerTrait;

    /**
     * @param int $id
     * @return Rapport
     * @throws NoResultException
     */
    public function findRapportById(int $id): Rapport
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('t, f')
            ->join('ra.these', 't')
            ->join('ra.fichier', 'f')
            ->where('ra = :id')->setParameter('id', $id);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Impossible mais vrai !");
        }
    }

    /**
     * @param These $these
     * @param TypeRapport|null $type
     * @return Rapport[]
     */
    public function findRapportsForThese(These $these, TypeRapport $type = null): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->addSelect('t, f')
            ->addSelect('tr')->join('ra.typeRapport', 'tr')
            ->join('ra.these', 't', Join::WITH, 't =:these')->setParameter('these', $these)
            ->join('ra.fichier', 'f')
            ->andWhereNotHistorise()
            ->orderBy('ra.anneeUniv');

        if ($type !== null) {
            $qb->andWhere('tr = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
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
    public function saveRapport(Rapport $rapport, array $uploadData): Rapport
    {
        $codeNatureFichier = $this->computeCodeNatureFichierFromTypeRapport($rapport);

        $this->fichierService->setNomFichierFormatter(new NomFichierRapportFormatter($rapport));
        $fichiers = $this->fichierService->createFichiersFromUpload($uploadData, $codeNatureFichier);

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
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement des rapports annuels, rollback!", 0, $e);
        }

        return $rapport;
    }

    public function updateRapport(Rapport $rapport): Rapport
    {
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($rapport);
            $this->entityManager->flush($rapport);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement des rapports, rollback!", 0, $e);
        }

        return $rapport;
    }

    private function computeCodeNatureFichierFromTypeRapport(Rapport $rapport): string
    {
        switch ($rapport->getTypeRapport()->getCode()) {
            case TypeRapport::RAPPORT_CSI:
                return NatureFichier::CODE_RAPPORT_CSI;
            case TypeRapport::RAPPORT_MIPARCOURS:
                return NatureFichier::CODE_RAPPORT_MIPARCOURS;
            default:
                throw new InvalidArgumentException("Le type du rapport spécifié n'est pas supporté");
        }
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
            $this->rapportValidationService->deleteAllForRapport($rapport);
            $this->fichierService->supprimerFichiers([$fichier]);
            $these->removeRapport($rapport);
            $this->entityManager->remove($rapport);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des rapports, rollback!", 0, $e);
        }
    }

    /**
     * @param TypeRapport $typeRapport
     * @param bool $cacheable
     * @return array
     */
    public function findDistinctAnnees(TypeRapport $typeRapport, bool $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->distinct()
            ->select("ra.anneeUniv")
            ->join('ra.typeRapport', 'tr', Join::WITH, 'tr = :type')->setParameter('type', $typeRapport)
            ->orderBy("ra.anneeUniv", 'desc');

        $qb->setCacheable($cacheable);

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }

    /**
     * @param \These\Entity\Db\These $these
     * @param TypeRapport $typeRapport
     * @param bool $cacheable
     * @return Rapport[] [int => Rapport[]]
     */
    public function findRapportsParAnneesForThese(These $these, TypeRapport $typeRapport, bool $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ra');
        $qb
            ->join('ra.these', 't', Join::WITH, 't = :these')->setParameter('these', $these)
            ->join('ra.typeRapport', 'tr', Join::WITH, 'tr = :type')->setParameter('type', $typeRapport)
            ->orderBy("ra.anneeUniv", 'desc');

        /** @var Rapport $rapports */
        $rapports = $qb->setCacheable($cacheable)->getQuery()->getArrayResult();

        $rapportsParAnnees = [];
        foreach ($rapports as $rapport) {
            $anneeUniv = $rapport['anneeUniv'];
            if (!array_key_exists($anneeUniv, $rapportsParAnnees)) {
                $rapportsParAnnees[$anneeUniv] = [];
            }
            $rapportsParAnnees[$anneeUniv][] = $rapport;
        }

        return $rapportsParAnnees;
    }

    /**
     * @param string $typeRapportCode
     * @return TypeRapport
     */
    public function findTypeRapportByCode(string $typeRapportCode): TypeRapport
    {
        $qb = $this->getEntityManager()->getRepository(TypeRapport::class);

        /** @var TypeRapport $type */
        $type = $qb->findOneBy(['code' => $typeRapportCode]);

        return $type;
    }

    /**
     * @param \Application\Entity\AnneeUniv $anneeUniv
     * @return \Closure
     */
    public function getFilterRapportsByAnneeUniv(AnneeUniv $anneeUniv): Closure
    {
        return function (Rapport $rapport) use ($anneeUniv) {
            return $rapport->getAnneeUniv() === $anneeUniv;
        };
    }

    /**
     * Générer une page de couverture et l'ajouter au rapport spécifié.
     *
     * @param \Application\Entity\Db\Rapport $rapport
     * @param PdcData $data
     * @return string
     */
    public function ajouterPdc(Rapport $rapport, PdcData $data): string
    {
        // generation de la page de couverture
        $pdcFilePath = tempnam(sys_get_temp_dir(), 'sygal_rapport_pdc_') . '.pdf';
        $this->generatePageDeCouverture($rapport, $data, $pdcFilePath);

        $outputFilePath = tempnam(sys_get_temp_dir(), 'sygal_fusion_rapport_pdc_') . '.pdf';
        $command = $this->createCommandForAjoutPdc($rapport, $pdcFilePath, $outputFilePath);
        try {
            $this->runShellCommand($command);
        } catch (TimedOutCommandException $e) {
            // sans timeout, cette exception n'est pas lancée.
        }

        return $outputFilePath;
    }

    /**
     * @param \Application\Entity\Db\Rapport $rapport
     * @param string $pdcFilePath
     * @param string $outputFilePath
     * @return PdfMergeShellCommandQpdf
     */
    private function createCommandForAjoutPdc(Rapport $rapport, string $pdcFilePath, string $outputFilePath): PdfMergeShellCommandQpdf
    {
        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            $rapportFilePath = $this->fichierStorageService->getFileForFichier($rapport->getFichier());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le fichier physique associé au Fichier suivant : " . $rapport->getFichier(), null, $e);
        }

        $command = new PdfMergeShellCommandQpdf();
        $command->setInputFilesPaths([
            'couverture' => $pdcFilePath,
            'rapport' => $rapportFilePath,
        ]);
        $command->setOutputFilePath($outputFilePath);
        $command->generateCommandLine();

        return $command;
    }

    /**
     * @param \Application\Entity\Db\Rapport $rapport
     * @param PdcData $data
     * @param string $filepath
     */
    public function generatePageDeCouverture(Rapport $rapport, PdcData $data, string $filepath)
    {
        $this->pageDeCouverturePdfExporter->setVars(['rapport' => $rapport, 'data' => $data]);
        $this->pageDeCouverturePdfExporter->export($filepath, Pdf::DESTINATION_FILE);
    }
}