<?php

namespace Soutenance\Rule;

use Application\Rule\RuleInterface;
use DateTime;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\PropositionThese;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class PropositionJuryRule implements RuleInterface
{
    use ParametreServiceAwareTrait;

    private Proposition $proposition;
    private array $indicateurs;

    public function setProposition(Proposition $proposition): void
    {
        $this->proposition = $proposition;
    }

    public function execute(): void
    {
        $this->computeIndicateurs();
    }

    public function getIndicateurs(): array
    {
        return $this->indicateurs;
    }

    public function computeIndicateurs(): void
    {
        $forcerValidite = $this->isValiditeForcee();

        $nbMembre = 0;
        $nbFemme = 0;
        $nbHomme = 0;
        $nbRangA = 0;
        $nbExterieur = 0;
        $nbEmerites = 0;
        $nbRapporteur = 0;

        $categorieCode = ($this->proposition instanceof PropositionThese) ? SoutenanceParametres::CATEGORIE : \Soutenance\Provider\Parametre\HDR\SoutenanceParametres::CATEGORIE;

        $membre_min     =  $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::NB_MIN_MEMBRE_JURY);
        $membre_max     =  $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::NB_MAX_MEMBRE_JURY);
        $rapporteur_min =  $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::NB_MIN_RAPPORTEUR);
        $rangA_min      =  ((float) $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::RATIO_MIN_RANG_A));
        $exterieur_min  =  ((float) $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::RATIO_MIN_EXTERIEUR));
        $emerites_max   =  (float) $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::RATIO_MAX_EMERITES);
        $parite_min     =  ((float) $this->getParametreService()->getValeurForParametre($categorieCode, SoutenanceParametres::EQUILIBRE_FEMME_HOMME));

        /** @var Membre $membre */
        foreach ($this->proposition->getMembres() as $membre) {
            $nbMembre++;
            if ($membre->getGenre() === "F") $nbFemme++; else $nbHomme++;
            if ($membre->getRang() === "A") $nbRangA++;
            if ($membre->isExterieur()) $nbExterieur++;
            if ($membre->getQualite()->isEmeritat()) $nbEmerites++;
            if ($membre->estRapporteur()) $nbRapporteur++;
        }

        $indicateurs = [];

        /** Bad rapporteur */
        $nbRapporteursBad = 0;
        foreach ($this->proposition->getMembres() as $membre) {
            if ($membre->estRapporteur() and $membre->getQualite()->isRangB() and $membre->getQualite()->getHdr() !== 'O') {
                $nbRapporteursBad++;
            }
        }
        if (!$forcerValidite && $nbRapporteursBad > 0) {
            $indicateurs["bad-rapporteur"]["valide"] = false;
            $indicateurs["bad-rapporteur"]["alerte"] = "Des rapporteurs de rang B ne sont pas titulaires d'une HDR";
        } else {
            $indicateurs["bad-rapporteur"]["valide"] = true;
        }
        $indicateurs["bad-rapporteur"]["Nombre"] = $nbRapporteursBad;

        /**  Il faut essayer de maintenir la parité Homme/Femme*/
        $ratioFemme = ($nbMembre) ? $nbFemme / $nbMembre : 0;
        $ratioHomme = ($nbMembre) ? (1 - $ratioFemme) : 0;
        $indicateurs["parité"] = ["Femme" => $ratioFemme, "Homme" => $ratioHomme];
        if (!$forcerValidite && min($ratioFemme, $ratioHomme) < $parite_min) {
            $indicateurs["parité"]["valide"] = false;
            $indicateurs["parité"]["alerte"] = "La parité n'est pas respectée";
        } else {
            $indicateurs["parité"]["valide"] = true;
        }

        /** entre 4 et 8 membres */
        $indicateurs["membre"] = ["Nombre" => $nbMembre, "Ratio" => ($nbMembre) ? $nbMembre / 10 : 0];

        if (!$forcerValidite && ($nbMembre < $membre_min || $nbMembre > $membre_max)) {
            $indicateurs["membre"]["valide"] = false;
            $indicateurs["membre"]["alerte"] = "Le jury doit être composé de $membre_min à $membre_max personnes";
        } else {
            $indicateurs["membre"]["valide"] = true;
        }

        /** Au moins deux rapporteurs */
        $indicateurs["rapporteur"] = ["Nombre" => $nbRapporteur, "Ratio" => ($nbMembre) ? $nbRapporteur / $nbMembre : 0];

        if (!$forcerValidite && $nbRapporteur < $rapporteur_min) {
            $indicateurs["rapporteur"]["valide"] = false;
            $indicateurs["rapporteur"]["alerte"] = "Le nombre minimum de rapporteurs attendu est de $rapporteur_min";
        } else {
            $indicateurs["rapporteur"]["valide"] = true;
        }

        /** Au moins la motié du jury de rang A */
        $ratioRangA = ($nbMembre) ? ($nbRangA / $nbMembre) : 0;
        $indicateurs["rang A"] = ["Nombre" => $nbRangA, "Ratio" => $ratioRangA];
        if (!$forcerValidite && ($ratioRangA < $rangA_min || !$nbMembre)) {
            $indicateurs["rang A"]["valide"] = false;
            if($ratioRangA === 0){
                $indicateurs["rang A"]["alerte"] = "Le nombre de membres de rang A doit représenter au moins la moitié du jury";
            }else{
                $indicateurs["rang A"]["alerte"] = "Le nombre de membres de rang A doit représenter au minimum " . ($ratioRangA * 100) . '%';
            }
        } else {
            $indicateurs["rang A"]["valide"] = true;
        }

        /** Au moins la motié du jury exterieur*/
        $ratioExterieur = ($nbMembre) ? ($nbExterieur / $nbMembre) : 0;
        $indicateurs["exterieur"] = ["Nombre" => $nbExterieur, "Ratio" => $ratioExterieur];
        if (!$forcerValidite && ($ratioExterieur < $exterieur_min || !$nbMembre)) {
            $indicateurs["exterieur"]["valide"] = false;
            if($ratioExterieur === 0){
                $indicateurs["exterieur"]["alerte"] = "Le nombre de membres extérieurs doit représenter au moins la moitié du jury";
            }else{
                $indicateurs["exterieur"]["alerte"] = "Le nombre de membres extérieurs doit représenter au minimum " . ($ratioRangA * 100) . '%';
            }
        } else {
            $indicateurs["exterieur"]["valide"] = true;
        }

        /** ratio minimum d'émérites */
        $ratioEmerites = $nbMembre ? ($nbEmerites / $nbMembre) : 0;
        $indicateurs["emerites"] = ["Nombre" => $nbEmerites, "Ratio" => $ratioEmerites];
        if (!$forcerValidite && $ratioEmerites > $emerites_max) {
            $indicateurs["emerites"]["valide"] = false;
            $indicateurs["emerites"]["alerte"] = "Le nombre d'émérites ne doit pas dépasser " . ($emerites_max * 100.0) . '%';
        } else {
            $indicateurs["emerites"]["valide"] = true;
        }

        $valide =
            $indicateurs["parité"]["valide"] &&
            $indicateurs["membre"]["valide"] &&
            $indicateurs["rapporteur"]["valide"] &&
            $indicateurs["rang A"]["valide"] &&
            $indicateurs["exterieur"]["valide"] &&
            $indicateurs["bad-rapporteur"]["valide"] &&
            $indicateurs["emerites"]["valide"];

        $indicateurs["valide"] = $valide;

        $this->indicateurs = $indicateurs;
    }

    private function isValiditeForcee(): bool
    {
        // dès lors que la date de soutenance est passée, tout est considéré comme valide !
        return ($dateSoutenance = $this->proposition->getDate()) !== null
            && $dateSoutenance->setTime(0,0) < new DateTime('today');
    }
}