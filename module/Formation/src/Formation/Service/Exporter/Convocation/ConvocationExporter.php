<?php

namespace Formation\Service\Exporter\Convocation;

use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Formation\Entity\Db\Inscription;
use Formation\Provider\Template\PdfTemplates;
use Formation\Service\Url\UrlServiceAwareTrait;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class ConvocationExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use RenduServiceAwareTrait;
    use StructureServiceAwareTrait;
    use UrlServiceAwareTrait;
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
        /** @var Inscription $inscription */
        $inscription = $this->vars['inscription'];
        $session = $inscription->getSession();

        $vars = $this->createTemplateVars($inscription);

        $comue = $this->etablissementService->fetchEtablissementComue();
        $ced = $this->etablissementService->fetchEtablissementCed();
        $etab = $session->getSite();
        $logos = [
            "COMUE" => $comue?$this->fichierStorageService->getFileForLogoStructure($comue->getStructure()):null,
            "CED" =>  $ced?$this->fichierStorageService->getFileForLogoStructure($ced->getStructure()):null,
            "ETAB" => $etab?$this->fichierStorageService->getFileForLogoStructure($etab->getStructure()):null,
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::FORMATION_CONVOCATION, $vars);
        $this->getMpdf()->SetMargins(0,0,60);
        $this->setHeaderScript('header.phtml', null, $logos);
        $this->setFooterScript('footer.phtml');
        $this->addBodyHtml($rendu->getCorps());

        // Modification d'une option de config PCRE pour éviter l'erreur
        // "The HTML code size is larger than pcre.backtrack_limit 1000000.
        // You should use WriteHTML() with smaller string lengths. Pass your HTML in smaller chunks."
        // (cf. https://mpdf.github.io/troubleshooting/known-issues.html#blank-pages-or-some-sections-missing)
        // TODO : trouver pourquoi l'erreur est rencontrée lors de cet export et pas les autres.
        ini_set("pcre.backtrack_limit", "5000000");

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }

    private function createTemplateVars(Inscription $inscription): array
    {
        $session = $inscription->getSession();
        $doctorant = $inscription->getDoctorant();

        $this->urlService->setVariables([
            'etablissement' => $doctorant->getEtablissement()
        ]);

        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($doctorant);
        $formationTemplateVariable = $this->getFormationTemplateVariable($session->getFormation());
        $formationSessionTemplateVariable = $this->getFormationSessionTemplateVariable($session);
        $formationInscriptionTemplateVariable = $this->getFormationInscriptionTemplateVariable($inscription);

        return [
            'doctorant' => $doctorantTemplateVariable,
            'session' => $formationSessionTemplateVariable,
            'formation' => $formationTemplateVariable,
            'inscription' => $formationInscriptionTemplateVariable,
            'Url' => $this->urlService,
        ];
    }
}