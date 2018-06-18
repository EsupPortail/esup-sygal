<?php

namespace Application\Form\Validator;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Validator\AbstractValidator;

class NewEmailValidator extends AbstractValidator {

    use EntityManagerAwareTrait;

    const NOT_YET       = 'not-yet';
    const NOT_EMAIL     = 'not-email';
    const USER          = 'user';
    const INDIVIDU      = 'individu';

    protected $messageTemplates = [
        self::NOT_YET       => "La méthode est pas implémentée",
        self::USER          => "Email déjà enregistré (Utilisateur)",
        self::INDIVIDU      => "Email déjà enregistré (Individu)",
        self::NOT_EMAIL     => "Email mal formé",
    ];

    public function isValid($value)
    {
        $nb_pb = 0;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->error(self::NOT_EMAIL);
            $nb_pb++;
        }
        if ($this->getEntityManager()->getRepository(Individu::class)->findOneBy(['email' => $value]) !== null) {
            $this->error(self::INDIVIDU);
            $nb_pb++;
        }
        if ($this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(['email' => $value]) !== null) {
            $this->error(self::USER);
            $nb_pb++;
        }

        if ($nb_pb > 0) return false;
        return true;
    }


}