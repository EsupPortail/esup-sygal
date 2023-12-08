<?php

namespace Formation\Service\Url;

use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\Inscription;
use Formation\Provider\NatureFichier\NatureFichier;
use Laminas\View\Renderer\PhpRenderer;
use Structure\Entity\Db\Etablissement;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class UrlService {
    use FichierStorageServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    /** @var PhpRenderer */
    protected PhpRenderer $renderer;
    protected array $variables = [];

    public function setRenderer(PhpRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }
    public function setVariables(array $variables) : void
    {
        $this->variables = $variables;
    }

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
        if (!isset($this->vars['inscription'])) throw new RuntimeException("Variable inscription non fournie à [".UrlService::class."]");
        /** @var Inscription $inscription */
        $inscription = $this->vars['inscription'];

        $url = $this->renderer->url('formation/enquete/repondre-questions', ['inscription' => $inscription->getId()], [], true);
        return "<a href='".$url."'>Formulaire d'enquête de retour de formation</a>";
    }
}