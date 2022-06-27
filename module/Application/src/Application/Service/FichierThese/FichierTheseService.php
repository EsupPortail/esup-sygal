<?php

namespace Application\Service\FichierThese;

use Application\Command\Exception\TimedOutCommandException;
use Doctrine\ORM\ORMException;
use Fichier\Command\MergeShellCommand;
use Fichier\Command\Pdf\AjoutPdcShellCommandQpdf;
use Fichier\Command\Pdf\RetraitementShellCommand;
use Application\Command\ShellCommandRunner;
use Application\Command\ShellCommandRunnerTrait;
use Fichier\Entity\Db\Fichier;
use Application\Entity\Db\FichierThese;
use Fichier\Entity\Db\NatureFichier;
use Application\Entity\Db\Repository\FichierTheseRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\ValiditeFichier;
use Fichier\Entity\Db\VersionFichier;
use Application\Filter\NomFichierTheseFormatter;
use Application\Service\BaseService;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\FichierThese\Exception\DepotImpossibleException;
use Application\Service\FichierThese\Exception\ValidationImpossibleException;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\PageDeCouverture\PageDeCouverturePdfExporterAwareTrait;
use Application\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Fichier\Validator\Exception\CinesErrorException;
use Fichier\Validator\FichierCinesValidator;
use Doctrine\ORM\OptimisticLockException;
use Retraitement\Service\RetraitementServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf;
use UnicaenApp\Util;
use Laminas\Http\Response;

