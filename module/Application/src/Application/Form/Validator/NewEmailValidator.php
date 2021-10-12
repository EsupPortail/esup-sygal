<?php

namespace Application\Form\Validator;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Validator\AbstractValidator;

class NewEmailValidator extends AbstractValidator {

    use EntityManagerAwareTrait;

    const NOT_YET       = 'not-yet';
    const NOT_EMAIL     = 'not-email';
    const USER          = 'user';
    const INDIVIDU      = 'individu';

    protected $messageTemplates = [
        self::NOT_YET       => "La méthode est pas implémentée",
        self::USER          => "Adresse électronique déjà enregistrée (Utilisateur)",
        self::INDIVIDU      => "Adresse électronique déjà enregistrée (Individu)",
        self::NOT_EMAIL     => "Adresse électronique mal formée",
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