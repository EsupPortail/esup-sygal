<?php

namespace Validation\View\Helper;

use Validation\Entity\Db\ValidationThese;
use Webmozart\Assert\Assert;

class ValidationTheseViewHelper extends ValidationViewHelper
{
    protected ValidationThese $validationThese;

    protected function setValidation($validation): void
    {
        Assert::isInstanceOf($validation, ValidationThese::class);
        $this->validationThese = $validation;

        parent::setValidation($validation->getValidation());
    }

    protected function renderAuteur(): string
    {
        if ($individu = $this->validationThese->getIndividu()) {
            $auteur = (string) $individu;
            if ($email = $individu->getEmailPro()) {
                $auteur .= sprintf(' (<a href="mailto: %s">%s</a>)', $email, $email);
            }
        } else {
            $auteur = parent::renderAuteur();
        }

        return $auteur;
    }
}