<?php

namespace Validation\View\Helper;

use Application\Constants;
use Application\View\Renderer\PhpRenderer;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;
use Validation\Entity\Db\Validation;
use Webmozart\Assert\Assert;

class ValidationViewHelper extends AbstractHelper
{
    protected bool $includeMailToLink = false;
    protected ?Validation $validation;

    public function __invoke($validation = null, string $color = null, string $icon = null, bool $includeMailToLink = false): string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $this->view->partial('validation.phtml', [
            'validation' => $validation,
            'includeMailToLink' => $includeMailToLink,
            'color' => $color,
            'icon' => $color === "success" ? "fa fa-check-circle" : "fa fa-times-circle",
        ]);
    }
//    public function render($validation = null, string $color = null, string $icon = null): string
//    {
//        $validation = $validation ?: $this->value;
//        if (!$validation) {
//            return '';
//        }
//
//        $this->setValidation($validation);
//
//        $vars = $this->createVars();
//        $html = "<dl class=\"validation row\">\n";
//        foreach ($vars as $key => $value) {
//            $html .= "\t<dt class=\"col-md-4\">$key :</dt><dd class=\"col-md-6\">$value</dd>\n";
//        }
//        $html .= "</dl>";
//        return $html;
//    }

    protected function setValidation($validation): void
    {
        Assert::isInstanceOf($validation, Validation::class);
        $this->validation = $validation;
    }

    protected function createVars(): array
    {
        $auteur = $this->renderAuteur();

        return [
            "Type de validation" => $this->validation->getTypeValidation(),
            "Date et auteur" => $this->validation->getHistoModification()->format(Constants::DATETIME_FORMAT)
                . ' par ' . $auteur,
        ];
    }

    protected function renderAuteur(): string
    {
        $auteur = $this->validation->getHistoModificateur()->getDisplayName();

        if ($this->includeMailToLink && ($email = $this->validation->getHistoModificateur()->getEmail())) {
            $auteur .= sprintf(' (<a href="mailto: %s">%s</a>)', $email, $email);
        }

        return $auteur;
    }
}