class FichierTheseService extends BaseService
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use ValiditeFichierServiceAwareTrait;
    use RetraitementServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use PageDeCouverturePdfExporterAwareTrait;
    use ShellCommandRunnerTrait;

    /**
     * @return FichierTheseRepository
     */
    public function getRepository()
    {
        /** @var FichierTheseRepository $repo */
        $repo = $this->entityManager->getRepository(FichierThese::class);

        return $repo;
    }

    /**
     * @param $code
     * @return null|VersionFichier
     */
    public function fetchVersionFichier($code)
    {
        /** @var VersionFichier $version */
        $version = $this->getEntityManager()->getRepository(VersionFichier::class)->findOneBy(['code' => $code]);

        return $version;
    }

    /**
     * @param $code
     * @return null|NatureFichier
     */
    public function fetchNatureFichier($code)
    {
        /** @var NatureFichier $nature */
        $nature = $this->getEntityManager()->getRepository(NatureFichier::class)->findOneBy(['code' => $code]);

        return $nature;
    }

    /**
     * @param bool $estAnnexe
     * @return NatureFichier|null
     * @deprecated estAnnexe devrait être abandonné
     */
    public function fetchNatureFichierByEstAnnexe($estAnnexe)
    {
        return $this->fetchNatureFichier($estAnnexe ? NatureFichier::CODE_FICHIER_NON_PDF : NatureFichier::CODE_THESE_PDF);
    }

    /**
     * Crée des fichiers concernant la soutenance de la thèse spécifiée, à partir des données d'upload fournies.
     *
     * @param These $these Thèse concernée
     * @param array $uploadResult Données résultant de l'upload de fichiers
     * @param NatureFichier $nature Version de fichier
     * @param \Fichier\Entity\Db\VersionFichier|null $version
     * @param null $retraitement
     * @return FichierThese[] Fichiers créés
     */
    public function createFichierThesesFromUpload(
        These $these,
        array $uploadResult,
        NatureFichier $nature,
        VersionFichier $version = null,
        $retraitement = null): array
    {
        $fichierTheses = [];
        $files = $uploadResult['files'];

        if ($version === null) {
            $version = $this->versionFichierService->getRepository()->fetchVersionOriginale();
        }

        // normalisation au cas où il n'y a qu'un fichier
        if (isset($files['name'])) {
            $files = [$files];
        }

        $nomFichierFormatter = new NomFichierTheseFormatter();

        foreach ((array)$files as $file) {
            $path = $file['tmp_name'];
            $nomFichier = $file['name'];
            $typeFichier = $file['type'];
            $tailleFichier = $file['size'];

            if (! is_uploaded_file($path)) {
                throw new DepotImpossibleException("Possible file upload attack: " . $path);
            }

            // validation du format de fichier
            if ($nature->estFichierNonPdf() && $typeFichier === FileUtils::MIME_TYPE_PDF) {
                throw new DepotImpossibleException("Le format de fichier PDF n'est pas accepté pour les annexes");
            }
            if ($nature->estThesePdf() && $typeFichier !== FileUtils::MIME_TYPE_PDF) {
                throw new DepotImpossibleException("Seul le format de fichier PDF est accepté pour la thèse");
            }

            $fichier = new Fichier();
            $fichier
                ->setNature($nature)
                ->setVersion($version)
                ->setTypeMime($typeFichier)
                ->setNomOriginal($nomFichier)
                ->setTaille($tailleFichier)
                ->setPath($path)
            ;

            $fichierThese = new FichierThese();
            $fichierThese
                ->setFichier($fichier)
                ->setThese($these)
                ->setRetraitement($retraitement);

            // à faire en dernier car le formatter exploite des propriétés du FichierThese
            $fichier->setNom($nomFichierFormatter->filter($fichierThese));

//            $this->fichierService->moveUploadedFileForFichier($fichier);

            try {
                $this->fichierService->saveFichiers([$fichier]);

//                $this->entityManager->persist($fichier);
                $this->entityManager->persist($fichierThese);
//                $this->entityManager->flush($fichier);
                $this->entityManager->flush($fichierThese);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du Fichier", null, $e);
            }

            $fichierTheses[] = $fichierThese;
        }

        return $fichierTheses;
    }

    /**
     * Exécute le test de validité au fichier spécifié et enregistre le résultat via une entité ValiditeFichier.
     *
     * @param FichierThese $fichierThese
     * @return ValiditeFichier
     * @throws ValidationImpossibleException Erreur rencontrée lors de la validation
     * @throws OptimisticLockException
     */
    public function validerFichierThese(FichierThese $fichierThese): ValiditeFichier
    {
        $exceptionThrown = null;

//        $filePath = $this->fichierService->computeFilePathForFichier($fichierThese->getFichier());
        try {
            $filePath = $this->fichierStorageService->getFileForFichier($fichierThese->getFichier());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le fichier physique associé au Fichier suivant : " . $fichierThese->getFichier(), null, $e);
        }

        try {
            $estArchivable = $this->fichierCinesValidator->isValid($filePath);
            $message = $estArchivable ? "Le fichier est archivable" : current($this->fichierCinesValidator->getMessages());
        }
        catch (CinesErrorException $cee) { // erreur possible à identifier
            $estArchivable = null;
            $message = $cee->getMessage();
            $exceptionThrown = $cee;
        }
        catch (RuntimeException $re) { // erreur non identifiée
            $estArchivable = null;
            $message = "Le test d'archivabilité a rencontré un problème : " . $re->getMessage();
            $exceptionThrown = $re;
        }

        $resultat = [
            'estArchivable' => $estArchivable,
            'resultat'      => $this->fichierCinesValidator->getResult(),
            'message'       => $message,
        ];

        $this->validiteFichierService->clearValiditesFichier($fichierThese);
        $validite = $this->validiteFichierService->createValiditeFichier($fichierThese, $resultat);

        if ($exceptionThrown) {
            throw new ValidationImpossibleException("Erreur rencontrée lors de la validation.", 0, $exceptionThrown);
        }

        return $validite;
    }

    /**
     * @param FichierThese $fichierThese Fichier à retraiter
     * @return FichierThese Futur fichier retraité
     */
    private function preparerFichierTheseRetraite(FichierThese $fichierThese)
    {
        $these = $fichierThese->getThese();
        $fichierSource = $fichierThese->getFichier();

        $version = $fichierSource->getVersion()->estVersionCorrigee() ?
            VersionFichier::CODE_ARCHI_CORR :
            VersionFichier::CODE_ARCHI;

        // si un fichier retraité "partiel" peut avoir déjà été créé en cas de retraitement en tâche de fond, on l'utilise.
        $fichierTheseRetraite = current($this->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $version, true));
        if ($fichierTheseRetraite && $fichierTheseRetraite->getEstPartiel()) {
            return $fichierTheseRetraite;
        }

        // suppression de tout fichier retraité existant
        if ($fichierTheseRetraite) {
            $this->deleteFichiers([$fichierTheseRetraite], $these);
        }

        $nomFichierFormatter = new NomFichierTheseFormatter();

        $fichier = new Fichier();
        $fichier
            ->setNature($fichierSource->getNature())
            ->setNom($fichierSource->getNom())
            ->setTypeMime($fichierSource->getTypeMime())
            ->setNomOriginal($fichierSource->getNomOriginal())
            ->setTaille(0);

        $fichier->setVersion($this->versionFichierService->getRepository()->findOneByCode($version));

        $fichierTheseRetraite = new FichierThese();
        $fichierTheseRetraite
            ->setFichier($fichier)
            ->setThese($these)
            ->setEstPartiel(true);

        $fichierTheseRetraite->setRetraitement(FichierThese::RETRAITEMENT_AUTO);

        $fichier->setNom($nomFichierFormatter->filter($fichierTheseRetraite));

        return $fichierTheseRetraite;
    }

    /**
     * À partir du fichier spécifié, crée un fichier retraité rattaché à la même thèse.
     *
     * @param FichierThese $fichierThese Fichier à retraiter
     * @param string|null $timeout Timeout éventuel à appliquer au lancement du script de retraitement.
     * @return FichierThese Fichier retraité
     * @throws \Application\Command\Exception\TimedOutCommandException
     */
    public function creerFichierTheseRetraite(FichierThese $fichierThese, string $timeout = null): FichierThese
    {
//        $inputFilePath  = $this->fichierService->computeFilePathForFichier($fichierThese->getFichier());
        try {
            $inputFilePath = $this->fichierStorageService->getFileForFichier($fichierThese->getFichier());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le fichier physique associé au Fichier suivant : " . $fichierThese->getFichier(), null, $e);
        }

        $fichierTheseRetraite = $this->preparerFichierTheseRetraite($fichierThese);
//        $outputFilePath = $this->fichierService->computeFilePathForFichier($fichierTheseRetraite->getFichier());
        $outputFilePath = tempnam(sys_get_temp_dir(), '');

//        $this->fichierService->createDirectoryForFichier($fichierTheseRetraite->getFichier());
        $this->retraitementService->retraiterFichier($inputFilePath, $outputFilePath, $timeout);
        try {
            $this->fichierStorageService->saveFileForFichier($outputFilePath, $fichierTheseRetraite->getFichier());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'enregistrer dans le storage le fichier associé au Fichier suivant : " . $fichierTheseRetraite->getFichier(), null, $e);
        }
        //
        // NB: Si un timout est spécifié et qu'il est atteint, une exception TimedOutCommandException est levée.
        //

        $fichierTheseRetraite
            ->setEstPartiel(false);
        $fichierTheseRetraite->getFichier()
            ->setTaille(filesize($outputFilePath));

        try {
            $this->entityManager->persist($fichierThese);
            $this->entityManager->flush($fichierThese);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du Fichier", null, $e);
        }

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($fichierTheseRetraite->getFichier());
            $this->entityManager->persist($fichierTheseRetraite);
            $this->entityManager->flush($fichierTheseRetraite->getFichier());
            $this->entityManager->flush($fichierTheseRetraite);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement du fichier retraité", 0, $e);
        }

        return $fichierTheseRetraite;
    }

    /**
     * Version asynchrone de la création d'un fichier retraité.
     *
     * L'idée est de créer un Fichier partiel (sans ContenuFichier associé) puis de lancer le retraitement
     * en tâche de fond.
     *
     * @param FichierThese $fichierThese
     * @see FichierTheseService::creerFichierTheseRetraite()
     */
    public function creerFichierTheseRetraiteAsync(FichierThese $fichierThese)
    {
        $newFichierThese = $this->preparerFichierTheseRetraite($fichierThese);

        try {
            $this->entityManager->persist($newFichierThese->getFichier());
            $this->entityManager->persist($newFichierThese);
            $this->entityManager->flush($newFichierThese->getFichier());
            $this->entityManager->flush($newFichierThese);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur survenue lors de l'enregistrement du fichier retraité", 0, $e);
        }

        // lancement du script de retraitement en tâche de fond
        $destinataires = $newFichierThese->getFichier()->getHistoModificateur()->getEmail();
        $command = new RetraitementShellCommand();
        $command->setDestinataires($destinataires);
        $command->setFichierThese($fichierThese);
        $command->generateCommandLine();
        $runner = new ShellCommandRunner();
        $runner->setCommand($command);
        $runner->runCommandInBackground();
        // /var/www/html/sygal/bin/fichier-retraiter.sh --tester-archivabilite --notifier="bertrand.gauthier@unicaen.fr" 25f8f498-23d0-44a0-b1b7-f889720ec4f8
    }

    /**
     * Supprime définitivement un fichier associé à une thèse.
     * S'il s'agit du fichier de thèse original, suppression aussi du fichier retraité éventuel.
     *
     * @param Fichier $fichier
     * @param These   $these
     */
    public function supprimerFichierThese(Fichier $fichier, These $these)
    {
        $version = $fichier->getVersion();
        $nature = $fichier->getNature();

        // si c'est le fichier de thèse original qui est supprimé, suppression aussi du fichier retraité éventuel
        $supprimerAussiTheseRetraite = $nature->estThesePdf() && $version->estVersionOriginale();

        // suppression du fichier
        // NB: "validites" supprimés en cascade
        $this->deleteFichiers([$fichier], $these);

        if ($supprimerAussiTheseRetraite) {
            // suppression aussi du fichier retraité éventuel
            // NB: "validites" supprimés en cascade
            $versionASupprimer = $version->estVersionCorrigee() ?
                VersionFichier::CODE_ARCHI_CORR :
                VersionFichier::CODE_ARCHI;
            $fichierTheseRetraite = current($this->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionASupprimer, true));
            if ($fichierTheseRetraite !== false) {
                $this->deleteFichiers([$fichierTheseRetraite], $these);
            }
        }
    }

    /**
     * Supprime définitivement des Fichiers ou des FichierThese, pour une thèse donnée.
     *
     * @param Fichier[]|FichierThese[] $fichiers
     * @param These                    $these
     */
    public function deleteFichiers(array $fichiers, These $these)
    {
        // normalisation
        $normalizedFichiers = [];
        foreach ($fichiers as $fichier) {
            $normalizedFichiers[] = $fichier instanceof FichierThese ? $fichier->getFichier() : $fichier;
        }

        $this->entityManager->beginTransaction();
        try {
            foreach ($normalizedFichiers as $fichier) {
                $these->removeFichier($fichier);
            }
            $this->fichierService->supprimerFichiers($normalizedFichiers);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des Fichiers en bdd, rollback!", 0, $e);
        }
    }

    /**
     * Génère un fichier PNG temporaire pour aperçu de la première page d'un fichier PDF,
     * et retourne son contenu binaire.
     *
     * @param string $inputFilePath
     * @return string Contenu binaire du fichier PNG généré
     * @throws LogicException Format de fichier incorrect
     */
    public function generateFirstPagePreview(string $inputFilePath): string
    {
        return FileUtils::generateFirstPagePreviewPngImageFromPdf($inputFilePath);
    }

    /**
     * @param PdcData     $pdcData
     * @param string      $filepath
     * @param boolean     $recto
     */
    public function generatePageDeCouverture(PdcData $pdcData, $filepath = null, $recto = true)
    {
        $this->pageDeCouverturePdfExporter->setVars([
            'informations' => $pdcData,
            'recto/verso' => $recto,
        ]);
        if ($filepath !== null) {
            $this->pageDeCouverturePdfExporter->export($filepath, Pdf::DESTINATION_FILE);
        } else {
            $this->pageDeCouverturePdfExporter->export('export.pdf');
            exit;
        }
    }

    /**
     * @param Response $response
     * @param string $fileContent
     * @param string $mimeType Ex : "image/png"
     * @param int|null $cacheMaxAge En secondes, ex: 60*60*24 = 86400 s = 1 jour
     * @return Response
     */
    public function createResponseForFileContent(Response $response, string $fileContent, string $mimeType, ?int $cacheMaxAge = null): Response
    {
        return FileUtils::createResponseForFileContent($response, $fileContent, $mimeType, $cacheMaxAge);
    }

    /**
     * @var FichierCinesValidator
     */
    protected $fichierCinesValidator;

    /**
     * @param FichierCinesValidator $fichierCinesValidator
     * @return $this
     */
    public function setFichierCinesValidator(FichierCinesValidator $fichierCinesValidator)
    {
        $this->fichierCinesValidator = $fichierCinesValidator;

        return $this;
    }

    /**
     * @param These $these
     * @param PdcData $pdcData
     * @param string $versionFichier
     * @param bool $removeFirstPage
     * @param string|null $timeout
     * @return string
     * @throws \Application\Command\Exception\TimedOutCommandException
     */
    public function fusionnerPdcEtThese(These $these, PdcData $pdcData, string $versionFichier, bool $removeFirstPage = false, string $timeout = null): string
    {
        $outputFilePath = $this->generateOutputFilePathForMerge($these);
        $command = $this->createCommandForPdcMerge($these, $pdcData, $versionFichier, $removeFirstPage, $outputFilePath);
        $this->runShellCommand($command, $timeout);

        return $outputFilePath;
    }

    private function generateOutputFilePathForMerge(These $these): string
    {
        $outputFilePath = uniqid(sprintf("sygal_fusion_%s-%s-%s_",
            $these->getId(),
            $these->getDoctorant()->getIndividu()->getNomUsuel(),
            $these->getDoctorant()->getIndividu()->getPrenom()
        )) . '.pdf';

        return sys_get_temp_dir() . '/' . Util::reduce($outputFilePath);
    }

    /**
     * @param These   $these
     * @param PdcData $pdcData
     * @param string  $versionFichier
     * @param bool    $removeFirstPage
     * @param string  $outputFilePath
     * @return MergeShellCommand
     */
    private function createCommandForPdcMerge(These $these, PdcData $pdcData, string $versionFichier, bool $removeFirstPage, string $outputFilePath)
    {
        // generation de la couverture
        $filename = "sygal_couverture_" . $these->getId() . "_" . uniqid() . ".pdf";
        $this->generatePageDeCouverture($pdcData, $filename, !$removeFirstPage);

        // recuperation de la bonne version du manuscript
        $manuscritFichier = current($this->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionFichier));
