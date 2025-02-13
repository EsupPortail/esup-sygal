<?php

namespace Depot\Service\FichierHDR;

use Application\Service\BaseService;
use Depot\Entity\Db\FichierHDR;
use Depot\Entity\Db\Repository\FichierHDRRepository;
use Depot\Filter\NomFichierHDRFormatter;
use Depot\Service\FichierHDR\Exception\DepotImpossibleException;
use Doctrine\ORM\ORMException;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Laminas\Http\Response;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class FichierHDRService extends BaseService
{
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use ValiditeFichierServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    /**
     * @return FichierHDRRepository
     */
    public function getRepository()
    {
        /** @var FichierHDRRepository $repo */
        $repo = $this->entityManager->getRepository(FichierHDR::class);

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
    public function fetchNatureFichier($code): ?NatureFichier
    {
        /** @var ?NatureFichier $nature */
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
     * Crée des fichiers concernant la soutenance de la HDR spécifiée, à partir des données d'upload fournies.
     *
     * @param HDR $hdr HDR concernée
     * @param array $uploadResult Données résultant de l'upload de fichiers
     * @param NatureFichier $nature Version de fichier
     * @param VersionFichier|null $version
     * @return FichierHDR[] Fichiers créés
     */
    public function createFichierHDRsFromUpload(
        HDR $hdr,
        array $uploadResult,
        NatureFichier $nature,
        VersionFichier $version = null,
        ): array
    {
        $fichierHDRs = [];
        $files = $uploadResult['files'];

        if ($version === null) {
            $version = $this->versionFichierService->getRepository()->fetchVersionOriginale();
        }

        // normalisation au cas où il n'y a qu'un fichier
        if (isset($files['name'])) {
            $files = [$files];
        }

        $nomFichierFormatter = new NomFichierHDRFormatter();

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
                throw new DepotImpossibleException("Seul le format de fichier PDF est accepté pour la HDR");
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

            $fichierHDR = new FichierHDR();
            $fichierHDR
                ->setFichier($fichier)
                ->setHDR($hdr);

            // à faire en dernier car le formatter exploite des propriétés du FichierHDR
            $fichier->setNom($nomFichierFormatter->filter($fichierHDR));
            
            try {
                $this->fichierService->saveFichiers([$fichier]);
                $this->entityManager->persist($fichierHDR);
                $this->entityManager->flush($fichierHDR);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du Fichier", null, $e);
            }

            $fichierHDRs[] = $fichierHDR;
        }

        return $fichierHDRs;
    }
    
    /**
     * Supprime définitivement un fichier associé à une HDR.
     *
     * @param Fichier $fichier
     * @param HDR  $hdr
     */
    public function supprimerFichierHDR(Fichier $fichier, HDR $hdr)
    {
        // suppression du fichier
        // NB: "validites" supprimés en cascade
        $this->deleteFichiers([$fichier], $hdr);
    }

    /**
     * Supprime définitivement des Fichiers ou des FichierHDR, pour une HDR donnée.
     *
     * @param Fichier[]|FichierHDR[] $fichiers
     * @param HDR                    $hdr
     */
    public function deleteFichiers(array $fichiers, HDR $hdr)
    {
        // normalisation
        $normalizedFichiers = [];
        foreach ($fichiers as $fichier) {
            $normalizedFichiers[] = $fichier instanceof FichierHDR ? $fichier->getFichier() : $fichier;
        }

        $this->entityManager->beginTransaction();
        try {
            foreach ($normalizedFichiers as $fichier) {
                $hdr->removeFichier($fichier);
            }
            $this->fichierService->supprimerFichiers($normalizedFichiers);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des Fichiers en bdd, rollback!", 0, $e);
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
}