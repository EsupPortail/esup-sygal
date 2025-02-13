<?php

namespace Validation\View\Helper;

use Validation\Entity\Db\ValidationHDR;
use Webmozart\Assert\Assert;

class ValidationHDRViewHelper extends ValidationViewHelper
{
    protected ValidationHDR $validationHDR;

    protected function setValidation($validation): void
    {
        Assert::isInstanceOf($validation, ValidationHDR::class);
        $this->validationHDR = $validation;

        parent::setValidation($validation->getValidation());
    }

    protected function renderAuteur(): string
    {
        if ($individu = $this->validationHDR->getIndividu()) {
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