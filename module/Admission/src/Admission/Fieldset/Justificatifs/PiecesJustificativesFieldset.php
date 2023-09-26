<?php
namespace Admission\Fieldset\Justificatifs;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class PiecesJustificativesFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {

        // Single file upload:
        $this->add(
            (new File('diplome_bac'))
                ->setLabel("Copie du diplôme de Bac + 5 permettant l'accès au doctorat")
        );

        $this->add(
            (new File('curicculum_vitae'))
                ->setLabel("Curriculum Vitae avec adresse, courriel et n° de téléphone")
        );

        $this->add(
            (new File('financement'))
                ->setLabel("Justificatif du financement (contrat, attestation de l'employeur)")
        );

        $this->add(
            (new File('projet_these'))
                ->setLabel("Le projet de thèse et son titre (dactylographiés) 1 à 1 page 1/2 maximum")
        );

        $this->add(
            (new File('exemplaires_convention'))
                ->setLabel("Deux exemplaires de la convention de formation doctorale")
        );

        $this->add(
            (new File('exemplaires_charte_doctorat'))
                ->setLabel("Deux exemplaires de la charte du doctorat")
        );

        $this->add(
            (new File('argumentaire_directeur_these'))
                ->setLabel("Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire")
        );

        $this->add(
            (new File('diplomes_releves_notes_traduits'))
                ->setLabel("Diplômes et relevés de notes traduits en français avec tampons originaux")
        );

        $this->add(
            (new File('argumentaire_directeur_these'))
                ->setLabel("Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire")
        );

        $this->add(
            (new File('acte_naissance'))
                ->setLabel("Extrait d'acte de naissance")
        );

        $this->add(
            (new File('photocopie_passeport'))
                ->setLabel("Photocopie du passeport (ou de la carte d'identité pour les ressortissants européens)")
        );
        $this->add(
            (new File('diplomes_travaux_experience_pro'))
                ->setLabel("Diplômes, travaux et expérience professionnelle détaillés")
        );

        $this->add(
            (new Text('documents_demande_confidentialite'))
                ->setLabel("Si demande de confidentialité : Demande à formuler et motiver dans la convention de formation doctorale")
        );
        $this->add(
            (new File('documents_cotutelle'))
                ->setLabel("Si cotutelle : Formulaire de demande de cotutelle")
        );
        $this->add(
            (new File('documents_coencadrement'))
                ->setLabel("Si co-encadrement : Formulaire de demande de co-encadrement à compléter (dès que le co-encadrant est connu)")
        );


    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'contrat_doctoral' => [
                'name' => 'contrat_doctoral',
                'required' => false,
            ],
            'employeur_contrat' => [
                'name' => 'employeur_contrat',
                'required' => false,
            ],
            'detail_contrat_doctoral' => [
                'name' => 'detail_contrat_doctoral',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
        ];
    }
}