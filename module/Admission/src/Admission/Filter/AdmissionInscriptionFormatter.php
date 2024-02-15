<?php

namespace Admission\Filter;

use Admission\Entity\Db\Inscription;
use Individu\Entity\Db\Individu;
use Laminas\Filter\AbstractFilter;

class AdmissionInscriptionFormatter extends AbstractFilter {

    public function filter($value)
    {
        // TODO: Implement filter() method.
    }

    public function htmlifyDiplomeIntituleInformations(Inscription $inscription){
        if ($inscription->getCoDirection()) {
            if($inscription->getCoDirecteur()){
                $nomPrenom = $inscription->getCoDirecteur()->getCiviliteToString() . " " .$inscription->getCoDirecteur()->getNomComplet();
            }else{
                $nomPrenom = $inscription->getNomCodirecteurThese() . " " . $inscription->getPrenomCodirecteurThese();
            }
            return "<b>Et </b>".$nomPrenom . ", co-directeur(-trice) de thèse<br>
                    <b>Fonction : </b>".$inscription->getFonctionCoDirecteurLibelle()." <br>
                    <b>Unité de recherche :</b> " . $inscription->getUniteRechercheCoDirecteurLibelle() . "<br>
                    <b>Établissement de rattachement :</b> " . $inscription->getEtablissementRattachementCoDirecteurLibelle() . " <br>
                    <b>Mail :</b> " . $inscription->getEmailCodirecteurThese();
        }
    }

    public function htmlifyCoTutelleInformations(Inscription $inscription){
        if ($inscription->getCoTutelle()) {
            $etablissementInscription = $inscription->getEtablissementInscription() ? $inscription->getEtablissementInscription()->getStructure() : null;
            $pays = $inscription->getPaysCoTutelle() ? $inscription->getPaysCoTutelle()->getLibelle() : "<b>Non renseigné</b>";
            $str = "- Vu la convention de co-tutelle internationale de thèse entre l’établissement <b>".
                $etablissementInscription."</b> (Normandie) et <b>".$pays."</b>";
            return $str;
        }
    }

    public function htmlifyResponsablesURDirecteur(array $responsables){
        $str = "<b>Aucune information de renseignée</b>";
        if($responsables){
            $str = "<ul>";
            foreach($responsables as $responsable){
                /** @var Individu $individu */
                $individu = $responsable->getIndividu();
                $str .= "<li>";
                $str .= $individu->getNomComplet();
                $str .= $individu->getEmailPro() ? ", ".$individu->getEmailPro() : null;
                $str .= "</li>";
            }
            $str .= "</ul>";
        }
        return $str;
    }

    public function htmlifyResponsablesURCoDirecteur(array $responsables){
        $str = "<b>Aucune information de renseignée</b>";
        if($responsables){
            $str = "<ul>";
                foreach($responsables as $responsable){
                    $individu = $responsable->getIndividu();
                    $str .= "<li>";
                    $str .= $individu->getNomComplet();
                    $str .= "</li>";
                }
            $str .= "</ul>";
        }
        return $str;
    }
}