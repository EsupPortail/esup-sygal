<?php

namespace Application\Renderer\Template\Variable\PluginManager;

use Acteur\Entity\Db\AbstractActeur;
use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Renderer\ActeurTemplateVariable;
use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\Etudiant as AdmissionEtudiant;
use Admission\Entity\Db\Financement as AdmissionFinancement;
use Admission\Entity\Db\Inscription as AdmissionInscription;
use Admission\Renderer\AdmissionEtudiantTemplateVariable;
use Admission\Renderer\AdmissionFinancementTemplateVariable;
use Admission\Renderer\AdmissionInscriptionTemplateVariable;
use Admission\Renderer\AdmissionOperationTemplateVariable;
use Admission\Renderer\AdmissionTemplateVariable;
use Application\Entity\Db\Role;
use Application\Renderer\RoleTemplateVariable;
use Application\Renderer\ValidationTemplateVariable;
use Candidat\Entity\Db\Candidat;
use Candidat\Renderer\CandidatTemplateVariable;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Renderer\DoctorantTemplateVariable;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Renderer\FormationInscriptionTemplateVariable;
use Formation\Renderer\FormationSessionTemplateVariable;
use Formation\Renderer\FormationTemplateVariable;
use HDR\Entity\Db\HDR;
use HDR\Renderer\HDRTemplateVariable;
use HDR\Renderer\ValidationHDRTemplateVariable;
use Individu\Entity\Db\Individu;
use Individu\Renderer\IndividuTemplateVariable;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Renderer\SoutenanceMembreTemplateVariable;
use Soutenance\Renderer\SoutenancePropositionTemplateVariable;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Renderer\StructureTemplateVariable;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Renderer\TheseTemplateVariable;
use These\Renderer\ValidationTheseTemplateVariable;
use Validation\Entity\Db\ValidationHDR;
use Validation\Entity\Db\ValidationThese;

trait TemplateVariablePluginManagerAwareTrait
{
    protected TemplateVariablePluginManager $templateVariablePluginManager;

    public function setTemplateVariablePluginManager(TemplateVariablePluginManager $templateVariablePluginManager): void
    {
        $this->templateVariablePluginManager = $templateVariablePluginManager;
    }


    ///////////////////////////////////////// Individu ////////////////////////////////////////////////

    protected function getIndividuTemplateVariable(Individu $individu): IndividuTemplateVariable
    {
        /** @var \Individu\Renderer\IndividuTemplateVariable $individuTemplateVariable */
        $individuTemplateVariable = $this->templateVariablePluginManager->get('individu');
        $individuTemplateVariable->setIndividu($individu);
        return $individuTemplateVariable;
    }


    ///////////////////////////////////////// Role ////////////////////////////////////////////////

    protected function getRoleTemplateVariable(Role $role): RoleTemplateVariable
    {
        /** @var \Application\Renderer\RoleTemplateVariable $roleTemplateVariable */
        $roleTemplateVariable = $this->templateVariablePluginManager->get('role');
        $roleTemplateVariable->setRole($role);
        return $roleTemplateVariable;
    }


    ///////////////////////////////////////// These ////////////////////////////////////////////////

    protected function getTheseTemplateVariable(These $these): TheseTemplateVariable
    {
        /** @var \These\Renderer\TheseTemplateVariable $theseTemplateVariable */
        $theseTemplateVariable = $this->templateVariablePluginManager->get('these');
        $theseTemplateVariable->setThese($these);
        return $theseTemplateVariable;
    }

    protected function getDoctorantTemplateVariable(Doctorant $doctorant): DoctorantTemplateVariable
    {
        /** @var \Doctorant\Renderer\DoctorantTemplateVariable $doctorantTemplateVariable */
        $doctorantTemplateVariable = $this->templateVariablePluginManager->get('doctorant');
        $doctorantTemplateVariable->setDoctorant($doctorant);
        return $doctorantTemplateVariable;
    }

