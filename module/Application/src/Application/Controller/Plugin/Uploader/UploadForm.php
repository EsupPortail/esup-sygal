<?php

namespace Application\Controller\Plugin\Uploader;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Filter\BytesFormatter;
use UnicaenApp\Util;
use Zend\Form\Element\Hidden;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Size as FileSizeValidator;

/**
 * Formulaire de dépôt de fichier.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UploadForm extends Form
{
    /**
     * @var array
     */
    private $addedElements = [];

    /**
     * Constructeur.
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

//        $this->setAttribute('id', "upload-form");
    }

    /**
     *
     */
    public function init()
    {
        $this->setUploadMaxFilesize($this->iniGetUploadMaxFilesize() - 1);

        $this
            ->addElements()
            ->addInputFilter();
    }

    /**
     * Permet d'ajouter un élément au formulaire sans pour autant avoir à fournir le script de rendu du formulaire.
     * Exemple: un champ caché.
     *
     * @param array|\Traversable|\Zend\Form\ElementInterface $elementOrFieldset
     * @param array                                          $flags
     * @return $this
     */
    public function addElement($elementOrFieldset, array $flags = [])
    {
        parent::add($elementOrFieldset, $flags);

        $this->addedElements[$elementOrFieldset->getName()] = $elementOrFieldset;

        return $this;
    }

    public function isValid()
    {
        $this->updateFileSizeValidator();

        return parent::isValid();
    }

    /**
     * @return array
     */
    public function getAddedElements()
    {
        return $this->addedElements;
    }

    /**
     *
     */
    private function addElements()
    {
        /**
         * Id
         */
        $this->add(new Hidden('id'));

        /**
         * Fichiers
         */
        $this->add([
            'name' => 'files',
            'type' => 'File',
            'options' => [
                'label' => "Déposer un fichier :",
                'label_attributes' => [
                    'title' => "Niveau",
                    'disable_html_escape' => true,
                ],
                'label_options' => ['disable_html_escape' => true],
            ],
            'attributes' => [
                'id' => 'files',
                'multiple' => true,
            ],
        ]);

        return $this;
    }

    /**
     *
     * @return self
     */
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();

        // File Input
        $fileInput = new FileInput('files');
        $fileInput->setRequired(true);

        // You only need to define validators and filters
        // as if only one file was being uploaded. All files
        // will be run through the same validators and filters
        // automatically.
        $fileInput->getValidatorChain()
            ->attach($this->getFileSizeValidator())
//            ->attachByName('filesize', array('max' => 1024*1024*2 )) // 2 Mo
//            ->attachByName('filemimetype', array('mimeType' => 'image/bmp'))
//            ->attachByName('fileimagesize', array('maxWidth' => 100, 'maxHeight' => 100))
        ;

        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);

        return $this;
    }

    /**
     * @var FileSizeValidator
     */
    private $fileSizeValidator;

    /**
     * Retourne le validateur de taille de fichier uploadable.
     * NB: met à jour systématiquement le paramètre 'max' du validateur à partir de l'attribut correspondant.
     *
     * @return FileSizeValidator
     */
    private function getFileSizeValidator()
    {
        if (null === $this->fileSizeValidator) {
            $this->fileSizeValidator = new FileSizeValidator();
        }

//        $fileSizeTooBigMessage = sprintf("Vous ne pouvez pas déposer de fichier dont la taille excède %s",
//            $this->getUploadMaxFilesizeFormatted());
//
//        $this->fileSizeValidator
//            ->setMax($this->getUploadMaxFilesize())
//            ->setMessage($fileSizeTooBigMessage, FileSizeValidator::TOO_BIG);

        return $this->fileSizeValidator;
    }

    /**
     * @return $this
     */
    private function updateFileSizeValidator()
    {
        $uploadMaxFilesize    = $this->getUploadMaxFilesize();
        $uploadMaxFilesizeIni = $this->iniGetUploadMaxFilesize();

        if ($uploadMaxFilesizeIni && $uploadMaxFilesizeIni <= $uploadMaxFilesize) {
            throw new LogicException(sprintf(
                "La taille max spécifiée (%s) doit être inférieure STRICTEMENT à %s "
                . "(valeur du paramètre de config 'upload_max_filesize' ou 'post_max_size') sinon le validateur ne peut entrer en action.",
                $uploadMaxFilesize,
                $uploadMaxFilesizeIni));
        }

        $fileSizeTooBigMessage = sprintf("Vous ne pouvez pas déposer de fichier dont la taille excède %s",
            $this->getUploadMaxFilesizeFormatted());

        $this->fileSizeValidator
            ->setMax($uploadMaxFilesize)
            ->setMessage($fileSizeTooBigMessage, FileSizeValidator::TOO_BIG);

        return $this;
    }

    /**
     * @var integer
     */
    private $uploadMaxFilesize;

    /**
     * Spécifie la taille maxi de chaque fichier uploadable.
     *
     * @param integer $uploadMaxFilesize Taille max en octets
     * @return self
     * @throws LogicException Si la taille max spécifiée dépasse OU ÉGALE la valeur du paramètre de config 'upload_max_filesize'
     */
    public function _setUploadMaxFilesize($uploadMaxFilesize)
    {
        $uploadMaxFilesize    = Util::convertAsBytes($uploadMaxFilesize);
        $uploadMaxFilesizeIni = $this->iniGetUploadMaxFilesize();

        if ($uploadMaxFilesizeIni && $uploadMaxFilesizeIni <= $uploadMaxFilesize) {
            throw new LogicException(sprintf(
                "La taille max spécifiée (%s) doit être inférieure STRICTEMENT à %s "
                . "(valeur du paramètre de config 'upload_max_filesize' ou 'post_max_size') sinon le validateur ne peut entrer en action.",
                $uploadMaxFilesize,
                $uploadMaxFilesizeIni));
        }

        $this->uploadMaxFilesize = $uploadMaxFilesize;

        $this->getFileSizeValidator();

        return $this;
    }
    public function setUploadMaxFilesize($uploadMaxFilesize)
    {
        $uploadMaxFilesize    = Util::convertAsBytes($uploadMaxFilesize);

        $this->uploadMaxFilesize = $uploadMaxFilesize;

        return $this;
    }

    /**
     * Retourne la taille maxi de chaque fichier uploadable.
     *
     * @return integer
     */
    public function getUploadMaxFilesize()
    {
        return $this->uploadMaxFilesize;
    }

    /**
     * Retourne la taille maxi de chaque fichier uploadable.
     *
     * @return integer
     */
    public function getUploadMaxFilesizeFormatted()
    {
        $f = new BytesFormatter();

        return $f->filter($this->getUploadMaxFilesize());
    }

    /**
     * Retourne la valeur numérique du paramètre de config le plus restrictif parmi :
     *  - 'upload_max_filesize'
     *  - 'post_max_size '.
     *
     * @return integer
     */
    private function iniGetUploadMaxFilesize()
    {
        $upload_max_filesize = ini_get('upload_max_filesize') ?: null;
        $post_max_size       = ini_get('post_max_size') ?: null;

        $params = [$upload_max_filesize, $post_max_size];

        $limits = array_filter(array_map(function($v) {
            return $v !== 0 ? $v : null;
        }, $params));
        if (empty($limits)) {
            return null;
        }

        $limits = array_map(function($v) {
            return Util::convertAsBytes($v);
        }, $limits);

        return min($limits);
    }
}