//        $manuscritChemin = $this->fichierService->computeFilePathForFichier($manuscritFichier->getFichier());
        try {
            $manuscritChemin = $this->fichierStorageService->getFileForFichier($manuscritFichier->getFichier());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le fichier physique associé au Fichier suivant : " . $manuscritFichier->getFichier(), null, $e);
        }

//        $command = new \Fichier\Command\Pdf\AjoutPdcShellCommandGs();
        $command = new AjoutPdcShellCommandQpdf();
        $command->setSupprimer1erePageDuManuscrit($removeFirstPage); // avec retrait de la 1ere page si necessaire
        $command->setInputFilesPaths([
            'couverture' => sys_get_temp_dir() . '/' . $filename,
            'manuscrit' => $manuscritChemin,
        ]);
        $command->setOutputFilePath($outputFilePath);
        $command->generateCommandLine();

        return $command;
    }

    public function fusionneFichierTheseAsync(These $these, $versionFichier, $removeFirstPage, $destinatairesNotification = [])
    {
        $scriptPath = sprintf("%s/merge.sh", APPLICATION_DIR . '/bin');

        $args = sprintf('--these=%d --versionFichier=%s', $these->getId(), $versionFichier);
        if ($removeFirstPage) {
            $args .= ' --removeFirstPage';
        }
        if (! empty($destinatairesNotification)) {
            $args .= sprintf(' --notifier="%s"', implode(',', $destinatairesNotification));
        }

        // lancement en tâche de fond
        $runner = new ShellCommandRunner();
        $runner->setCommandAsString('bash ' . $scriptPath . ' ' . $args);
        $runner->runCommandInBackground();
    }

    public function updateConformiteFichierTheseRetraitee(These $these, $conforme = null)
    {
        $repo = $this->getRepository();

        $fichiersVA  = $repo->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI);
        $fichiersVAC = $repo->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI_CORR);

        /** @var FichierThese $fichierThese */
        if (! empty($fichiersVAC)) {
            $fichierThese = current($fichiersVAC) ?: null;
        } else {
            $fichierThese = current($fichiersVA) ?: null;
        }
        // il n'existe pas forcément de fichier en version d'archivage (si la version originale est testée archivable)
        if ($fichierThese === null) {
            return;
        }

        $fichierThese->setEstConforme($conforme);

        try {
            $this->entityManager->flush($fichierThese);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }
}