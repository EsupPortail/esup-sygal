<?php

namespace Application\Service\Fichier;

use Application\Command\ShellScriptRunner;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Repository\FichierRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\VersionFichier;
use Application\Filter\NomFichierFormatter;
use Application\Service\BaseService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Fichier\Exception\DepotImpossibleException;
use Application\Service\Fichier\Exception\ValidationImpossibleException;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\PageDeGarde\PageDeCouverturePdfExporter;
use Application\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Application\Validator\Exception\CinesErrorException;
use Application\Validator\FichierCinesValidator;
use Doctrine\ORM\OptimisticLockException;
use Retraitement\Service\RetraitementServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf;
use UnicaenApp\Util;
use Zend\Filter\FilterInterface;
use Zend\Http\Response;
use Zend\View\Renderer\RendererInterface;

class FichierService extends BaseService
{
    use VersionFichierServiceAwareTrait;
    use ValiditeFichierServiceAwareTrait;
    use RetraitementServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NotifierServiceAwareTrait;

    /**
     * @var string
     */
    private $rootDirectoryPath;

    /**
     * @param string $rootDirectoryPath
     */
    public function setRootDirectoryPath($rootDirectoryPath)
    {
        $this->rootDirectoryPath = $rootDirectoryPath;
    }

    /**
     * @return string
     */
    public function getRootDirectoryPath()
    {
        return $this->rootDirectoryPath;
    }

    /**
     * @return FichierRepository
     */
    public function getRepository()
    {
        /** @var FichierRepository $repo */
        $repo = $this->entityManager->getRepository(Fichier::class);

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
     * Retourne le contenu d'un Fichier sous la forme d'une chaîne de caractères.
     *
     * @param Fichier $fichier
     * @return string
     */
    public function fetchContenuFichier(Fichier $fichier)
    {
        $filePath = $this->computeDestinationFilePathForFichier($fichier);

        if (! is_readable($filePath)) {
            throw new RuntimeException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $filePath);
        }

        $contenuFichier = file_get_contents($filePath);

        return $contenuFichier;
    }

    /**
     * Retourne le chemin sur le disque (du serveur) du dossier parent du fichier physique associé à un Fichier.
     *
     * @param Fichier $fichier Entité Fichier dont on veut connaître le chemin du fichier physique associé
     *                         stocké sur disque
     * @return string
     */
    private function computeDestinationDirectoryPathForFichier(Fichier $fichier)
    {
        return $this->rootDirectoryPath . '/' . strtolower($fichier->getNature()->getCode());
    }

    /**
     * Retourne le chemin sur le disque (du serveur) du fichier physique associé à un Fichier.
     *
     * @param Fichier $fichier Entité Fichier dont on veut connaître le chemin du fichier physique associé
     *                         stocké sur disque
     * @return string
     */
    public function computeDestinationFilePathForFichier(Fichier $fichier)
    {
        return $this->computeDestinationDirectoryPathForFichier($fichier) . '/' . $fichier->getNom();
    }

    /**
     * Création si besoin du dossier destination du Fichier spécifié.
     *
     * @param Fichier $fichier
     */
    private function createDestinationDirectoryPathForFichier(Fichier $fichier)
    {
        $parentDir = $this->computeDestinationDirectoryPathForFichier($fichier);
        $ok = \Application\Util::createWritableFolder($parentDir, 0770);
        if (!$ok) {
            throw new RuntimeException(
                "Le répertoire suivant n'a pas pu être créé sur le serveur : " . $parentDir);
        }
    }

    private function moveUploadedFileForFichier(Fichier $fichier, $fromPath)
    {
        // création si bseoin du dossier destination
        $this->createDestinationDirectoryPathForFichier($fichier);

        $newPath = $this->computeDestinationFilePathForFichier($fichier);
        $res = move_uploaded_file($fromPath, $newPath);

        if ($res === false) {
            throw new RuntimeException("Impossible de déplacer le fichier temporaire uploadé de $fromPath vers $newPath");
        }
    }

