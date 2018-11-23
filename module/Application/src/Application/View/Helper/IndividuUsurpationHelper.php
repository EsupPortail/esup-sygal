<?php

namespace Application\View\Helper;

use Application\Entity\Db\Individu;
use UnicaenApp\Form\View\Helper\FormControlGroup;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\View\Helper\UserAbstract;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Zend\Form\View\Helper\Form as FormHelper;
use Zend\Form\View\Helper\FormElement;
use Zend\View\Renderer\PhpRenderer;

/**
 * Aide de vue dessinat un bouton permettant d'usurper l'identité d'un individu.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class IndividuUsurpationHelper extends UserAbstract
{
    /**
     * @var PhpRenderer
     */
    protected $view;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @var string
     */
    private $url;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * @var bool
     */
    private $usurpationEnabled = false;

    /**
     * Point d'entrée.
     *
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->usurpationEnabled) {
            return '';
        }

        /** @var FormHelper $formHelper */
        $formHelper = $this->view->plugin('form');
        /** @var FormElement $formElementHelper */
        $formElementHelper = $this->view->plugin('formElement');

        $form = new Form('individu-usurpation-form');
        $form->setAttributes([
            'class'  => 'individu-usurpation-form',
            'action' => $this->url,
        ]);

        $hidden = null;
        $hidden = new Hidden('individu');
        $hidden->setValue($this->individu->getId());
        $hidden->setAttributes([
            'id' => 'individu-usurpation-hidden',
        ]);

        $submit = new Submit('submit');
        $submit->setValue("Usurper");
        $submit->setAttributes([
            'class' => 'individu-usurpation-submit btn btn-danger btn-xs',
            'title' => "Usurper l'identité de " . $this->individu,
        ]);

        $html = '';
        $html .= $formHelper->openTag($form);
        $html .= $formElementHelper->__invoke($hidden);
        $html .= $formElementHelper->__invoke($submit);
        $html .= $formHelper->closeTag();

        return $html;
    }

    /**
     * Spécifie l'URL à laquelle sont POSTées les données du formulaire.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Spécifie l'indiviu dont on veut usurper l'identité.
     *
     * @param Individu $individu
     * @return self
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Injecte les options du module unicaen/auth.
     *
     * @param ModuleOptions $moduleOptions
     * @return self
     */
    public function setModuleOptions(ModuleOptions $moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * Active ou non la possibilité d'usurper une identité.
     *
     * @param bool $usurpationEnabled
     * @return self
     */
    public function setUsurpationEnabled($usurpationEnabled = true)
    {
        $this->usurpationEnabled = $usurpationEnabled;

        return $this;
    }
}