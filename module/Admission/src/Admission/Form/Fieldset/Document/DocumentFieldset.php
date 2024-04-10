<?php
namespace Admission\Form\Fieldset\Document;

use Admission\Form\Fieldset\AdmissionBaseFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\InputFilter\InputFilterProviderInterface;

class DocumentFieldset extends AdmissionBaseFieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->add(
            (new File('diplomeBac'))
                ->setLabel("Copie du diplôme de Bac + 5 permettant l'accès au doctorat")
                ->setAttributes(["id" => "ADMISSION_DIPLOME_BAC"])
                ->setLabelAttributes(['data-after' => " / Copy of the Bac +5 diploma allowing access to the doctorate"])
        );

        $this->add(
            (new File('curicculumVitae'))
                ->setLabel("Curriculum Vitae avec adresse, courriel et n° de téléphone")
                ->setAttributes(["id" => "ADMISSION_CURRICULUM_VITAE"])
                ->setLabelAttributes(['data-after' => " / Curriculum Vitae with address, email and telephone number"])
        );

        $this->add(
            (new File('financement'))
                ->setLabel("Justificatif du financement (contrat, attestation de l'employeur)")
                ->setAttributes(["id" => "ADMISSION_FINANCEMENT"])
                ->setLabelAttributes(['data-after' => " / Proof of funding (contract, employer's certificate, etc.)"])
        );

        $this->add(
            (new File('projetThese'))
                ->setLabel("Le projet de thèse et son titre (dactylographiés) 1 à 1 page 1/2 maximum")
                ->setAttributes(["id" => "ADMISSION_PROJET_THESE"])
                ->setLabelAttributes(['data-after' => " / The thesis proposal and its title (typed)"])
        );

        $this->add(
            (new File('exemplairesConvention'))
                ->setLabel("Convention de formation doctorale")
                ->setAttributes(["id" => "ADMISSION_CONVENTION"])
                ->setLabelAttributes(['data-after' => " / Doctoral Training Agreement"])
        );

        $this->add(
            (new File('exemplairesCharteDoctorat'))
                ->setLabel("Charte du doctorat")
                ->setAttributes(["id" => "ADMISSION_CHARTE_DOCTORAT"])
                ->setLabelAttributes(['data-after' => " / PhD Charter"])
        );

        $this->add(
            (new File('diplomesRelevesNotesTraduits'))
                ->setLabel("Diplômes et relevés de notes traduits en français avec tampons originaux")
                ->setAttributes(["id" => "ADMISSION_DIPLOMES_RELEVES_TRADUITS"])
                ->setLabelAttributes(['data-after' => " / Diplomas and transcripts translated into French"])
        );

        $this->add(
            (new Textarea('argumentaireDirecteurThese'))
                ->setAttributes(["id" => "argumentaireDirecteurThese"])
        );

        $this->add(
            (new File('acteNaissance'))
                ->setLabel("Extrait d'acte de naissance")
                ->setAttributes(["id" => "ADMISSION_ACTE_NAISSANCE"])
                ->setLabelAttributes(['data-after' => " / Birth certificate"])
        );

        $this->add(
            (new File('photocopiePasseport'))
                ->setLabel("Photocopie du passeport (ou de la carte d'identité pour les ressortissants européens)")
                ->setAttributes(["id" => "ADMISSION_PASSEPORT"])
                ->setLabelAttributes(['data-after' => " / Photocopy of passport (or identity card for EU nationals)"])
        );
        $this->add(
            (new File('diplomesTravauxExperiencePro'))
                ->setLabel("Diplômes, travaux et expérience professionnelle détaillés")
                ->setAttributes(["id" => "ADMISSION_DIPLOMES_TRAVAUX_EXPERIENCE_PRO"])
                ->setLabelAttributes(['data-after' => " / Detailed diplomas, work and professional experience"])
        );

        $this->add(
            (new Text('documentsDemandeConfidentialite'))
                ->setLabel("Demande de confidentialité à formuler et motiver dans la convention de formation doctorale")
                ->setLabelAttributes(['data-after' => " / If confidentiality is requested: Request to be formulated and justified in the doctoral training agreement"])
        );
        $this->add(
            (new File('documentsCotutelle'))
                ->setLabel("Formulaire de demande de cotutelle")
                ->setAttributes(["id" => "ADMISSION_DEMANDE_COTUTELLE"])
        );
        $this->add(
            (new File('documentsCoencadrement'))
                ->setLabel("Formulaire de demande de co-encadrement à compléter (dès que le co-encadrant est connu)")
                ->setAttributes(["id" => "ADMISSION_DEMANDE_COENCADREMENT"])
        );
        $this->add(
            (new File('recapitulatifDossierSigne'))
                ->setLabel("Récapitulatif du dossier d'admission signé par la direction de l'établissement")
                ->setAttributes(["id" => "ADMISSION_RECAPITULATIF_DOSSIER_SIGNE"])
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationDocument");
        $this->add($verificationFieldset);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [];
    }
}