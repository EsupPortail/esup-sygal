<?php /** @noinspection PhpUnusedAliasInspection */


namespace Formation\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Resolver\TemplatePathStack;

class InscriptionViewHelper extends AbstractHelper
{
    /**
     * @param Inscription $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke(Inscription $object, array $options = [])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('inscription', ['inscription' => $object, 'options' => $options]);
    }
}