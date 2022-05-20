<?php

namespace Individu\View\Helper;

use Individu\Entity\Db\Individu;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\View\Helper\UserAbstract;
use Laminas\Form\Form;
use Laminas\Form\View\Helper\Form as FormHelper;
use Laminas\Form\View\Helper\FormElement;
use Laminas\View\Renderer\PhpRenderer;

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
     * @var Form
     */
    private $form;

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

        $this->form->get('individu')->setValue($this->individu->getId());
        $this->form->get('submit')->setAttribute('title', "Usurper l'identité de " . $this->individu);

        /** @var FormHelper $formHelper */
        $formHelper = $this->view->plugin('form');
        /** @var FormElement $formElementHelper */
        $formElementHelper = $this->view->plugin('formElement');

        $html = '';
        $html .= $formHelper->openTag($this->form);
        $html .= $formElementHelper->__invoke($this->form->get('individu'));
        $html .= $formElementHelper->__invoke($this->form->get('submit'));
        $html .= $formHelper->closeTag();

        return $html;
    }

    /**
     * @param Form $form
     * @return IndividuUsurpationHelper
     */
    public function setForm(Form $form): IndividuUsurpationHelper
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * Spécifie l'indiviu dont on veut usurper l'identité.
     *
     * @param Individu $individu
     * @return self
     */
    public function setIndividu(Individu $individu)
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