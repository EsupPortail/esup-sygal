<?php

namespace These\Form\TheseSaisie;

use Doctorant\Form\MissionEnseignement\MissionEnseignementForm;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;
use These\Entity\Db\These;
use These\Fieldset\Direction\DirectionFieldset;
use These\Fieldset\Encadrement\EncadrementFieldset;
use These\Fieldset\Financement\FinancementFieldset;
use These\Fieldset\Generalites\GeneralitesFieldset;
use These\Fieldset\Structures\StructuresFieldset;
use UnicaenApp\Form\Element\Collection;

class TheseSaisieForm extends Form
{
    public function prepare()
    {
        /** @var These $these */
        $these = $this->getObject();
        $estModifiable = !$these->getSource()->getImportable();

        $this->get('financements')->setOptions([
            'should_create_template' => $estModifiable,
            'allow_add' => $estModifiable,
            'allow_remove' => $estModifiable,
        ]);

        return parent::prepare();
    }
    public function init(): void
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $generalitesFieldset = $this->getFormFactory()->getFormElementManager()->get(GeneralitesFieldset::class);
        $generalitesFieldset->setName("generalites");
        $generalitesFieldset->setLabel("Informations générales");
        $this->add($generalitesFieldset);

        $structuresFieldset = $this->getFormFactory()->getFormElementManager()->get(StructuresFieldset::class);
        $structuresFieldset->setName("structures");
        $structuresFieldset->setLabel("Structures encadrantes");
        $this->add($structuresFieldset);

        $directionFieldset = $this->getFormFactory()->getFormElementManager()->get(DirectionFieldset::class);
        $directionFieldset->setName("direction");
        $directionFieldset->setLabel("Direction de thèse");
        $this->add($directionFieldset);

        $encadrementFieldset = $this->getFormFactory()->getFormElementManager()->get(EncadrementFieldset::class);
        $encadrementFieldset->setName("encadrements");
        $encadrementFieldset->setLabel("Co-encadrements");
        $this->add($encadrementFieldset);

        $missionsEnseignement = new Collection('missionsEnseignement');
        $missionsEnseignement
            ->setLabel("Mission·s d'enseignement")
            ->setMinElements(0)
            ->setOptions([
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    MissionEnseignementForm::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($missionsEnseignement);

        $financements = new Collection('financements');
        $financements
            ->setMinElements(0)
            ->setOptions([
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    FinancementFieldset::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($financements);

        $this
            ->add(new Csrf('security'))
            ->add([
                'type' => Button::class,
                'name' => 'submit',
                'options' => [
                    'label' => '<span class="icon icon-save"></span> Enregistrer',
                    'label_options' => [
                        'disable_html_escape' => true,
                    ],
                ],
                'attributes' => [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                ],
            ]);
    }
}