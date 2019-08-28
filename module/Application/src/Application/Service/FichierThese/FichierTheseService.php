<?php

namespace Application\Service\FichierThese;

use Application\Command\MergeCommand;
use Application\Command\ShellScriptRunner;
use Application\Command\TruncateAndMergeCommand;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Repository\FichierTheseRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\VersionFichier;
use Application\Filter\NomFichierTheseFormatter;
use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\FichierThese\Exception\DepotImpossibleException;
use Application\Service\FichierThese\Exception\ValidationImpossibleException;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\PageDeGarde\PageDeCouverturePdfExporter;
use Application\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Application\Validator\Exception\CinesErrorException;
use Application\Validator\FichierCinesValidator;
use Doctrine\ORM\OptimisticLockException;
use Retraitement\Exception\TimedOutCommandException;
use Retraitement\Service\RetraitementServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf;
use UnicaenApp\Util;
use Zend\Http\Response;
use Zend\View\Renderer\PhpRenderer;

class FichierTheseService extends BaseService
{
    use FichierServiceAwareTrait;
    use FileServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use ValiditeFichierServiceAwareTrait;
    use RetraitementServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;

    /**
     * @var PhpRenderer
     */
    private $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

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
     * @param These          $these        Thèse concernée
     * @param array          $uploadResult Données résultant de l'upload de fichiers
     * @param NatureFichier  $nature       Version de fichier
     * @param VersionFichier $version
     * @param string         $retraitement
     * @return FichierThese[] Fichiers créés
     */
    public function createFichierThesesFromUpload(
        These $these,
        array $uploadResult,
        NatureFichier $nature,
        VersionFichier $version = null,
        $retraitement = null)
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
                throw new RuntimeException("Possible file upload attack: " . $path);
            }

            // validation du format de fichier
            if ($nature->estFichierNonPdf() && $typeFichier === Fichier::MIME_TYPE_PDF) {
                throw new DepotImpossibleException("Le format de fichier PDF n'est pas accepté pour les annexes");
            }
            if ($nature->estThesePdf() && $typeFichier !== Fichier::MIME_TYPE_PDF) {
                throw new DepotImpossibleException("Seul le format de fichier PDF est accepté pour la thèse");
            }

            $fichier = new Fichier();
            $fichier
                ->setNature($nature)
                ->setVersion($version)
                ->setTypeMime($typeFichier)
                ->setNomOriginal($nomFichier)
                ->setTaille($tailleFichier);

            $fichierThese = new FichierThese();
            $fichierThese
                ->setFichier($fichier)
                ->setThese($these)
                ->setRetraitement($retraitement);

            // à faire en dernier car le formatter exploite des propriétés du FichierThese
            $fichier->setNom($nomFichierFormatter->filter($fichierThese));

            $this->fichierService->moveUploadedFileForFichier($fichierThese->getFichier(), $path);

            $this->entityManager->persist($fichier);
            $this->entityManager->persist($fichierThese);
            try {
                $this->entityManager->flush($fichier);
                $this->entityManager->flush($fichierThese);
            } catch (OptimisticLockException $e) {
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
    public function validerFichierThese(FichierThese $fichierThese)
    {
        $exceptionThrown = null;

        $filePath = $this->fichierService->computeDestinationFilePathForFichier($fichierThese->getFichier());

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
     * @param string       $timeout      Timeout éventuel à appliquer au lancement du script de retraitement.
     * @return FichierThese Fichier retraité
     */
    public function creerFichierTheseRetraite(FichierThese $fichierThese, $timeout = null)
    {
        $inputFilePath  = $this->fichierService->computeDestinationFilePathForFichier($fichierThese->getFichier());

        $fichierTheseRetraite = $this->preparerFichierTheseRetraite($fichierThese);
        $outputFilePath = $this->fichierService->computeDestinationFilePathForFichier($fichierTheseRetraite->getFichier());

        $this->fichierService->createDestinationDirectoryPathForFichier($fichierTheseRetraite->getFichier());
        $this->retraitementService->retraiterFichier($inputFilePath, $outputFilePath, $timeout);
        //
        // NB: Si un timout est spécifié et qu'il est atteint, une exception TimedOutCommandException est levée.
        //

        $fichierTheseRetraite
            ->setEstPartiel(false);
        $fichierTheseRetraite->getFichier()
            ->setTaille(filesize($outputFilePath));

        $this->entityManager->persist($fichierThese);
        try {
            $this->entityManager->flush($fichierThese);
        } catch (OptimisticLockException $e) {
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
        $scriptPath = APPLICATION_DIR . '/bin/fichier-retraiter.sh';
        $destinataires = $newFichierThese->getFichier()->getHistoModificateur()->getEmail();
        $args = sprintf('--tester-archivabilite --notifier="%s" %s', $destinataires, $fichierThese->getId());
        $runner = new ShellScriptRunner($scriptPath);
        $runner->setAsync();
        $runner->run($args);
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
     * Supprime définitivement des fichiers liés à une thèse.
     *
     * @param Fichier[] $fichiers
     * @param These     $these
     */
    public function deleteFichiers(array $fichiers, These $these)
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($fichiers as $fichier) {
                $these->removeFichier($fichier);
            }
            $this->fichierService->supprimerFichiers($fichiers);
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
     *
     * @deprecated Appeler directement FileService::generateFirstPagePreview()
     */
    public function generateFirstPagePreview($inputFilePath)
    {
        return $this->fileService->generateFirstPagePreview($inputFilePath);
    }

    /**
     * @param PdcData     $pdcData
     * @param PhpRenderer $renderer
     * @param string      $filepath
     */
    public function generatePageDeCouverture(PdcData $pdcData, PhpRenderer $renderer, $filepath = null)
    {
        $exporter = new PageDeCouverturePdfExporter($renderer, 'A4');
        $exporter->setVars([
            'informations' => $pdcData,
        ]);
        if ($filepath !== null) {
            $exporter->export($filepath, Pdf::DESTINATION_FILE);
        } else {
            $exporter->export('export.pdf');
            exit;
        }
    }

    /**
     * @param Response $response
     * @param string   $fileContent
     * @param int|null $cacheMaxAge En secondes, ex: 60*60*24 = 86400 s = 1 jour
     * @return Response
     */
    public function createResponseForFileContent(Response $response, $fileContent, $cacheMaxAge = null)
    {
        $response->setContent($fileContent);

        $headers = $response->getHeaders();
        $headers
            ->addHeaderLine('Content-Transfer-Encoding', "binary")
            ->addHeaderLine('Content-Type', "image/png")
            ->addHeaderLine('Content-length', strlen($fileContent));

        if ($cacheMaxAge === null) {
            $headers
                ->addHeaderLine('Cache-Control', "no-cache, no-store, must-revalidate")
                ->addHeaderLine('Pragma', 'no-cache');
        }
        else {
            // autorisation de la mise en cache de l'image par le client
            $headers
                ->addHeaderLine('Cache-Control', "private, max-age=$cacheMaxAge")
                ->addHeaderLine('Pragma', 'private')// tout sauf 'no-cache'
                ->addHeaderLine('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheMaxAge));
        }

        return $response;
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
     * @param These   $these
     * @param PdcData $pdcData
     * @param string  $versionFichier
     * @param bool    $removeFirstPage
     * @param int     $timeout
     * @return string
     */
    public function fusionnerPdcEtThese(These $these, PdcData $pdcData, $versionFichier, $removeFirstPage = false, $timeout = 0)
    {
        $outputFilePath = $this->generateOutputFilePathForMerge($these);

        $command = $this->createCommandForPdcMerge($these, $pdcData, $versionFichier, $removeFirstPage, $outputFilePath);

        if ($timeout) {
            $command->setOption('timeout', $timeout);
        }
        try {
            $command->checkResources();
            $command->execute();

            $success = ($command->getReturnCode() === 0);
            if (!$success) {
                throw new RuntimeException(sprintf(
                    "La commande %s a échoué (code retour = %s), voici le résultat d'exécution : %s",
                    $command->getName(),
                    $command->getReturnCode(),
                    implode(PHP_EOL, $command->getResult())
                ));
            }
        }
        catch (TimedOutCommandException $toce) {
            throw $toce;
        }
        catch (RuntimeException $rte) {
            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande " . $command->getName(),
                0,
                $rte);
        }

        return $outputFilePath;
    }

    private function generateOutputFilePathForMerge(These $these)
    {
        $outputFilePath = sprintf("sygal_fusion_%s-%s-%s.pdf",
            $these->getId(),
            $these->getDoctorant()->getIndividu()->getNomUsuel(),
            $these->getDoctorant()->getIndividu()->getPrenom()
        );
        $outputFilePath = sys_get_temp_dir() . '/' . Util::reduce($outputFilePath);

        return $outputFilePath;
    }

    /**
     * @param These   $these
     * @param PdcData $pdcData
     * @param string  $versionFichier
     * @param bool    $removeFirstPage
     * @param string  $outputFilePath
     * @return MergeCommand|TruncateAndMergeCommand
     */
    private function createCommandForPdcMerge(These $these, PdcData $pdcData, $versionFichier, $removeFirstPage, $outputFilePath)
    {
        // generation de la couverture
        $filename = "sygal_couverture_" . $these->getId() . "_" . uniqid() . ".pdf";
        $this->generatePageDeCouverture($pdcData, $this->renderer, $filename);

        // recuperation de la bonne version du manuscript
        $manuscritFichier = current($this->getRepository()->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF, $versionFichier));
        $manuscritChemin = $this->fichierService->computeDestinationFilePathForFichier($manuscritFichier->getFichier());

        $inputFiles = [
            'couverture' => sys_get_temp_dir() . '/' . $filename,
            'manuscrit' => $manuscritChemin
        ];

        // retrait de la premier page si necessaire
        if ($removeFirstPage) {
            $command = new TruncateAndMergeCommand();
        } else {
            $command = new MergeCommand();
        }

        $command->generate($outputFilePath, $inputFiles, $errorFilePath);
        //unlink($inputFiles['couverture']);

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
        $runner = new ShellScriptRunner($scriptPath, 'bash');
        $runner->setAsync();
        $runner->run($args);
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