    /**
     * Crée des fichiers concernant la soutenance de la thèse spécifiée, à partir des données d'upload fournies.
     *
     * @param These           $these        Thèse concernée
     * @param array           $uploadResult Données résultant de l'upload de fichiers
     * @param NatureFichier   $nature       Version de fichier
     * @param VersionFichier  $version
     * @param string          $retraitement
     * @param FilterInterface $nomFichierFormatter
     * @return Fichier[] Fichiers créés
     */
    public function createFichiersFromUpload(
        These $these,
        array $uploadResult,
        NatureFichier $nature,
        VersionFichier $version = null,
        $retraitement = null,
        FilterInterface $nomFichierFormatter = null)
    {
        $fichiers = [];
        $files = $uploadResult['files'];

        if ($version === null) {
            $version = $this->versionFichierService->getRepository()->fetchVersionOriginale();
        }

        // normalisation au cas où il n'y a qu'un fichier
        if (isset($files['name'])) {
            $files = [$files];
        }

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
                ->setThese($these)
                ->setNature($nature)
                ->setVersion($version)
                ->setTypeMime($typeFichier)
                ->setNomOriginal($nomFichier)
                ->setTaille($tailleFichier)
                ->setRetraitement($retraitement);

            // à faire en dernier car le formatter exploite des propriétés du Fichier
            $fichier->setNom($nomFichierFormatter ? $nomFichierFormatter->filter($fichier) : $nomFichier);

            $this->moveUploadedFileForFichier($fichier, $path);

            $this->entityManager->persist($fichier);
            try {
                $this->entityManager->flush($fichier);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du Fichier", null, $e);
            }

            $fichiers[] = $fichier;

            unset($fichier);
        }

