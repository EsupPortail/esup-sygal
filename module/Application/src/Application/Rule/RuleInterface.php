<?php

namespace Application\Rule;

interface RuleInterface
{
    /**
     * Lance le "calcul" de la règle métier.
     *
     * @return mixed
     */
    public function execute();
}