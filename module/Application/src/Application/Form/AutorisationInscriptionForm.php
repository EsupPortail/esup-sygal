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
        );

        $this->add(new Csrf('security'), ['csrf_options' => ['timeout' => 600]]);

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'autorisationInscription' => [
                'name' => 'autorisationInscription',
                'required' => true,
            ],
            'commentaires' => [
                'name' => 'commentaires',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
        ];
    }
}