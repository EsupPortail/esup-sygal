<?php

namespace Formation\Service\Url;

use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\NatureFichier\NatureFichier;
use Structure\Entity\Db\Etablissement;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class UrlService extends \Application\Service\Url\UrlService
{
    use FichierStorageServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    protected ?array $allowedVariables = [
        'etablissement',
    ];

    public function getFormationSignature() : string
    {
        /** @var Etablissement $etablissement */
        $etablissement = $this->variables['etablissement'];
        if ($etablissement === null) return "Aucun établissement";

        $fichier = $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
            $etablissement->getStructure(),
            NatureFichier::CODE_SIGNATURE_FORMATION,
            $etablissement);
        if ($fichier === null) return "Aucune signature";

        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            $content = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la signature !", 0, $e);
        }
        return '<img src="data:image/png;base64,'. base64_encode($content). '" style="max-width:5cm;"/>';
    }

    /** @noinspection PhpUnused */
    public function getUrlEnquete(): string
    {
        if (!isset($this->variables['inscription'])) throw new RuntimeException("Variable inscription non fournie à [".UrlService::class."]");
        /** @var Inscription $inscription */
        $inscription = $this->variables['inscription'];

        $url = $this->fromRoute('formation/enquete/repondre-questions', ['inscription' => $inscription->getId()], ['force_canonical' => true], true);
        return "<a href='".$url."'>Formulaire d'enquête de retour de formation</a>";
    }

    /** @noinspection PhpUnused */
    public function getUrlListeInscritsSessionFormation(): string
    {
        if (!isset($this->variables['session'])) throw new RuntimeException("Variable session non fournie à [".UrlService::class."]");
        /** @var Session $session */
        $session = $this->variables['session'];

        $url = $this->fromRoute('formation/session/generer-emargements', ['session' => $session->getId()], ['force_canonical' => true] , true);
        return "<a href='".$url."'>Liste des inscrits à cette session de formation</a>";
    }
}