<?php

namespace Admission\Service\Exporter\Recapitulatif;

use Admission\Entity\Db\Admission;
use Admission\Provider\Template\PdfTemplates;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Url\UrlServiceAwareTrait;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Soutenance\Service\Notification\StringElement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class RecapitulatifExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RenduServiceAwareTrait;
    use StructureServiceAwareTrait;
    use UrlServiceAwareTrait;
    use AdmissionServiceAwareTrait;
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

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::ADMISSION_RECAPITULATIF, $vars);

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
        $individu = $admission->getIndividu();
        $operations = $this->vars['operations'];

        $libelleSignature = new StringElement();
        $libelleSignature->texte = $this->admissionService->generateLibelleSignaturePresidenceForAdmission($admission);

        $admissionAdmissionTemplateVariable = $this->getAdmissionAdmissionTemplateVariable($admission);
        $admissionAdmissionTemplateVariable->setOperations($operations);
        $admissionEtudiantTemplateVariable = $this->getAdmissionEtudiantTemplateVariable($admission->getEtudiant()->first());
        $admissionInscriptionTemplateVariable = $this->getAdmissionInscriptionTemplateVariable($admission->getInscription()->first());
        $admissionFinancementTemplateVariable = $this->getAdmissionFinancementTemplateVariable($admission->getFinancement()->first());
        $individuTemplateVariable = $this->getIndividuTemplateVariable($individu);

        return [
            'admission' => $admissionAdmissionTemplateVariable,
            'admissionEtudiant' => $admissionEtudiantTemplateVariable,
            'admissionInscription' => $admissionInscriptionTemplateVariable,
            'admissionFinancement' => $admissionFinancementTemplateVariable,
            'stringelement' => $libelleSignature,
            'individu' => $individuTemplateVariable,
//            'admissionRecapitulatif' => $admissionRecapitulatif, // cf. $admissionAdmissionTemplateVariable
        ];
    }
}