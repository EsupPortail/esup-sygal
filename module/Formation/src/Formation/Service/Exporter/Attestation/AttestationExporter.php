<?php

namespace Formation\Service\Exporter\Attestation;

use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Formation\Entity\Db\Inscription;
use Formation\Provider\Template\PdfTemplates;
use Formation\Service\Url\UrlServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class AttestationExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RenduServiceAwareTrait;
    use StructureServiceAwareTrait;
    use UrlServiceAwareTrait;

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

        /** @var Inscription $inscription */
        $inscription = $this->vars['inscription'];
        $session = $inscription->getSession();
        $doctorant = $inscription->getDoctorant();

        $urlService = $this->urlService;
        $urlService->setVariables(['etablissement' => $doctorant->getEtablissement()]);

        $vars = [
            'doctorant' => $doctorant,
            'session' => $session,
            'formation' => $session->getFormation(),
            'inscription' => $inscription,
            'Url' => $urlService,
        ];

        $comue = $this->etablissementService->fetchEtablissementComue();
        $ced = $this->etablissementService->fetchEtablissementCed();
        $etab = $session->getSite();
        $logos = [
            "COMUE" => $comue?$this->fichierStorageService->getFileForLogoStructure($comue->getStructure()):null,
            "CED" =>  $ced?$this->fichierStorageService->getFileForLogoStructure($ced->getStructure()):null,
            "ETAB" => $etab?$this->fichierStorageService->getFileForLogoStructure($etab->getStructure()):null,
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::FORMATION_ATTESTATION, $vars);
        $this->getMpdf()->SetMargins(0,0,60);
        $this->setHeaderScript('header.phtml', null, $logos);
        $this->setFooterScript('footer.phtml');
        $this->addBodyHtml($rendu->getCorps());
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}