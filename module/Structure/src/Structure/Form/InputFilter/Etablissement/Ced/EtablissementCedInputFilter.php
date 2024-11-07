<?php

namespace Structure\Form\InputFilter\Etablissement\Ced;

use Doctrine\ORM\EntityManager;
use Laminas\Form\Form;
use Laminas\Validator\NotEmpty;
use Structure\Form\InputFilter\Etablissement\EtablissementInputFilter;
use Webmozart\Assert\Assert;

class EtablissementCedInputFilter extends EtablissementInputFilter
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);

        $this->add([
            'name' => 'libelle',
            'required' => false, // facultatif car sera forcé
        ]);
        $this->add([
            'name' => 'code',
            'required' => false, // facultatif car sera forcé
        ]);
        $this->add([
            'name' => 'sigle',
            'required' => false, // facultatif car sera forcé
        ]);
        $this->add([
            'name' => 'sourceCode',
            'required' => false, // facultatif car sera forcé
        ]);
        $this->add([
            'name' => 'adresse',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner l'adresse postale",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'cheminLogo',
            'required' => true,
        ]);
    }

    public function prepareForm(Form $structureForm): void
    {
        parent::prepareForm($structureForm);

        Assert::true($structureForm->getObject()->estCed(), "L'établissement bindé n'est pas valide");

        $structureForm->get('libelle')->setAttribute('disabled', 'disabled');
        $structureForm->get('code')->setAttribute('disabled', 'disabled');
        $structureForm->get('sigle')->setAttribute('disabled', 'disabled');
        $structureForm->get('sourceCode')->setAttribute('disabled', 'disabled');
        $structureForm->remove('domaine');

        $structureForm->remove('estInscription');
        $structureForm->get('estCed')->setAttribute('disabled', 'disabled');
        $structureForm->remove('estAssocie');
        $structureForm->remove('estFerme');

        $structureForm->remove('emailAssistance');
        $structureForm->remove('emailBibliotheque');
        $structureForm->remove('emailDoctorat');

        $structureForm->remove('id_ref');
        $structureForm->remove('id_hal');
    }
}