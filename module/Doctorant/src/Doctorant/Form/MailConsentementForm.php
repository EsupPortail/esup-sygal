<?php

namespace Doctorant\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class MailConsentementForm extends Form implements InputFilterProviderInterface
{
    private bool $refusInterdit = false;

    public function setRefusInterdit(bool $refusInterdit)
    {
        $this->refusInterdit = $refusInterdit;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->setAttribute('method', 'post');

        $this->add(new Csrf('security'));

        $this->add(new Hidden('id'));
        $this->add(new Hidden('idIndividu'));

        $this->add((new Text('email'))
            ->setLabel("Adresse électronique concernée :")
            ->setAttribute('readonly', 'readonly')
        );

        $this->add([
            'name' => 'refusListeDiff',
            'type' => Checkbox::class,
            'options' => [
                'label' => "Je refuse de recevoir sur cette adresse les messages des listes de diffusion",
            ],
            'attributes' => [
                'id' => 'refusListeDiff',
            ],
        ]);

        FormUtils::addSaveButton($this);
    }

    public function prepare(): self
    {
        if ($this->refusInterdit) {
            $this->get('refusListeDiff')
                ->setValue(false)
                ->setAttribute('disabled', 'disabled');
        }

        return parent::prepare();
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'email' => [
                'required' => false,
            ],
            'refusListeDiff' => [
                'required' => false,
            ],
        ];
    }
}