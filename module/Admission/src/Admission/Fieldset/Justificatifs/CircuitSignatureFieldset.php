<?php
namespace Admission\Fieldset\Justificatifs;

use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;

class CircuitSignatureFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {

        $this->add(
            (new Text('validation_gestionnaires'))
                ->setLabel("Validité des gestionnaires")
        );

        $this->add(
            (new Text('validation_directeurthese'))
                ->setLabel("Validité du directeur de thèse")
        );

        $this->add(
            (new Text('validation_codirecteur'))
                ->setLabel("Validité du co-directeur")
        );

        $this->add(
            (new Text('validation_uniterecherche'))
                ->setLabel("Validité de l'Unité de recherche")
        );

        $this->add(
            (new Text('validation_ecoledoctorale'))
                ->setLabel("Validité de l'école doctorale")
        );

        $this->add(
            (new Text('signature_president'))
                ->setLabel("Signature du président de l'Université")
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'validation_gestionnaires' => [
                'name' => 'validation_gestionnaires',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'], // Validez l'extension du fichier (par exemple, autorisez seulement les fichiers JPG et PNG)
                        ],
                    ],
                ],
            ],
            'intitule_du_diplome' => [
                'name' => 'intitule_du_diplome',
                'required' => false,
            ],
            'annee_dobtention_diplome' => [
                'name' => 'annee_dobtention_diplome',
                'required' => false,
            ],
            'etablissement_dobtention_diplome' => [
                'name' => 'etablissement_dobtention_diplome',
                'required' => false,
            ],
            'type_diplome_autre' => [
                'name' => 'type_diplome_autre',
                'required' => false,
            ],
        ];
    }
}