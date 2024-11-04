<?php

namespace Admission\Form\ConventionFormationDoctorale;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class ConventionFormationDoctoraleForm extends Form implements InputFilterProviderInterface
{
    private bool $disableMotivationDemandeConfidentialite = false;

    /**
     * @param bool $disable
     */
    public function disableMotivationDemandeConfidentialite(bool $disable = true): void
    {
        $this->disableMotivationDemandeConfidentialite = $disable;
    }
    public function __construct()
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
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Textarea('modalitesEncadrSuiviAvancmtRech'))
                ->setLabel("Modalités d'encadrement, de suivi de la formation et d'avancement des recherches du doctorant")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Textarea('conditionsRealisationProjRech'))
                ->setLabel("Conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques si nécessaire")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Textarea('modalitesIntegrationUr'))
                ->setLabel(" Modalités d'intégration dans l'unité ou l’équipe de recherche")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Textarea('partenariatsProjThese'))
                ->setLabel("Partenariats impliqués par le projet de thèse")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Textarea('motivationDemandeConfidentialite'))
                ->setLabel("Motivation de la demande de confidentialité par le doctorant et la direction de thèse")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Textarea('projetProDoctorant'))
                ->setLabel("Projet professionnel du doctorant")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(new Csrf('security'), ['csrf_options' => ['timeout' => 600]]);

        FormUtils::addSaveButton($this);
    }

    /**
     * @return Form
     */
    public function prepare(): Form
    {
        parent::prepare();

        if ($this->disableMotivationDemandeConfidentialite) {
            $this->remove('motivationDemandeConfidentialite');
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'calendrierProjetRecherche' => [
                'name' => 'calendrierProjetRecherche',
                'required' => true,
            ],
            'modalitesEncadrSuiviAvancmtRech' => [
                'name' => 'modalitesEncadrSuiviAvancmtRech',
                'required' => true,
            ],
            'conditionsRealisationProjRech' => [
                'name' => 'conditionsRealisationProjRech',
                'required' => true,
            ],
            'modalitesIntegrationUr' => [
                'name' => 'modalitesIntegrationUr',
                'required' => true,
            ],
            'partenariatsProjThese' => [
                'name' => 'partenariatsProjThese',
                'required' => true,
            ],
            'motivationDemandeConfidentialite' => [
                'name' => 'motivationDemandeConfidentialite',
                'required' => !$this->disableMotivationDemandeConfidentialite,
            ],
            'projetProDoctorant' => [
                'name' => 'projetProDoctorant',
                'required' => true,
            ],
        ];
    }
}