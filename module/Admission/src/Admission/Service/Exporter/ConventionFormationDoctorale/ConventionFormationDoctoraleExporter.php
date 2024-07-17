<?php

namespace Admission\Service\Exporter\ConventionFormationDoctorale;

use Admission\Entity\ConventionFormationDoctoraleDataTemplate;
use Admission\Entity\Db\Admission;
use Admission\Entity\Db\ConventionFormationDoctorale;
use Admission\Entity\Db\Inscription;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Admission\Provider\Template\PdfTemplates;
use Admission\Service\Url\UrlServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class ConventionFormationDoctoraleExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RenduServiceAwareTrait;
    use StructureServiceAwareTrait;
    use UrlServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use RoleServiceAwareTrait;
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
        /** @var ConventionFormationDoctorale $conventionFormationDoctorale */
        $conventionFormationDoctorale = $this->vars['conventionFormationDoctorale'];
        $conventionFormationDoctoraleDataTemplate = new ConventionFormationDoctoraleDataTemplate();

        /** @var Admission $admission */
        $admission = $this->vars['admission'];

        $conventionFormationDoctoraleDataTemplate->setAdmission($admission);

        $operations = $this->vars['conventionFormationDoctoraleOperations'];
        $conventionFormationDoctoraleDataTemplate->setOperations($operations);

        $individu = $admission->getIndividu();
        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first();
        $uniteRechercheDirecteur = $inscription?->getUniteRecherche();
        $uniteRechercheCoDirecteur = $inscription?->getUniteRechercheCoDirecteur();

        $individuResponsablesUniteRechercheDirecteur = $uniteRechercheDirecteur ? $this->roleService->findIndividuRoleByStructure($uniteRechercheDirecteur->getStructure(), Role::CODE_RESP_UR) : null;
        if(is_array($individuResponsablesUniteRechercheDirecteur)){
            $conventionFormationDoctoraleDataTemplate->setIndividuResponsablesUniteRechercheDirecteur($individuResponsablesUniteRechercheDirecteur);
        }
        $individuResponsablesUniteRechercheCoDirecteur = $uniteRechercheCoDirecteur ? $this->roleService->findIndividuRoleByStructure($uniteRechercheCoDirecteur->getStructure(), Role::CODE_RESP_UR) : null;
        if(is_array($individuResponsablesUniteRechercheCoDirecteur)){
            $conventionFormationDoctoraleDataTemplate->setIndividuResponsablesUniteRechercheCoDirecteur($individuResponsablesUniteRechercheCoDirecteur);
        }

        $vars = [
            'admission' => $admission,
            'admissionEtudiant' => $admission->getEtudiant()->first(),
            'admissionInscription' => $admission->getInscription()->first(),
            'admissionFinancement' => $admission->getFinancement()->first(),
            'individu' => $individu,
            'conventionFormationDoctorale' => $conventionFormationDoctorale,
            'admissionConventionFormationDoctoraleData' => $conventionFormationDoctoraleDataTemplate
        ];
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
}