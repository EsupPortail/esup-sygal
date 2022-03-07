<?php

namespace Application\View\Helper\Actualite;

use Application\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

class ActualiteViewHelper extends AbstractHelper
{
    protected $enabled = false;
    protected $url = '';

    public function setEnabled(bool $enabled = true): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function __invoke($value = null): self
    {
        $this->view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $this;
    }

    public function render(): string
    {
        return $this->view->partial('actualites.phtml', [
            'enabled' => $this->enabled,
            'url' => $this->url,
        ]);
    }
}