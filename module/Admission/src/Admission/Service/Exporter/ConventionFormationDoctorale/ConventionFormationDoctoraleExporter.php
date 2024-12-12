<?php

namespace Admission\Service\Exporter\ConventionFormationDoctorale;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Inscription;
use Admission\Provider\Template\PdfTemplates;
use Admission\Renderer\AdmissionConventionFormationDoctoraleTemplateVariable;
use Admission\Renderer\AdmissionEtudiantTemplateVariable;
use Admission\Renderer\AdmissionFinancementTemplateVariable;
use Admission\Renderer\AdmissionInscriptionTemplateVariable;
use Admission\Renderer\AdmissionTemplateVariable;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Url\UrlServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Individu\Renderer\IndividuTemplateVariable;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class ConventionFormationDoctoraleExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RenduServiceAwareTrait;
    use StructureServiceAwareTrait;
    use UrlServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    private $vars;

    public function setVars(array $vars)
    {
        $this->vars = $vars;
        $this->vars['exporter'] = $this;

        return $this;
    }

    public function __construct(PhpRenderer $renderer = null, $format = 'A4', $orientationPaysage = false, $defaultFontSize = 10)
    {
        parent::__construct($renderer, $format, $orientationPaysage, $defaultFontSize);
        $resolver = $renderer->resolver();
        $resolver->attach(new TemplatePathStack(['script_paths' => [__DIR__]]));
    }

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $vars = $this->createTemplateVariables();

        $logos = [
            "COMUE" => $this->vars['logos']["comue"],
            "ETAB" => $this->vars['logos']["site"],
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::ADMISSION_CONVENTION_FORMATION_DOCTORALE, $vars);

        $this->getMpdf()->SetMargins(0,0,60);
        $this->setHeaderScript('header.phtml', null, $logos);
        $this->setFooterScript('footer.phtml');
        $this->addBodyHtml($rendu->getCorps());

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }

    private function createTemplateVariables(): array
    {
        /** @var Admission $admission */
        $admission = $this->vars['admission'];
        $operations = $this->vars['conventionFormationDoctoraleOperations'];
        $individu = $admission->getIndividu();

        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        $uniteRechercheDirecteur = $inscription?->getUniteRecherche();
        $uniteRechercheCoDirecteur = $inscription?->getUniteRechercheCoDirecteur();

        $individuResponsablesUniteRechercheDirecteur = $uniteRechercheDirecteur ? $this->applicationRoleService->findIndividuRoleByStructure($uniteRechercheDirecteur->getStructure(), Role::CODE_RESP_UR) : null;
        $individuResponsablesUniteRechercheCoDirecteur = $uniteRechercheCoDirecteur ? $this->applicationRoleService->findIndividuRoleByStructure($uniteRechercheCoDirecteur->getStructure(), Role::CODE_RESP_UR) : null;

        /** @var AdmissionTemplateVariable $admissionAdmissionTemplateVariable */
        $admissionAdmissionTemplateVariable = $this->templateVariablePluginManager->get('admission');
        $admissionAdmissionTemplateVariable->setAdmission($admission);

        /** @var AdmissionEtudiantTemplateVariable $admissionEtudiantTemplateVariable */
        $admissionEtudiantTemplateVariable = $this->templateVariablePluginManager->get('admissionEtudiant');
        $admissionEtudiantTemplateVariable->setEtudiant($admission->getEtudiant()->first());

        /** @var AdmissionInscriptionTemplateVariable $admissionInscriptionTemplateVariable */
        $admissionInscriptionTemplateVariable = $this->templateVariablePluginManager->get('admissionInscription');
        $admissionInscriptionTemplateVariable->setInscription($admission->getInscription()->first());

        /** @var AdmissionFinancementTemplateVariable $admissionFinancementTemplateVariable */
        $admissionFinancementTemplateVariable = $this->templateVariablePluginManager->get('admissionFinancement');
        $admissionFinancementTemplateVariable->setFinancement($admission->getFinancement()->first());

        /** @var AdmissionConventionFormationDoctoraleTemplateVariable $admissionConventionFormationDoctoraleTemplateVariable */
        $admissionConventionFormationDoctoraleTemplateVariable = $this->templateVariablePluginManager->get('admissionConventionFormationDoctorale');
        $admissionConventionFormationDoctoraleTemplateVariable->setAdmission($admission);
        $admissionConventionFormationDoctoraleTemplateVariable->setOperations($operations);
        $admissionConventionFormationDoctoraleTemplateVariable->setConventionFormationDoctorale($admission->getConventionFormationDoctorale()->first());
        if (is_array($individuResponsablesUniteRechercheDirecteur)) {
            $admissionConventionFormationDoctoraleTemplateVariable->setIndividuResponsablesUniteRechercheDirecteur($individuResponsablesUniteRechercheDirecteur);
        }
        if (is_array($individuResponsablesUniteRechercheCoDirecteur)) {
            $admissionConventionFormationDoctoraleTemplateVariable->setIndividuResponsablesUniteRechercheCoDirecteur($individuResponsablesUniteRechercheCoDirecteur);
        }

        /** @var IndividuTemplateVariable $individuTemplateVariable */
        $individuTemplateVariable = $this->templateVariablePluginManager->get('individu');
        $individuTemplateVariable->setIndividu($individu);

        return [
            'admission' => $admissionAdmissionTemplateVariable,
            'admissionEtudiant' => $admissionEtudiantTemplateVariable,
            'admissionInscription' => $admissionInscriptionTemplateVariable,
            'admissionFinancement' => $admissionFinancementTemplateVariable,
            'individu' => $individuTemplateVariable,
//            'conventionFormationDoctorale' => $admissionConventionFormationDoctoraleTemplateVariable, // remplacée par 'admissionConventionFormationDoctorale'
            'admissionConventionFormationDoctorale' => $admissionConventionFormationDoctoraleTemplateVariable
        ];
    }
}