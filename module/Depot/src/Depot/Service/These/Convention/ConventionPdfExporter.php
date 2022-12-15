<?php

namespace Depot\Service\These\Convention;

use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplatePathStack;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Exporter\Pdf as PdfExporter;

class ConventionPdfExporter extends PdfExporter
{
    /**
     * @var array
     */
    private $vars;

    /**
     * ConventionPdfExporter constructor.
     *
     * @param PhpRenderer|null $renderer
     * @param string           $format
     * @param bool             $orientationPaysage
     * @param int              $defaultFontSize
     */
    public function __construct(PhpRenderer $renderer = null, $format = 'A4', $orientationPaysage = false, $defaultFontSize = 10)
    {
        parent::__construct($renderer, $format, $orientationPaysage, $defaultFontSize);

        /** @var AggregateResolver $resolver */
        $resolver = $renderer->resolver();
        $resolver->attach(new TemplatePathStack(['script_paths' => [__DIR__]]));

        $this->setLogo(file_get_contents(APPLICATION_DIR . '/public/logo_normandie_univ.jpg')); // 'var:logo' dans les phtml
        $this->setHeaderScript('partial/header-odd.phtml', 'O');  // pages paires
        $this->setHeaderScript('partial/header-even.phtml', 'E'); // pages impaires
        $this->setFooterScript('partial/footer.phtml');
        $this->setMarginTop(20);
        $this->setMarginBottom(25);
        $this->setFooterTitle("Convention de mise en ligne");
    }

    /**
     * @param array $vars
     * @return $this
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
        $this->vars['exporter'] = $this;

        return $this;
    }

    /**
     * @param string $filename
     * @param string $destination
     * @param string $memoryLimit
     * @return string
     */
    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        if (empty($this->vars)) {
            throw new RuntimeException("Il faut fournir les variables avant de pouvoir exporter!");
        }

        $this->addBodyHtml('<style>' . file_get_contents(APPLICATION_DIR . '/public/css/app.css') . '</style>');

        $this->addBodyScript('convention.phtml', false, $this->vars);
        $this->addBodyScript('convention.phtml', true, $this->vars, 1);

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}