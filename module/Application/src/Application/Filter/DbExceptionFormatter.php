<?php

namespace Application\Filter;

use Doctrine\DBAL\DBALException;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Filter\AbstractFilter;

/**
 * Filtre traducteur de message d'erreur rencontrée par une base de données.
 */
class DbExceptionFormatter extends AbstractFilter
{
    protected $config = [
        [
            'pattern' => '#^ORA-01722.+$#',
            'message' => "Nombre invalide",
        ],
        [
            'pattern' => '#^.+unique constraint \(([a-zA-Z0-9_.]+)\) violated$#i',
            'message' => "Contrainte d'unicité non respectée",
            'next'    => [
                'pattern' => '#^SODOCT\.THESARD_COMPL_UN$#i',
                'message' => "Un doctorant ayant le même PersoP@ss existe déjà.",
            ],
        ],
    ];

    /**
     * Retourne le message d'erreur correspondant à une erreur rencontrée par une base de données.
     * La valeur en entrée doit être une exception de type DBALException.
     *
     * @param  DBALException $exception
     * @return string
     */
    public function filter($exception)
    {
        if (!$exception instanceof DBALException) {
            throw new RuntimeException(sprintf("Vous devez spécifier une exception de type %s", DBALException::class));
        }

        $message = $exception->getPrevious()->getMessage();

        foreach ($this->config as $data) {
            $found = $this->findTranslationForMatchingErrorPattern($message, $data);
            if ($found) {
                return $found;
            }
        }

        return $message;
    }

    /**
     * @param string $message       Message de l'erreur rencontrée
     * @param array  $data          De la forme ['pattern' => '', 'message' => "", 'next' => []]
     * @param string $previousFound Dernière traduction trouvée
     * @return string
     */
    private function findTranslationForMatchingErrorPattern($message, array $data, $previousFound = "")
    {
        if (!$data) {
            return "";
        }

        if (!preg_match($data['pattern'], $message, $matches)) {
            return $previousFound ?
                sprintf("%s (%s)", $previousFound, $message) :
                $previousFound;
        }

        if (!array_key_exists('next', $data)) {
            return $data['message'];
        }

        return $this->findTranslationForMatchingErrorPattern($matches[1], $data['next'], $data['message']);
    }
}