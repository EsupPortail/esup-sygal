<?php

namespace Depot\Service\ConventionDeMiseEnLigne\Exporter;

use Depot\Entity\Db\Diffusion;
use Depot\Provider\Template\PdfTemplates;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Url\UrlServiceAwareTrait as UrlFichierServiceAwareTrait;
use InvalidArgumentException;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use UnicaenRenderer\Entity\Db\Template;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class ConventionDeMiseEnLigneExporter extends PdfExporter
{
    use EtablissementServiceAwareTrait;
    use UrlFichierServiceAwareTrait;
    use RenduServiceAwareTrait;

    private These $these;
    private VersionFichier $versionFichier;
    private Diffusion $diffusion;
    private string $engine = Template::TEMPLATE_ENGINE_LAMINAS;
//    private string $engine = Template::TEMPLATE_ENGINE_TWIG;

    public function __construct(PhpRenderer $renderer = null, $format = 'A4', $orientationPaysage = false, $defaultFontSize = 10)
    {
        parent::__construct($renderer, $format, $orientationPaysage, $defaultFontSize);

        $resolver = $renderer->resolver();
        $resolver->attach(new TemplatePathStack(['script_paths' => [__DIR__]]));

        $this->setHeaderScriptToNone();
//        $this->setHeaderScript('header-odd.phtml', 'O');  // pages paires
//        $this->setHeaderScript('header-even.phtml', 'E'); // pages impaires
        $this->setMarginTop(20);
        $this->setMarginBottom(25);
        $this->setFooterTitle("Convention de mise en ligne");
    }

    public function setThese(These $these): void
    {
        $this->these = $these;
    }

    public function setVersionFichier(VersionFichier $versionFichier): void
    {
        $this->versionFichier = $versionFichier;
    }

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null): void
    {
        $this->diffusion = $this->these->getDiffusionForVersion($this->versionFichier);

        $corpsHtml = $this->renderCorps();
        $this->addBodyHtml($corpsHtml);
        $this->addBodyHtml($corpsHtml, true, 1); // génération de 2 exemplaires de la convention
        $this->setFooterScript('footer.phtml', null, ['etablissement' => $this->etablissementService->fetchEtablissementComue()]);

        PdfExporter::export($filename, $destination, $memoryLimit);
    }

    private function renderCorps(): string
    {
        $this->urlFichierService->setParams(['these' => $this->these]);

        $vars = [
            'these' => $this->these,
            'diffusion' => $this->diffusion,
            'UrlFichier' => $this->urlFichierService,
        ];

        $templateCode = match ($this->engine) {
            Template::TEMPLATE_ENGINE_LAMINAS => PdfTemplates::DEPOT_CONVENTION_DE_MISE_EN_LIGNE__LAMINAS,
            Template::TEMPLATE_ENGINE_TWIG => PdfTemplates::DEPOT_CONVENTION_DE_MISE_EN_LIGNE__TWIG,
            default => throw new InvalidArgumentException("ENgine inattendu"),
        };
        $rendu = $this->contenuService->generateRenduByTemplateCode($templateCode, $vars);

        return $rendu->getCorps();

    }
}