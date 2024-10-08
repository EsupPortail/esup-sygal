<?php
namespace Application\Form;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;

class AutorisationInscriptionForm extends Form implements InputFilterProviderInterface
{

    public function init()
    {
        $this->add(
            (new Hidden('id'))
        );

        $this->add(
            (new Radio('autorisationInscription'))
                ->setValueOptions([
                    1 => "Oui",
                    0 => "Non",
                ])
                ->setAttributes(['id' => 'autorisationInscription'])
                ->setLabel("Autorisez-vous l'inscription dans l'année suivante ?")
        );

        $this->add(
            (new Hidden('individu'))
        );

        $this->add(
            (new Hidden('these'))
        );

        $this->add(
            (new Hidden('anneeUniv'))
        );

        $this->add(
            (new Hidden('rapport'))
        );

        $this->add(
            (new Text('commentaires'))
                ->setLabel("Commentaires")
                ->setAttributes(['id' => 'commentaires'])
        );

        $this->add(new Csrf('security'), ['csrf_options' => ['timeout' => 600]]);

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    public function getInputFilterSpecification(): array
    {
        $autorisationPositive = (bool) $this->get('autorisationInscription')->getValue();
        return [
            'autorisationInscription' => [
                'name' => 'autorisationInscription',
                'required' => true,
            ],
            'commentaires' => [
                'required' => !$autorisationPositive,
            ],
        ];
    }
}