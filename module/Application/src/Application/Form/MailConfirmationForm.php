<?php

namespace Application\Form;

use Application\Entity\Db\MailConfirmation;
use Application\Utils\FormUtils;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\Factory;
use Laminas\Validator\Callback;
use Webmozart\Assert\Assert;

class MailConfirmationForm extends Form
{
    private bool $refusListeDiffInterdit = false;

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->add(new Csrf('security'));

        $this->add(new Hidden('id'));
        $this->add(new Hidden('idIndividu'));
        $this->add((new Text('individu'))
            ->setLabel("Votre identité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
            ->setLabelOptions(['disable_html_escape' => true,])
            ->setAttribute('readonly', 'readonly')
        );
        $this->add((new Text('email'))
            ->setLabel("Votre adresse électronique <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
            ->setLabelOptions(['disable_html_escape' => true,])
        );
        $this->add((new Checkbox('refusListeDiff'))
            ->setLabel("Je refuse de recevoir sur cette adresse les messages des listes de diffusion ?")
        );
        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'individu' => [
                'name' => 'individu',
                'required' => true,
            ],
            'email' => [
                'name' => 'email',
                'required' => true,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => "L'adresse électronique fournie n'est pas valide.",
                            ],
                            'callback' => fn($value) => filter_var($value, FILTER_VALIDATE_EMAIL),
                        ],
                    ]
                ],
            ],
            'refusListeDiff' => [
                'name' => 'refusListeDiff',
                'required' => false,
            ],
        ]));
    }

    public function bind($object, $flags = FormInterface::VALUES_NORMALIZED): MailConfirmationForm
    {
        Assert::isInstanceOf($object, MailConfirmation::class);

        return parent::bind($object, $flags);
    }

    public function prepare(): self
    {
        if ($this->refusListeDiffInterdit) {
            $this->get('refusListeDiff')
                ->setValue(false)
                ->setAttribute('disabled', 'disabled');
        }

        return parent::prepare();
    }

    public function setRefusListeDiffInterdit(bool $refusListeDiffInterdit)
    {
        $this->refusListeDiffInterdit = $refusListeDiffInterdit;
    }
}