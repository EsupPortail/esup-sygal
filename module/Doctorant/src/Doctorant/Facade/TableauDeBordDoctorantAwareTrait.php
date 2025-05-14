<?php

namespace Doctorant\Facade;

trait TableauDeBordDoctorantAwareTrait
{
    /**
     * @var TableauDeBordDoctorantFacade
     */
    protected $tableauDeBordDoctorantFacade;

    /**
     * @param TableauDeBordDoctorantFacade $tableauDeBordDoctorantFacade
     */
    public function setTableauDeBordDoctorantFacade(TableauDeBordDoctorantFacade $tableauDeBordDoctorantFacade)
    {
        $this->tableauDeBordDoctorantFacade = $tableauDeBordDoctorantFacade;
    }
}