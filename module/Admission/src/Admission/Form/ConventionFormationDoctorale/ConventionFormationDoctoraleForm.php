<?php

namespace Admission\Form\ConventionFormationDoctorale;

use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;

class ConventionFormationDoctoraleForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Textarea('calendrierProjetRecherche'))
                ->setLabel("Calendrier prévisionnel du projet de recherche")
        );

        $this->add(
            (new Textarea('modalitesEncadrSuiviAvancmtRech'))
                ->setLabel("Modalités d'encadrement, de suivi de la formation et d'avancement des recherches du doctorant")
        );

        $this->add(
            (new Textarea('conditionsRealisationProjRech'))
                ->setLabel("Conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques si nécessaire")
        );

        $this->add(
            (new Textarea('modalitesIntegrationUr'))
                ->setLabel(" Modalités d'intégration dans l'unité ou l’équipe de recherche")
        );

        $this->add(
            (new Textarea('partenariatsProjThese'))
                ->setLabel("Partenariats impliqués par le projet de thèse")
        );

        $this->add(
            (new Textarea('motivationDemandeConfidentialite'))
                ->setLabel("Motivation de la demande de confidentialité par le doctorant et la direction de thèse")
        );

        $this->add(
            (new Textarea('projetProDoctorant'))
                ->setLabel("Projet professionnel du doctorant")
        );

        $this->add(new Csrf('security'), ['csrf_options' => ['timeout' => 600]]);

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Enregistrer',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'calendrierProjetRecherche' => [
                'name' => 'calendrierProjetRecherche',
                'required' => false,
            ],
            'modalitesEncadrSuiviAvancmtRech' => [
                'name' => 'modalitesEncadrSuiviAvancmtRech',
                'required' => false,
            ],
            'conditionsRealisationProjRech' => [
                'name' => 'conditionsRealisationProjRech',
                'required' => false,
            ],
            'modalitesIntegrationUr' => [
                'name' => 'modalitesIntegrationUr',
                'required' => false,
            ],
            'partenariatsProjThese' => [
                'name' => 'partenariatsProjThese',
                'required' => false,
            ],
            'motivationDemandeConfidentialite' => [
                'name' => 'motivationDemandeConfidentialite',
                'required' => false,
            ],
            'projetProDoctorant' => [
                'name' => 'projetProDoctorant',
                'required' => false,
            ],
        ];
    }
}