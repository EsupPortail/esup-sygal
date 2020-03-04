<?php

namespace ComiteSuivi\Entity;

use DateTime;
use Exception;
use UnicaenApp\Exception\RuntimeException;

trait DateTimeTrait {

    private function getDateTime()
    {
        try {
            $date = new DateTime();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération de la date",0, $e);
        }
        return $date;
    }

    public function getAnneeScolaire()
    {
        $date = $this->getDateTime();
        /** @var integer $annee */
        $annee = $date->format('Y');
        $mois = $date->format('m');

        if ($mois < '09') {
            return ($annee-1).'/'.$annee;
        }
        return ($annee).'/'.($annee+1);
    }
}