    protected function getActeurTemplateVariable(AbstractActeur $acteur): ActeurTemplateVariable
    {
        /** @var \Acteur\Renderer\ActeurTemplateVariable $acteurTemplateVariable */
        $acteurTemplateVariable = $this->templateVariablePluginManager->get('acteur');
        $acteurTemplateVariable->setActeur($acteur);
        return $acteurTemplateVariable;
    }

    protected function getValidationTheseTemplateVariable(ValidationThese $validationThese): ValidationTheseTemplateVariable
    {
        /** @var \These\Renderer\ValidationTheseTemplateVariable $validationTemplateVariable */
        $validationTemplateVariable = $this->templateVariablePluginManager->get('validationThese');
        $validationTemplateVariable->setValidationThese($validationThese);
        return $validationTemplateVariable;
    }


    ///////////////////////////////////////// HDR ////////////////////////////////////////////////

    protected function getHDRTemplateVariable(HDR $hdr): HDRTemplateVariable
    {
        /** @var \HDR\Renderer\HDRTemplateVariable $hdrTemplateVariable */
        $hdrTemplateVariable = $this->templateVariablePluginManager->get('hdr');
        $hdrTemplateVariable->setHDR($hdr);
        return $hdrTemplateVariable;
    }

    protected function getCandidatTemplateVariable(Candidat $candidat): CandidatTemplateVariable
    {
        /** @var CandidatTemplateVariable $candidatTemplateVariable */
        $candidatTemplateVariable = $this->templateVariablePluginManager->get('candidat');
        $candidatTemplateVariable->setCandidat($candidat);
        return $candidatTemplateVariable;
    }

    protected function getActeurHDRTemplateVariable(ActeurHDR $acteur): ActeurHDRTemplateVariable
    {
        /** @var \HDR\Renderer\ActeurHDRTemplateVariable $acteurTemplateVariable */
        $acteurTemplateVariable = $this->templateVariablePluginManager->get('acteurHDR');
        $acteurTemplateVariable->setActeurHDR($acteur);
        return $acteurTemplateVariable;
    }

    protected function getValidationHDRTemplateVariable(ValidationHDR $validationHDR): ValidationHDRTemplateVariable
    {
        /** @var \HDR\Renderer\ValidationHDRTemplateVariable $validationTemplateVariable */
        $validationTemplateVariable = $this->templateVariablePluginManager->get('validationHDR');
        $validationTemplateVariable->setValidationHDR($validationHDR);
        return $validationTemplateVariable;
    }


    ///////////////////////////////////////// Structure ////////////////////////////////////////////////

    protected function getStructureTemplateVariable(StructureConcreteInterface $structureConcrete): StructureTemplateVariable
    {
        /** @var \Structure\Renderer\StructureTemplateVariable $etablissementTemplateVariable */
        $etablissementTemplateVariable = $this->templateVariablePluginManager->get('structure'); // NB : pattern singleton désactivé (shared = false)
        $etablissementTemplateVariable->setStructureConcrete($structureConcrete);
        return $etablissementTemplateVariable;
    }


    ///////////////////////////////////////// Soutenance ////////////////////////////////////////////////

    protected function getSoutenancePropositionTemplateVariable(Proposition $proposition): SoutenancePropositionTemplateVariable
    {
        /** @var \Soutenance\Renderer\SoutenancePropositionTemplateVariable $soutenancePropositionTemplateVariable */
        $soutenancePropositionTemplateVariable = $this->templateVariablePluginManager->get('soutenanceProposition');
        $soutenancePropositionTemplateVariable->setProposition($proposition);
        return $soutenancePropositionTemplateVariable;
    }

    protected function getSoutenanceMembreTemplateVariable(Membre $membre): SoutenanceMembreTemplateVariable
    {
        /** @var \Soutenance\Renderer\SoutenanceMembreTemplateVariable $soutenanceMembreTemplateVariable */
        $soutenanceMembreTemplateVariable = $this->templateVariablePluginManager->get('soutenanceMembre');
        $soutenanceMembreTemplateVariable->setMembre($membre);
        return $soutenanceMembreTemplateVariable;
    }


    ///////////////////////////////////////// Validation ////////////////////////////////////////////////

