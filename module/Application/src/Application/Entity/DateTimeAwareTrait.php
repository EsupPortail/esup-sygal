<?php

namespace Application\Entity;

use DateTime;
use Exception;
use UnicaenApp\Exception\RuntimeException;

trait DateTimeAwareTrait {

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        try {
            $date = new DateTime();
        } catch (Exception $e) {
            throw new RuntimeException("Un problème s'est produit lors de la récupération de la date", 0, $e);
        }

        return $date;
    }
}