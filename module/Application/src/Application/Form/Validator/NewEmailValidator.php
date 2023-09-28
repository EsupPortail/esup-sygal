<?php

namespace Application\Form\Validator;

use Application\Entity\Db\Utilisateur;
use Individu\Entity\Db\Individu;
use Laminas\Validator\AbstractValidator;
use UnicaenApp\Service\EntityManagerAwareTrait;

class NewEmailValidator extends AbstractValidator
{
    use EntityManagerAwareTrait;

    const NOT_EMAIL = 'not-email';
    const UTILISATEUR = 'utilisateur';
    const INDIVIDU = 'individu';

    protected array $perimetre = [];

    protected array $messageTemplates = [
        self::UTILISATEUR => "Adresse électronique déjà utilisée (Utilisateur)",
        self::INDIVIDU => "Adresse électronique déjà utilisée (Individu)",
        self::NOT_EMAIL => "Adresse électronique mal formée",
    ];

    public function isValid($value): bool
    {
        $nb_pb = 0;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->error(self::NOT_EMAIL);
            $nb_pb++;
        }

        $perimetre = $this->getPerimetre();

        if (in_array('utilisateur', $perimetre)) {
            if ($this->entityManager->getRepository(Utilisateur::class)->findOneBy(['username' => $value]) !== null) {
                $this->error(self::UTILISATEUR);
                $nb_pb++;
            }
        }
        if (in_array('individu', $perimetre)) {
            if ($this->entityManager->getRepository(Individu::class)->findOneBy(['email' => $value]) !== null) {
                $this->error(self::INDIVIDU);
                $nb_pb++;
            }
        }

        if ($nb_pb > 0) return false;
        return true;
    }

    /**
     * @param string[] $perimetre
     */
    public function setPerimetre(array $perimetre): void
    {
        $this->perimetre = $perimetre;
    }

    /**
     * @return string[]
     */
    public function getPerimetre(): array
    {
        return $this->perimetre;
    }
}