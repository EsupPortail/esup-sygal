<?php

namespace Substitution;

use BadMethodCallException;
use Laminas\Mvc\Controller\AbstractController;
use Webmozart\Assert\Assert;

trait TypeAwareTrait
{
    protected string $type;

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Dans le contexte d'un contrôleur, retourne le type spécifié dans la route de la requête courante.
     */
    protected function getRequestedType(): string
    {
        if (!is_subclass_of($this, AbstractController::class)) {
            throw new BadMethodCallException("Cette méthode doit être appelée sur un contrôleur !");
        }

        $type = $this->params()->fromRoute('type');
        Assert::inArray($type, Constants::TYPES);

        return $type;
    }
}