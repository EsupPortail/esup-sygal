<?php

namespace Fichier\Controller\Plugin\Uploader;

use UnicaenApp\Util;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\View\Model\JsonModel;

/**
 * Plugin facilitant le dépôt de fichier.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UploaderPlugin extends AbstractPlugin
{
    /**
     * Magic method.
     *
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Dépôt d'un fichier
     *
     * @todo Améliorations possibles :
     * - retourner tous les résultats au format JSON ;
     * - s'inspirer de https://github.com/cgmartin/ZF2FileUploadExamples
     *
     * @return array|JsonModel|bool <code>false</code> si la requête n'est pas de type POST,
     *                              <code>array</code> si la requête est de type POST et que le formulaire est valide,
     *                              <code>JsonModel</code> si la requête est de type POST et que le formulaire est invalide.
     */
    public function upload()
    {
        /** @var Request $request */
        $request = $this->getController()->getRequest();
        $form    = $this->getForm();

        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
            }
            else {
                // extraction des messages d'info (ce sont les feuilles du tableau)
                $errors = Util::extractArrayLeafNodes($form->getMessages());

                return new JsonModel(['errors' => $errors]);
            }

            return $data;
        }

        return false;
    }

    /**
     * Téléchargement d'un fichier déposé
     *
     * @param UploadedFileInterface $fichier
     */
    public function download(UploadedFileInterface $fichier)
    {
        $content = $fichier->getContenu();
        $contentType = $fichier->getTypeMime() ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $fichier->getNom() . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        echo $content;
        exit;
    }

    /**
     * @var UploadForm
     */
    protected $form;

    /**
     * @param UploadForm $form
     * @return UploaderPlugin
     */
    public function setForm(UploadForm $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Retourne le formulaire de dépôt de fichier.
     *
     * @return UploadForm
     */
    public function getForm()
    {
        return $this->form;
    }
}