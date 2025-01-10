<?php

namespace These\Fieldset\TitreAcces;

use Application\Entity\Db\Pays;
use Application\Service\TitreAcces\TitreAccesServiceAwareTrait;
use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\NotEmpty;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Service\EntityManagerAwareTrait;

class TitreAccesFieldset extends Fieldset implements InputFilterProviderInterface
{
    use EtablissementServiceAwareTrait;
    use EntityManagerAwareTrait;

    private array $typesEtabTitreAcces;

    /**
     * @param array $unitesRecherche
     */
    public function setTypesEtabTitreAcces(array $typesEtabTitreAcces): void
    {
        $this->typesEtabTitreAcces = $typesEtabTitreAcces;
    }
    
    public function prepareElement(FormInterface $form): void
    {
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();
        
        $this->get('titreAccesInterneExterne')->setAttribute('disabled', !$estModifiable);
        $this->get('libelleTitreAcces')->setAttribute('readonly', !$estModifiable);
        $this->get('pays')->setAttribute('disabled', !$estModifiable);
        $this->get('etablissement')->setAttribute('disabled', !$estModifiable);
        $this->get('codeDeptTitreAcces')->setAttribute('disabled', !$estModifiable);
        $this->get('nomDeptTitreAcces')->setAttribute('disabled', !$estModifiable);
        $this->get('typeEtabTitreAcces')->setValueOptions($this->typesEtabTitreAcces);
        $this->get('typeEtabTitreAcces')->setEmptyOption("Sélectionnez le type d'établissement");
        $this->get('typeEtabTitreAcces')->setAttribute('disabled', !$estModifiable);

        parent::prepareElement($form); // TODO: Change the autogenerated stub
    }

    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Select("titreAccesInterneExterne"))
                ->setEmptyOption("Sélectionnez le type d'accès")
                ->setValueOptions([
                    'E' => 'Externe',
                    'I' => 'Interne',
                ])
                ->setLabel("Accès <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true, ])
                ->setAttributes([
                    'class' => 'selectpicker show-tick',
                    'id' => "titreAccesInterneExterne"
                ])
        );

        $this->add(
            (new Text("libelleTitreAcces"))
                ->setLabel("Libellé <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> : ")
                ->setLabelOptions(['disable_html_escape' => true, ])
        );

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'pays',
            'options' => [
                'label' => "Pays d'obtention <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'target_class' => Pays::class,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'pays',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner le pays",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add(
            (new Text('nomDeptTitreAcces'))
                ->setLabel("Département :")
                ->setAttributes([
                    'id' => "nomDeptTitreAcces",
                    'placeholder' => "Entrez les deux premières lettres...",
                    'class' => 'selectpicker show-tick',
                ])
        );

        $this->add(
            (new Hidden('codeDeptTitreAcces'))
            ->setAttribute('id', "codeDeptTitreAcces")
        );

        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'etablissement',
            'options' => [
                'label' => "Établissement <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'target_class' => Etablissement::class,
                'find_method' => [
                    'name' => 'findAll',
                ],
                'label_generator' => function($targetEntity) {
                    $sigle = $targetEntity->getStructure() && $targetEntity->getStructure()->getSigle() ? " (".$targetEntity->getStructure()->getSigle().")" : null;
                    return $targetEntity->getStructure()?->getLibelle() . $sigle;
                },
            ],
            'attributes' => [
                'id' => 'etablissement',
                'class' => 'selectpicker show-menu-arrow',
                'title' => "Sélectionner l'établissement",
                'data-live-search' => 'true',
            ],
        ]);

        $this->add(
            (new Select("typeEtabTitreAcces"))
                ->setLabel("Type d'établissement :")
                ->setAttributes([
                    'class' => 'selectpicker show-menu-arrow',
                    'data-live-search' => 'true',
                    'id' => 'typeEtabTitreAcces',
                ])
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();
        return [
            'titreAccesInterneExterne' => [
                'required' => $estModifiable,
            ],
            'libelleTitreAcces' => [
                'required' => $estModifiable,
            ],
            'pays' => [
                'required' => $estModifiable,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner un pays.',
                            ],
                        ],
                    ],
                ],
            ],
            'etablissement' => [
                'required' => $estModifiable,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => 'Veuillez sélectionner un établissement.',
                            ],
                        ],
                    ],
                ],
            ],
            'typeEtabTitreAcces' => [
                'required' => false,
            ],
        ];
    }
}