        return $fichiers;
    }

    /**
     * Exécute le test de validité au fichier spécifié et enregistre le résultat via une entité ValiditeFichier.
     *
     * @param Fichier $fichier
     * @return ValiditeFichier
     * @throws ValidationImpossibleException Erreur rencontrée lors de la validation
     */
    public function validerFichier(Fichier $fichier)
    {
        $exceptionThrown = null;

        $filePath = $this->computeDestinationFilePathForFichier($fichier);

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

        $this->validiteFichierService->clearValiditesFichier($fichier);
        $validite = $this->validiteFichierService->createValiditeFichier($fichier, $resultat);

        if ($exceptionThrown) {
            throw new ValidationImpossibleException("Erreur rencontrée lors de la validation.", 0, $exceptionThrown);
        }

        return $validite;
    }

    /**
     * @param Fichier $fichier Fichier à retraiter
     * @return Fichier Futur fichier retraité
     */
    private function preparerFichierRetraite(Fichier $fichier)
    {
        $version = $fichier->getVersion()->estVersionCorrigee() ?
            VersionFichier::CODE_ARCHI_CORR :
            VersionFichier::CODE_ARCHI;

        // si un fichier retraité "partiel" peut avoir déjà été créé en cas de retraitement en tâche de fond, on l'utilise.
        $fichierRetraite = current($this->getRepository()->fetchFichiers($fichier->getThese(), NatureFichier::CODE_THESE_PDF, $version, true));
        if ($fichierRetraite && $fichierRetraite->getEstPartiel()) {
            return $fichierRetraite;
        }

        // suppression de tout fichier retraité existant
        if ($fichierRetraite) {
            $this->deleteFichiers([$fichierRetraite]);
        }

        $fichierRetraite = new Fichier();
        $fichierRetraite
            ->setThese($fichier->getThese())
            ->setNature($fichier->getNature())
            ->setNom($fichier->getNom())
            ->setTypeMime($fichier->getTypeMime())
            ->setNomOriginal($fichier->getNomOriginal())
            ->setEstPartiel(true)
            ->setTaille(0)
            ->setEstAnnexe($fichier->getEstAnnexe());

        $fichierRetraite->setVersion($this->versionFichierService->getRepository()->findOneByCode($version));
        $fichierRetraite->setRetraitement(Fichier::RETRAITEMENT_AUTO);

        $nomFichierFormatter = new NomFichierFormatter();
        $fichierRetraite->setNom($nomFichierFormatter->filter($fichierRetraite));

        return $fichierRetraite;
    }

    /**
     * À partir du fichier spécifié, crée un fichier retraité rattaché à la même thèse.
     *
     * @param Fichier $fichier Fichier à retraiter
     * @param string  $timeout Timeout éventuel à appliquer au lancement du script de retraitement.
     * @return Fichier Fichier retraité
     */
    public function creerFichierRetraite(Fichier $fichier, $timeout = null)
    {
        $inputFilePath  = $this->computeDestinationFilePathForFichier($fichier);

        $fichierRetraite = $this->preparerFichierRetraite($fichier);
        $outputFilePath = $this->computeDestinationFilePathForFichier($fichierRetraite);

        $this->createDestinationDirectoryPathForFichier($fichierRetraite);
        $this->retraitementService->retraiterFichier($inputFilePath, $outputFilePath, $timeout);
        //
        // NB: Si un timout est spécifié et qu'il est atteint, une exception TimedOutCommandException est levée.
        //

        $fichierRetraite
            ->setEstPartiel(false)
            ->setTaille(strlen(file_get_contents($outputFilePath))); // TODO: utiliser filesize() plutôt ?

        $this->entityManager->persist($fichier);
        try {
            $this->entityManager->flush($fichier);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du Fichier", null, $e);
        }

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($fichierRetraite);
            $this->entityManager->flush($fichierRetraite);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement du fichier retraité", 0, $e);
        }

        return $fichierRetraite;
    }

    /**
     * Version asynchrone de la création d'un fichier retraité.
     *
     * L'idée est de créer un Fichier partiel (sans ContenuFichier associé) puis de lancer le retraitement
     * en tâche de fond.
     *
     * @param Fichier $fichier
     * @see FichierService::creerFichierRetraite()
     */
    public function creerFichierRetraiteAsync(Fichier $fichier)
    {
        $newFichier = $this->preparerFichierRetraite($fichier);

        try {
            $this->entityManager->persist($newFichier);
            $this->entityManager->flush($newFichier);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur survenue lors de l'enregistrement du fichier retraité", 0, $e);
        }

        // lancement du script de retraitement en tâche de fond
        $scriptPath = APPLICATION_DIR . '/bin/fichier-retraiter.sh';
        $destinataires = $newFichier->getHistoModificateur()->getEmail();
        $args = sprintf('--tester-archivabilite --notifier="%s" %s', $destinataires, $fichier->getId());
        $runner = new ShellScriptRunner($scriptPath);
        $runner->setAsync();
        $runner->run($args);
        // /var/www/html/sygal/bin/fichier-retraiter.sh --tester-archivabilite --notifier="bertrand.gauthier@unicaen.fr" 25f8f498-23d0-44a0-b1b7-f889720ec4f8
    }

    /**
     * Supprime définitivement un fichier.
     * S'il s'agit du fichier de thèse original, suppression aussi du fichier retraité éventuel.
     *
     * @param Fichier $fichier
     */
    public function supprimerFichier(Fichier $fichier)
    {
        $version = $fichier->getVersion();
        $nature = $fichier->getNature();

        // si c'est le fichier de thèse original qui est supprimé, suppression aussi du fichier retraité éventuel
        $supprimerAussiTheseRetraite = $nature->estThesePdf() && $version->estVersionOriginale();

        // suppression du fichier
        // NB: "validites" supprimés en cascade
        $this->deleteFichiers([$fichier]);

        if ($supprimerAussiTheseRetraite) {
            // suppression aussi du fichier retraité éventuel
            // NB: "validites" supprimés en cascade
            $versionASupprimer = $version->estVersionCorrigee() ?
                VersionFichier::CODE_ARCHI_CORR :
                VersionFichier::CODE_ARCHI;
            $fichierTheseRetraite = current($this->getRepository()->fetchFichiers($fichier->getThese(), NatureFichier::CODE_THESE_PDF, $versionASupprimer, true));
            if ($fichierTheseRetraite !== false) {
                $this->deleteFichiers([$fichierTheseRetraite]);
            }
        }
    }

    /**
     * Supprime définitivement des fichiers liés à une thèse.
     *
     * @param Fichier[] $fichiers
     */
    public function deleteFichiers($fichiers)
    {
        $filePaths = [];
        $this->entityManager->beginTransaction();
        try {
            foreach ($fichiers as $fichier) {
                $filePaths[] = $this->computeDestinationFilePathForFichier($fichier);
                $these = $fichier->getThese();
                $these->removeFichier($fichier);
                $this->entityManager->remove($fichier);
                $this->entityManager->flush($fichier);
            }
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des Fichiers en bdd, rollback!", 0, $e);
        }

        // suppression des fichiers physiques sur le disque
        $notDeletedFiles = [];
        foreach ($filePaths as $filePath) {
            $success = unlink($filePath);
            if ($success === false) {
                $notDeletedFiles[] = $filePath;
            }
        }
        if ($notDeletedFiles) {
            throw new RuntimeException(
                "Les fichiers suivants n'ont pas pu être supprimés sur le disque : " . implode(', ', $notDeletedFiles));
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
    public function generateFirstPagePreview($inputFilePath)
    {
        if (mime_content_type($inputFilePath) !== Fichier::MIME_TYPE_PDF) {
            return Util::createImageWithText("Erreur: Seul le format |de fichier PDF est accepté", 200, 100);
        }

        if (! extension_loaded('imagick')) {
            return Util::createImageWithText("Erreur: extension PHP |'imagick' non chargée", 170, 100);
        }

        $outputFilePath = sys_get_temp_dir() . '/' . uniqid() . '.png';

        try {
            $im = new \Imagick();
            $im->setResolution(300, 300);
            $im->readImage($inputFilePath . '[0]'); // 1ere page seulement
            $im->setImageFormat('png');
            $im->writeImage($outputFilePath);
            $im->clear();
            $im->destroy();
        } catch (\ImagickException $ie) {
            throw new RuntimeException(
                "Erreur rencontrée lors de la création de l'aperçu", null, $ie);
        }

        $content = file_get_contents($outputFilePath);

        return $content;
    }

    /**
     * Cette fonction a pour vocation de récupérer les informations utile pour la génération de la page de couverture.
     * Si une clef est vide cela indique un problème associé à la thèse
     * @param These $these
     * @return PdcData
     */
    public function fetchInformationsPageDeCouverture(These $these) {
        $pdcData = new PdcData();
        $informations = [];

        if ($these === null) throw new LogicException("Une these doit être fournie pour pouvoir effectuer la récupération de ces données.");

        /**  informations générales */
        $pdcData->setTitre($these->getTitre());
        $pdcData->setSpecialite($these->getLibelleDiscipline());
        if ($these->getEtablissement()) $pdcData->setEtablissement($these->getEtablissement()->getLibelle());
        if ($these->getDoctorant()) $pdcData->setDoctorant($these->getDoctorant()->getIndividu()->getNomComplet(false, false, false, true, true));
        if ($these->getDateSoutenance()) $pdcData->setDate($these->getDateSoutenance()->format("d/m/Y"));

        /** cotutelle */
        $pdcData->setCotutuelle(false);
        if($these->getLibelleEtabCotutelle() !== null && $these->getLibelleEtabCotutelle() !== "") {
            $pdcData->setCotutuelle(true);
            $pdcData->setCotutuelleLibelle($these->getLibelleEtabCotutelle());
            if ($these->getLibellePaysCotutelle()) $pdcData->setCotutuellePays($these->getLibellePaysCotutelle());
        }

        /** Jury de thèses */
        $jury = [];
        $acteurs = $these->getActeurs()->toArray();
        $rapporteurs =  array_filter($acteurs, function(Acteur $a) { return $a->estRapporteur(); });
        $pdcData->setRapporteurs($rapporteurs);
        $directeurs =  array_filter($acteurs, function(Acteur $a) { return $a->estDirecteur(); });
        $pdcData->setDirecteurs($directeurs);
        $codirecteurs =  array_filter($acteurs, function(Acteur $a) { return $a->estCodirecteur(); });
        $pdcData->setCodirecteurs($codirecteurs);
        $president =  array_filter($acteurs, function(Acteur $a) { return $a->estPresidentJury(); });

        $membres = array_diff($acteurs, $rapporteurs, $directeurs, $president);
        $pdcData->setMembres($membres);

        /** associée */
        $pdcData->setAssocie(false);
        /** @var Acteur $directeur */
        foreach ($directeurs as $directeur) {
            if (! $directeur->getEtablissement()) {
                throw new RuntimeException("Anomalie: le directeur de thèse '{$directeur}' n'a pas d'établissement.");
            }
            if ($directeur->getEtablissement()->estAssocie()) {
                $pdcData->setAssocie(true);
                $pdcData->setLogoAssocie($directeur->getEtablissement()->getCheminLogo());
                $pdcData->setLibelleAssocie($directeur->getEtablissement()->getLibelle());
            }
        }

        $acteursEnCouverture = array_merge($rapporteurs, $directeurs, $codirecteurs, $president, $membres);
        usort($acteursEnCouverture, Acteur::getComparisonFunction());
        $acteursEnCouverture = array_unique($acteursEnCouverture);

        /** @var Acteur $acteur */
        foreach ($acteursEnCouverture as $acteur) {
            $acteurData = new MembreData();
            $acteurData->setDenomination($acteur->getIndividu()->getNomComplet(true,false,false, true, true));
            $acteurData->setQualite($acteur->getQualite());

            if (!$acteur->estPresidentJury()) {
                $acteurData->setRole($acteur->getRole()->getLibelle());
            } else {
                $acteurData->setRole(Role::LIBELLE_PRESIDENT);
            }
            if ($acteur->getEtablissement()) $acteurData->setEtablissement($acteur->getEtablissement()->getStructure()->getLibelle());
            $pdcData->addActeurEnCouverture($acteurData);
        }

        /** Directeurs de thèses */
        $nomination = [];
        foreach ($directeurs as $directeur) {
            $nomination[] = $directeur->getIndividu()->getNomComplet(false, false, false, true, true);
        }
        $pdcData->setListing(implode(" et ", $nomination).", ");
        if ($these->getUniteRecherche()) $pdcData->setUniteRecherche($these->getUniteRecherche()->getStructure()->getLibelle());

        $comue = $this->getEtablissementService()->getRepository()->find(1);
        $pdcData->setLogoCOMUE( ($comue)?$comue->getCheminLogo():null);
        $pdcData->setLogoEtablissement($these->getEtablissement()?$these->getEtablissement()->getCheminLogo():null);
        $pdcData->setLogoEcoleDoctorale($these->getEcoleDoctorale()?$these->getEcoleDoctorale()->getCheminLogo():null);
        $pdcData->setLogoUniteRecherche($these->getUniteRecherche()?$these->getUniteRecherche()->getCheminLogo():null);


        return $pdcData;
    }

    /**
     * @param These             $these
     * @param RendererInterface $renderer
     * @param string            $filepath
     */
    public function generatePageDeCouverture(These $these, RendererInterface $renderer, $filepath = null)
    {

        $pdcData = $this->fetchInformationsPageDeCouverture($these);

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
}