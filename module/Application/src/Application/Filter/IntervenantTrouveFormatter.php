<?php

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
use Application\Entity\Db\IntervenantInterface;
use stdClass;
use Application\Constants;

/**
 * Formatte un intervenant pour le transmettre à l'élément de formulaire SearchAndSelect.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\Form\Element\SearchAndSelect
 */
class IntervenantTrouveFormatter extends AbstractFilter
{
    /**
     * @var NomCompletFormatter
     */
    protected $nomCompletFormatter;

    /**
     * Constructeur.
     *
     * @param IntervenantInterface|stdClass|array $intervenant
     */
    public function __construct()
    {
        $this->nomCompletFormatter = new NomCompletFormatter(true, false, true);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param IntervenantInterface|stdClass|array $value
     * @return string
     */
    public function filter($value)
    {
        // normalisation
        if ($value instanceof IntervenantInterface) {
            /* @var $value IntervenantInterface */
            $id        = $value->getSourceCode();
            $label     = $this->nomCompletFormatter->filter($value);
            $civilite  = $value->getCiviliteToString();
            $dateNaiss = $value->getDateNaissance();
            $feminin   = $value->estUneFemme();
            $affectat  = $value->getAffectationsToString();
        }
        else if ($value instanceof \stdClass) {
            foreach (['civilite', 'sourceCode', 'dateNaissance', 'estUneFemme', 'affectation'] as $prop) {
                if (!isset($value->$prop)) {
                    throw new \LogicException("L'objet à formatter doit posséder l'attribut public '$prop'.");
                }
            }
            $id        = $value->sourceCode;
            $label     = $this->nomCompletFormatter->filter($value);
            $civilite  = $value->civilite;
            $dateNaiss = $value->dateNaissance;
            $feminin   = $value->estUneFemme();
            $affectat  = $value->affectation;
        }
        else if (is_array($value)) {
            foreach (['civilite', 'source_code', 'date_naissance', 'est_une_femme', 'affectation'] as $prop) {
                if (!array_key_exists($prop, $value)) {
                    throw new \LogicException("Le tableau à formatter doit posséder la clé '$prop'.");
                }
            }
            $id        = $value['source_code'];
            $label     = $this->nomCompletFormatter->filter($value);
            $civilite  = $value['civilite'];
            $dateNaiss = $value['date_naissance'];
            $feminin   = $value['est_une_femme'];
            $affectat  = $value['affectation'];
        }
        else {
            throw new \LogicException("L'objet à formatter n'est pas d'un type supporté.");
        }

        if (!$dateNaiss instanceof \DateTime) {
            $dateNaiss = new \DateTime($dateNaiss);
        }

        $extra  = sprintf("(%s, né%s le %s, n°%s, %s)",
                $civilite,
                (boolean) $feminin ? 'e' : '',
                $dateNaiss->format(Constants::DATE_FORMAT),
                $id ?: "Inconnu",
                $affectat ?: "Affectation inconnue");

        $result = [
            'id'    => $id,
            'label' => $label,
            'extra' => $extra,
        ];

	return $result;
    }
}