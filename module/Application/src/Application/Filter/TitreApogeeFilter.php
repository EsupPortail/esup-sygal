<?php

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Class TitreApogeeFilter
 *
 * Les titres de thèses peuvent comporter des symboles mathématiques.
 * Pour gérer ça, l'AMUE a mis au point une police de caractères qui fait croire à l'utilisateur qu'il saisit
 * par exemple un ∃ alors qu'en réalité Apogée stocke un Ü turc en win1254.
 * Cela devra se faire en php dans l'application et non avec une fonction Oracle puisque Oracle ne peut pas stocker
 * le résultat du transcodage sur un seul caractère.
 *
 * @package Application\Filter
 */
class TitreApogeeFilter extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!$value) {
            return $value;
        }
        
        return $this->tr($value);
    }

    private function tr($strin)
    {
        $mapping = [
            "A"  => "A",
            "B"  => "B",
            "C"  => "C",
            "D"  => "D",
            "E"  => "E",
            "1"  => "1",
            "2"  => "2",
            "3"  => "3",
            "4"  => "4",
            "5"  => "5",
            "Ø"  => "∫",
            "Ù"  => "∞",
            "Ú"  => "√",
            "Û"  => "∀",
            "Ü"  => "∃",
            "ß"  => "∩",
            "á"  => "∈",
            "®"  => "≤",
            "ä"  => "≥",
            "Ä"  => "π",
            "±"  => "±",
            "º"  => "⁰",
            "¹"  => "¹",
            "²"  => "²",
            "³"  => "³",
            "#"  => "⁴",
            "$"  => "⁵",
            "ù"  => "⁶",
            "@"  => "⁷",
            "\\" => "⁸",
            "^"  => "⁹",
            "å"  => "ᵃ", //7491
            "æ"  => "ⁿ",
            "ë"  => "ᵗ", //7511
            "ì"  => "ˣ", //739
            "¡"  => "⁺",
            "ñ"  => "⁻",
//            "«"  => "₀", // commenté pour respecter l'utilisation possible dans Apogée de "«"
            "_"  => "₁",
            "ú"  => "₂",
            "~"  => "₃",
            "¤"  => "₄",
            "¥"  => "₅",
            "¦"  => "₆",
            "§"  => "₇",
            "©"  => "₈",
            "ª"  => "₉",
            "ò"  => "ₐ", //8336
            "¢"  => "ₙ", //8345
            "õ"  => "ₜ", //8348
            "¬"  => "ₓ", //8339
            "÷"  => "₊",
            "ø"  => "₋",
            "¯"  => "α",
            "£"  => "β",
            "¶"  => "γ",
            "·"  => "δ",
//            "»"  => "ε", // commenté pour respecter l'utilisation possible dans Apogée de "»"
            "¼"  => "ζ",
            "½"  => "η",
            "¾"  => "θ",
            "¿"  => "ι",
            "À"  => "κ",
            "Á"  => "λ",
            "µ"  => "μ",
            "Â"  => "ν",
            "Ã"  => "ξ",
            "o"  => "ο",
            "Ä"  => "π",
            "Å"  => "ρ", //961 => manque 962 ς
            "Æ"  => "σ", //963
            "Ç"  => "τ",
            "È"  => "υ",
            "É"  => "φ",
            "Ê"  => "χ",
            "Ë"  => "ψ",
            "Ì"  => "ω",
            "A"  => "Α",
            "B"  => "Β",
            "Í"  => "Γ",
            "Î"  => "Δ",
            "E"  => "Ε",
            "Z"  => "Ζ",
            "H"  => "Η",
            "Ï"  => "Θ",
            "I"  => "Ι",
            "K"  => "Κ",
            "Ñ"  => "Λ",
            "M"  => "Μ",
            "N"  => "Ν",
            "Ò"  => "Ξ",
            "O"  => "Ο",
            "Ó"  => "Π",
            "P"  => "Ρ",
            "Ô"  => "Σ",
            "T"  => "Τ",
            "Y"  => "Υ",
            "Õ"  => "Φ",
            "X"  => "Χ",
            "Ö"  => "Ψ",
            "×"  => "Ω",
            "&"  => "&",
            "í"  => "í",
            "ó"  => "ó",
            "°"  => "°",
            "ö"  => "ö",
            "ã"  => "ã",
        ];

        return strtr($strin, $mapping);
    }
}