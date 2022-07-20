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
 * @see    UploaderPlugin
 */
class UploaderHelper extends AbstractHelper
{
    /**
     * URL de l'action permettant de déposer un nouveau fichier.
     * C'est l'URL à laquelle est POSTé le formulaire d'upload.
     *
     * Si elle est null, le formulaire n'est pas affiché.
     *
     * @var string
     */
    protected $url;

    /**
     * Point d'entrée.
     *
     * @return UploaderHelper
     */
    public function __invoke()
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
     *
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Retourne le code HTML.
     *
     * @return string Code HTML
     */
    public function __toString()
    {
        return $this->renderForm();
    }

    /**
     * Génrère le code HTML du_ formulaire de dépôt.
     * NB: le formulaire s'affiche ssi une URL de dépôt a été spécifiée.
     *
     * @return string Code HTML
     */
    public function renderForm()
    {
        $form = $this->getForm();

        $html = $this->getView()->render("upload-form.phtml", [
            'form' => $form,
            'url'  => $this->url,
        ]);

        return $html;
    }

    /**
     * Génère le code HTML de la DIV destionée à afficher la liste des fichiers déposés.
     *
     * @param string $url   URL de dépôt (upload) de fichier
     * @param string $label Label de la liste des fichiers déposés
     * @return string Code HTML
     */
    public function renderUploadedFiles($url, $label = "Fichiers déposés :")
    {
        $html = $this->getView()->render("uploaded-files.phtml", [
            'url'   => $url,
            'label' => $label,
        ]);

        return $html;
    }

    /**
     * Génère le code HTML du lien permettant de télécharger un fichier déposé.
     * Sauf si aucune URL n'est spécifiée, auquel cas ce n'est pas un lien mais simplement le nom du fichier.
     *
     * @param UploadedFileInterface $fichier Fichier déposés
     * @param string                $url     URL de téléchargement (download) du fichier déposé
     * @return string Code HTML
     */
    public function renderUploadedFile(UploadedFileInterface $fichier, $url = null)
    {
        $html = $this->getView()->render("uploaded-file.phtml", [
            'fichier' => $fichier,
            'url'     => $url,
        ]);

        return $html;
    }

    /**
     * Génère le code HTML du lien permettant de supprimer un fichier déposé.
     * Sauf si aucune URL n'est spécifiée, auquel cas aucun lien n'est généré.
     *
     * @param UploadedFileInterface $fichier Fichier à supprimer
     * @param string                $url     URL de la requête de suppression du fichier
     * @param boolean               $confirm Faut-il afficher une demande de confirmation avant suppression ? Oui, par
     *                                       défaut.
     * @return string Code HTML
     */
    public function renderDeleteFile(UploadedFileInterface $fichier, $url = null, $confirm = true)
    {
        $html = $this->getView()->render("delete-file.phtml", [
            'fichier' => $fichier,
            'url'     => $url,
            'confirm' => $confirm,
        ]);

        return $html;
    }

    /**
     * @var UploadForm
     */
    protected $form;

    /**
     * @param UploadForm $form
     * @return UploaderHelper
     */
    public function setForm(UploadForm $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Retourne le formulaire de dépôt de fichier, fourni par le plugin de contrôleur.
     *
     * @return UploadForm
     */
    public function getForm()
    {
        return $this->form;
    }
}