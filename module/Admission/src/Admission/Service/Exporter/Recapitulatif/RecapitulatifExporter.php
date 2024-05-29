<?php

namespace Admission\Service\Exporter\Recapitulatif;

use Admission\Entity\AdmissionRecapitulatifDataTemplate;
use Admission\Entity\Db\Admission;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Admission\Provider\Template\PdfTemplates;
use Admission\Service\Url\UrlServiceAwareTrait;
use Soutenance\Service\Notification\StringElement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class RecapitulatifExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RenduServiceAwareTrait;
    use StructureServiceAwareTrait;
    use UrlServiceAwareTrait;
    use AdmissionServiceAwareTrait;
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

        /** @var Admission $admission */
        $admission = $this->vars['admission'];
        $individu = $admission->getIndividu();

        $operations = $this->vars['operations'];
        $admissionRecapitulatif = new AdmissionRecapitulatifDataTemplate();
        $admissionRecapitulatif->setAdmission($admission);

        $libelleSignature = new StringElement();
        $libelleSignature->texte = $this->admissionService->generateLibelleSignaturePresidenceForAdmission($admission);

        $admissionRecapitulatif->setOperations($operations);
        $vars = [
            'admission' => $admission,
            'admissionEtudiant' => $admission->getEtudiant()->first(),
            'admissionInscription' => $admission->getInscription()->first(),
            'admissionFinancement' => $admission->getFinancement()->first(),
            'stringelement' => $libelleSignature,
            'individu' => $individu,
            'admissionRecapitulatif' => $admissionRecapitulatif,
        ];

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
}