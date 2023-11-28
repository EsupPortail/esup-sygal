<?php
namespace Admission\Form\Fieldset\Validation;

use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Element\Url;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;

class AdmissionValidationFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->add(
            (new Url('attestationHonneurInformations'))
                ->setLabel("J'atteste sur l'honneur l'exactitude des informations renseignées ci-dessus")
                ->setAttribute("code", "ATTESTATION_HONNEUR")
        );

        $this->add(
            (new Url('validationGestionnaires'))
                ->setLabel("Vérification effectuée par les gestionnaires")
                ->setAttribute("code", "VALIDATION_GESTIONNAIRE")
        );

        $this->add(
            (new Text('validationDirecteurthese'))
                ->setLabel("Validation de la Direction de thèse")
                ->setAttribute("code", "VALIDATION_DIRECTION_THESE")
        );

        $this->add(
            (new Text('validationCodirecteur'))
                ->setLabel("Validation de la Co-direction")
                ->setAttribute("code", "VALIDATION_CO_DIRECTION_THESE")
        );

        $this->add(
            (new Text('validationUniterecherche'))
                ->setLabel("Validation de l'Unité de recherche")
                ->setAttribute("code", "VALIDATION_UR")
        );

        $this->add(
            (new Text('validationEcoledoctorale'))
                ->setLabel("Validation de l'École doctorale")
                ->setAttribute("code", "VALIDATION_ED")
        );

        $this->add(
            (new Text('signaturePresident'))
                ->setLabel("Signature de la Présidence de l'établissement d'inscription")
                ->setAttribute("code", "SIGNATURE_PRESIDENT")
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationValidation");
        $this->add($verificationFieldset);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            //Circuit signatures
            'validationGestionnaires' => [
                'name' => 'validationGestionnaires',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'intituleDuDiplome' => [
                'name' => 'intituleDuDiplome',
                'required' => false,
            ],
            'anneeDobtentionDiplome' => [
                'name' => 'anneeDobtentionDiplome',
                'required' => false,
            ],
            'etablissementDobtentionDiplome' => [
                'name' => 'etablissementDobtentionDiplome',
                'required' => false,
            ],
            'typeDiplomeAutre' => [
                'name' => 'typeDiplomeAutre',
                'required' => false,
            ],
        ];
    }
}