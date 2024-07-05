<?php

namespace These\Form\TheseSaisie;

use Doctorant\Form\MissionEnseignement\MissionEnseignementForm;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
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
    public function prepare(): self
    {
        /** @var These $these */
        $these = $this->getObject();

        $estModifiable = ! $these->getSource()->getImportable();

        $this->get('generalites')->get('doctorant')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('titre')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('discipline')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('confidentialite')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('dateFinConfidentialite')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('datePremiereInscription')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('titreAcces')->get('titreAccesInterneExterne')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('titreAcces')->get('libelleTitreAcces')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('titreAcces')->get('pays')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('titreAcces')->get('etablissement')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('dateAbandon')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('dateTransfert')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('resultat')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('cotutelle')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('etablissementCoTutelle')->setAttribute('disabled', !$estModifiable);
        $this->get('generalites')->get('paysCoTutelle')->setAttribute('disabled', !$estModifiable);
        $this->get('structures')->get('etablissement')->setAttribute('disabled', !$estModifiable);
        $this->get('structures')->get('uniteRecherche')->setAttribute('disabled', !$estModifiable);
        $this->get('structures')->get('ecoleDoctorale')->setAttribute('disabled', !$estModifiable);
        $this->get('direction')->get('directeur-individu')->setAttribute('disabled', !$estModifiable);
        $this->get('direction')->get('directeur-etablissement')->setAttribute('disabled', !$estModifiable);
        $this->get('direction')->get('directeur-ecoleDoctorale')->setAttribute('disabled', !$estModifiable);
//        $this->get('direction')->get('directeur-uniteRecherche')->setAttribute('disabled', !$estModifiable);
        $this->get('direction')->get('directeur-qualite')->setAttribute('disabled', !$estModifiable);

        for ($i = 1; $i <= DirectionFieldset::NBCODIR; $i++) {
            $this->get('direction')->get('codirecteur' . $i . '-enabled')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur' . $i . '-individu')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur' . $i . '-etablissement')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur' . $i . '-ecoleDoctorale')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur' . $i . '-uniteRecherche')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur' . $i . '-qualite')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur' . $i . '-principal')->setAttribute('disabled', !$estModifiable);
            $this->get('direction')->get('codirecteur2-exterieur')->setAttribute('disabled', !$estModifiable);
        }

        foreach ($this->get('financements') as $financement) {
            $financement->get('annee')->setAttribute('disabled', !$estModifiable);
            $financement->get('origineFinancement')->setAttribute('disabled', !$estModifiable);
            $financement->get('complementFinancement')->setAttribute('disabled', !$estModifiable);
            $financement->get('quotiteFinancement')->setAttribute('disabled', !$estModifiable);
        }

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
            ->setLabel("Financement")
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
            ->add((new Submit('submit'))->setValue('Enregistrer'));
    }
}