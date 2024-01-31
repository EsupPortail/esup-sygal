<?php

namespace Admission\Filter;

use Admission\Entity\Db\Inscription;
use Laminas\Filter\AbstractFilter;

class AdmissionInscriptionFormatter extends AbstractFilter {

    public function filter($value)
    {
        // TODO: Implement filter() method.
    }

    public function htmlifyDiplomeIntituleInformations(Inscription $inscription){
        if ($inscription->getCoDirection()) {
            if($inscription->getCoDirecteur()){
                $nomPrenom = $inscription->getCoDirecteur()->getNomComplet() . " " . $inscription->getCoDirecteur()->getPrenom();
            }else{
                $nomPrenom = $inscription->getNomCodirecteurThese() . " " . $inscription->getPrenomCodirecteurThese();
            }
            return "Nom et prénom du co-directeur de thèse : ".$nomPrenom."<br>
                            Unité de recherche : <br>
                            Établissement de rattachement : <br>
                            Courriel : ".$inscription->getEmailCodirecteurThese();
        }
    }

    public function htmlifyCoTutelleInformations(Inscription $inscription){
        if ($inscription->getCoTutelle()) {
            $etablissementInscription = $inscription->getEtablissementInscription() ? $inscription->getEtablissementInscription()->getStructure() : null;
            $pays = $inscription->getPaysCoTutelle() ? $inscription->getPaysCoTutelle()->getLibelle() : "<b>Non renseigné</b>";
            $str = "Vu la convention de co-tutelle internationale de thèse entre l’établissement ".
                $etablissementInscription." (Normandie) et ".$pays;
            return $str;
        }
    }
}