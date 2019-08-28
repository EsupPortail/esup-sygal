<?php

namespace Application\Controller\Plugin\Uploader;

use UnicaenApp\Filter\BytesFormatter;
use UnicaenApp\Util;
use Zend\Form\Element\Csrf;
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
     * @var bool
     */
    private $ajaxMode = true;

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
//        $this->setUploadMaxFilesize($this->iniGetUploadMaxFilesize() - 1);

        $this
            ->addElements()
            ->addInputFilter();
    }

    /**
     * @return bool
     */
    public function isAjaxMode()
    {
        return $this->ajaxMode;
    }

    /**
     * @param bool $ajaxMode
     * @return $this
     */
    public function setAjaxMode($ajaxMode = true)
    {
        if ($this->ajaxMode && ! $ajaxMode) {
            $this->addSubmitButton();
        }

        $this->ajaxMode = $ajaxMode;

        return $this;
    }

    private function addSubmitButton()
    {
        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'options' => [
                'label' => "Téléverser",
            ],
            'attributes' => [
                'class' => 'upload-file',
            ],
        ]);
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

        $this->add(new Csrf('csrf'));

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

        return $this->fileSizeValidator;
    }

    /**
     * @return $this
     */
    private function updateFileSizeValidator()
    {
        $uploadMaxFilesizeIni = $this->iniGetUploadMaxFilesize();

        $fileSizeTooBigMessage = sprintf("Vous ne pouvez pas déposer de fichier dont la taille excède %s",
            $this->formatUploadMaxFilesize($uploadMaxFilesizeIni));

        $this->fileSizeValidator
            ->setMax($uploadMaxFilesizeIni)
            ->setMessage($fileSizeTooBigMessage, FileSizeValidator::TOO_BIG);

        return $this;
    }

    /**
     * Retourne la taille maxi de chaque fichier uploadable, d'après la config PHP.
     *
     * @return integer
     */
    public function getUploadMaxFilesize()
    {
        return $this->iniGetUploadMaxFilesize();
    }

    /**
     * @param string $maxFilesize
     * @return string
     */
    private function formatUploadMaxFilesize($maxFilesize)
    {
        $f = new BytesFormatter();

        return $f->filter($maxFilesize);
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