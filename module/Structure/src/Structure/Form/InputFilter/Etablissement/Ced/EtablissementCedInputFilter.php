<?php

namespace Structure\Form\InputFilter\Etablissement\Ced;

use Doctrine\ORM\EntityManager;
use Laminas\Validator\NotEmpty;
use Structure\Form\EtablissementForm;
use Structure\Form\InputFilter\Etablissement\EtablissementInputFilter;
use Structure\Form\InputFilter\Etablissement\EtablissementInputFilterInterface;
use Webmozart\Assert\Assert;

class EtablissementCedInputFilter extends EtablissementInputFilter implements EtablissementInputFilterInterface
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

    public function prepareForm(EtablissementForm $etablissementForm): void
    {
        Assert::true($etablissementForm->getObject()->estCed(), "L'établissement bindé n'est pas valide");

        $etablissementForm->get('libelle')->setAttribute('disabled', 'disabled');
        $etablissementForm->get('code')->setAttribute('disabled', 'disabled');
        $etablissementForm->get('sigle')->setAttribute('disabled', 'disabled');
        $etablissementForm->get('sourceCode')->setAttribute('disabled', 'disabled');
        $etablissementForm->remove('domaine');

        $etablissementForm->remove('estInscription');
        $etablissementForm->get('estCed')->setAttribute('disabled', 'disabled');
        $etablissementForm->remove('estMembre');
        $etablissementForm->remove('estAssocie');
        $etablissementForm->remove('estFerme');

        $etablissementForm->remove('emailAssistance');
        $etablissementForm->remove('emailBibliotheque');
        $etablissementForm->remove('emailDoctorat');

        $etablissementForm->remove('id_ref');
        $etablissementForm->remove('id_hal');

        if ($etablissementForm->getObject()->getId()) {
            $etablissementForm->get('sourceCode')->setAttribute('disabled', 'disabled');
        }
    }
}