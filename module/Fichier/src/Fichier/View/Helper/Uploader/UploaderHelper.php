<?php

namespace Fichier\View\Helper\Uploader;

use Fichier\Controller\Plugin\Uploader\UploadedFileInterface;
use Fichier\Controller\Plugin\Uploader\UploaderPlugin;
use Fichier\Controller\Plugin\Uploader\UploadForm;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

/**
 * Aide de vue simplifiant l'upload de fichier.
 *
 * NB: requiert le widget jQuery dont le source est dans "./script/uploader-widget.js.dist".
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see UploaderPlugin
 */
class UploaderHelper extends AbstractHelper
{
    /**
     * URL de l'action permettant de déposer un nouveau fichier.
     * C'est l'URL à laquelle est POSTé le formulaire d'upload.
     *
     * Si elle est null, le formulaire n'est pas affiché.
     */
    protected ?string $url = null;

    /**
     * Point d'entrée.
     */
    public function __invoke(): static
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();

        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/script"]]));

        return $this;
    }

    /**
     * Spécifie l'URL permettant de déposer un nouveau fichier.
     * C'est l'URL à laquelle est POSTé le formulaire de dépôt.
     *
     * Si elle est null, aucun formulaire ne sera affiché.
     */
    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Retourne le code HTML.
     *
     * @return string Code HTML
     */
    public function __toString(): string
    {
        return $this->renderForm();
    }

    /**
     * Génrère le code HTML du_ formulaire de dépôt par défaut ou de celui spécifié.
     * NB: le formulaire s'affiche ssi une URL de dépôt a été spécifiée.
     *
     * @return string Code HTML
     */
    public function renderForm(?UploadForm $uploadForm = null): string
    {
        return $this->getView()->render("upload-form.phtml", [
            'form' => $uploadForm ?: $this->form,
            'url' => $this->url,
        ]);
    }

    /**
     * Génère le code HTML de la DIV destionée à afficher la liste des fichiers déposés.
     *
     * @param string $url URL de dépôt (upload) de fichier
     * @param string $label Label de la liste des fichiers déposés
     * @return string Code HTML
     */
    public function renderUploadedFiles(string $url, string $label = "Fichiers déposés :"): string
    {
        return $this->getView()->render("uploaded-files.phtml", [
            'url' => $url,
            'label' => $label,
        ]);
    }

    /**
     * Génère le code HTML du lien permettant de télécharger un fichier déposé.
     * Sauf si aucune URL n'est spécifiée, auquel cas ce n'est pas un lien mais simplement le nom du fichier.
     *
     * @param UploadedFileInterface $fichier Fichier déposé
     * @param string|null $url Eventuelle URL de téléchargement (download) du fichier déposé
     * @return string Code HTML
     */
    public function renderUploadedFile(UploadedFileInterface $fichier, string $url = null): string
    {
        return $this->getView()->render("uploaded-file.phtml", [
            'fichier' => $fichier,
            'url' => $url,
            'iconOnly' => false,
        ]);
    }

    /**
     * Génère le code HTML du lien permettant de télécharger un fichier déposé, sous la forma d'un simple icône.
     *
     * @param UploadedFileInterface $fichier Fichier déposé
     * @param string $url URL de téléchargement (download) du fichier déposé
     * @return string Code HTML
     */
    public function renderUploadedFileIcon(UploadedFileInterface $fichier, string $url): string
    {
        return $this->getView()->render("uploaded-file.phtml", [
            'fichier' => $fichier,
            'url' => $url,
            'iconOnly' => true,
        ]);
    }

    /**
     * Génère le code HTML du lien permettant de supprimer un fichier déposé.
     * Sauf si aucune URL n'est spécifiée, auquel cas aucun lien n'est généré.
     *
     * @param UploadedFileInterface $fichier Fichier à supprimer
     * @param string|null $url Eventuelle URL de la requête de suppression du fichier
     * @return string Code HTML
     */
    public function renderDeleteFile(UploadedFileInterface $fichier, ?string $url = null): string
    {
        return $this->getView()->render("delete-file.phtml", [
            'fichier' => $fichier,
            'url' => $url,
        ]);
    }

    protected ?UploadForm $form = null;

    public function setForm(UploadForm $form): static
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Retourne le formulaire de dépôt de fichier, fourni par le plugin de contrôleur.
     */
    public function getForm(): ?UploadForm
    {
        return $this->form;
    }
}