    protected function getValidationTemplateVariable(ValidationThese|ValidationHDR $validation): ValidationTemplateVariable
    {
        /** @var \Application\Renderer\ValidationTemplateVariable $validationTemplateVariable */
        $validationTemplateVariable = $this->templateVariablePluginManager->get('validation');
        $validationTemplateVariable->setValidation($validation);
        return $validationTemplateVariable;
    }


    ///////////////////////////////////////// Admission ////////////////////////////////////////////////

    protected function getAdmissionAdmissionTemplateVariable(Admission $admission): AdmissionTemplateVariable
    {
        /** @var AdmissionTemplateVariable $admissionAdmissionTemplateVariable */
        $admissionAdmissionTemplateVariable = $this->templateVariablePluginManager->get('admission');
        $admissionAdmissionTemplateVariable->setAdmission($admission);
        return $admissionAdmissionTemplateVariable;
    }

    protected function getAdmissionEtudiantTemplateVariable(AdmissionEtudiant $etudiant): AdmissionEtudiantTemplateVariable
    {
        /** @var AdmissionEtudiantTemplateVariable $admissionEtudiantTemplateVariable */
        $admissionEtudiantTemplateVariable = $this->templateVariablePluginManager->get('admissionEtudiant');
        $admissionEtudiantTemplateVariable->setEtudiant($etudiant);
        return $admissionEtudiantTemplateVariable;
    }

    protected function getAdmissionInscriptionTemplateVariable(AdmissionInscription $inscription): AdmissionInscriptionTemplateVariable
    {
        /** @var AdmissionInscriptionTemplateVariable $admissionInscriptionTemplateVariable */
        $admissionInscriptionTemplateVariable = $this->templateVariablePluginManager->get('admissionInscription');
        $admissionInscriptionTemplateVariable->setInscription($inscription);
        return $admissionInscriptionTemplateVariable;
    }

    protected function getAdmissionFinancementTemplateVariable(AdmissionFinancement $financement): AdmissionFinancementTemplateVariable
    {
        /** @var AdmissionFinancementTemplateVariable $admissionFinancementTemplateVariable */
        $admissionFinancementTemplateVariable = $this->templateVariablePluginManager->get('admissionFinancement');
        $admissionFinancementTemplateVariable->setFinancement($financement);
        return $admissionFinancementTemplateVariable;
    }

    private function getAdmissionOperationTemplateVariable(AdmissionOperationInterface $operation): AdmissionOperationTemplateVariable
    {
        /** @var AdmissionOperationTemplateVariable $admissionOperationTemplateVariable */
        $admissionOperationTemplateVariable = $this->templateVariablePluginManager->get('admissionOperation');
        $admissionOperationTemplateVariable->setOperation($operation);

        return $admissionOperationTemplateVariable;
    }


    ///////////////////////////////////////// Formation ////////////////////////////////////////////////


    protected function getFormationTemplateVariable(Formation $formation): FormationTemplateVariable
    {
        /** @var \Formation\Renderer\FormationTemplateVariable $formationTemplateVariable */
        $formationTemplateVariable = $this->templateVariablePluginManager->get('formation');
        $formationTemplateVariable->setFormation($formation);
        return $formationTemplateVariable;
    }

    protected function getFormationSessionTemplateVariable(Session $session): FormationSessionTemplateVariable
    {
        /** @var \Formation\Renderer\FormationSessionTemplateVariable $formationSessionTemplateVariable */
        $formationSessionTemplateVariable = $this->templateVariablePluginManager->get('formationSession');
        $formationSessionTemplateVariable->setSession($session);
        return $formationSessionTemplateVariable;
    }

    protected function getFormationInscriptionTemplateVariable(Inscription $inscription): FormationInscriptionTemplateVariable
    {
        /** @var \Formation\Renderer\FormationInscriptionTemplateVariable $formationInscriptionTemplateVariable */
        $formationInscriptionTemplateVariable = $this->templateVariablePluginManager->get('formationInscription');
        $formationInscriptionTemplateVariable->setInscription($inscription);
        return $formationInscriptionTemplateVariable;
    }
}