<?php

namespace Application\Form\Validator;

use Zend\Validator\AbstractValidator;

class PasswordValidator extends AbstractValidator {

    const MIN_LENGTH = 8;

    const NOT_YET       = 'not-yet';
    const NO_UPPER      = 'no-upper';
    const NO_LOWER      = 'no-lower';
    const NO_SPECHAR    = 'no-spe';
    const NO_NUMBER     = 'no-number';
    const TOO_SHORT     = 'too_short';

    protected $messageTemplates = [
        self::NOT_YET       => "La méthode est pas implémentée",
        self::NO_UPPER      => "Aucun caractère en majuscule",
        self::NO_LOWER      => "Aucun caractère en minuscule",
        self::NO_SPECHAR    => "Aucun caractère spécial",
        self::NO_NUMBER     => "Aucun caractère numérique",
        self::TOO_SHORT     => "Mot de passe trop court (8 caractères minimum)",
    ];

    public function isValid($value)
    {
        $nb_pb = 0;
        if (strlen($value) < self::MIN_LENGTH) {
            $this->error(self::TOO_SHORT);
            $nb_pb++;
        }
        if(1 !== preg_match('~[0-9]~', $value)){
            $this->error(self::NO_NUMBER);
            $nb_pb++;
        }
        if(1 !== preg_match('~[a-z]~', $value)){
            $this->error(self::NO_LOWER);
            $nb_pb++;
        }
        if(1 !== preg_match('~[A-Z]~', $value)){
            $this->error(self::NO_UPPER);
            $nb_pb++;
        }
        if (1 !== preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $value)) {
            $this->error(self::NO_SPECHAR);
            $nb_pb++;
        }

        if ($nb_pb > 0) return false;
        return true;
    }

}