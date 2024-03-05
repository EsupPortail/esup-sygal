<?php

namespace Admission\Filter;

use Admission\Entity\Db\Financement;
use Laminas\Filter\AbstractFilter;
use Structure\Entity\Db\Structure;

class AdmissionFinancementFormatter extends AbstractFilter
{

    public function filter($value)
    {
        // TODO: Implement filter() method.
    }

    public function htmlifyConventionCollaborationInformations(Financement $financement, Structure|null $etablissementInscription)
    {
        if ($financement->getEstSalarie()) {
            $etablissementLaboratoireUR = $financement->getEtablissementLaboratoireRecherche();
            $str = "- Vu la convention de collaboration entre l’employeur <b>[dénomination de l’établissement partenaire, ville,
            pays]</b>, le salarié doctorant, l’établissement d’inscription <b>".$etablissementInscription."</b>
            (Normandie)";

            if ($financement->getEtablissementLaboratoireRecherche()) {
                $str .= " et, l’établissement hébergeant le laboratoire de recherche
                d’accueil du salarié doctorant <b>" . $etablissementLaboratoireUR . "</b>.";
            }else{
                $str .= ".";
            }
            return $str;
        }
    }
}