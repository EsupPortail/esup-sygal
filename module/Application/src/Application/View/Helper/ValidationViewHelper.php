<?php

namespace Application\View\Helper;

use Application\Constants;
use Application\Entity\Db\Validation;

class ValidationViewHelper extends AbstractHelper
{
    /**
     *
     *
     * @param Validation $validation
     * @return string Code HTML
     */
    public function render(Validation $validation = null)
    {
        $validation = $validation ?: $this->value;

        if (!$validation) {
            return '';
        }

        if ($individu = $validation->getIndividu()) {
            $auteur = (string) $validation->getIndividu();
            if ($email = $validation->getIndividu()->getEmail()) {
                $auteur .= sprintf(' (<a href="mailto: %s">%s</a>)', $email, $email);
            }

        } else {
            $auteur = $validation->getHistoModificateur()->getDisplayName();
            if ($email = $validation->getHistoModificateur()->getEmail()) {
                $auteur .= sprintf(' (<a href="mailto: %s">%s</a>)', $email, $email);
            }
        }

        $vars = [
            "Type de validation" => $validation->getTypeValidation(),
            "Date et auteur"     => $validation->getHistoModification()->format(Constants::DATETIME_FORMAT)
                . ' par ' . $auteur,
        ];

        $html = "<dl class=\"validation dl-horizontal\">\n";
        foreach ($vars as $key => $value) {
            $html .= "\t<dt>$key :</dt><dd>$value</dd>\n";
        }
        $html .= "</dl>";

        return $html;
    }
}