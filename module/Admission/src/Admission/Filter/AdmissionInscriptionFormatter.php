<?php

namespace Admission\Filter;

use Admission\Entity\Db\ConventionFormationDoctorale;
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
                $nomPrenom = $inscription->getCoDirecteur()->getCivilite() . " " .$inscription->getCoDirecteur()->getNomComplet();
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
            $etablissementInscription = $inscription->getEtablissementInscription()?->getStructure();
            $pays = $inscription->getPaysCoTutelle() ? $inscription->getPaysCoTutelle()->getLibelle() : "<b>Non renseigné</b>";
            $str = "- Vu la convention de co-tutelle internationale de thèse entre l’établissement <b>".
                $etablissementInscription."</b> (Normandie) et <b>".$pays."</b>";
            return $str;
        }
    }

    public function htmlifyCoTutelleCoDirectionInformations(Inscription $inscription, array $responsablesURCoDirection){
        if ($inscription->getCoTutelle()) {
            $str = "Et<br><br>";
            $pays = $inscription->getPaysCoTutelle() ? $inscription->getPaysCoTutelle()->getLibelle() : "<b>Non renseigné</b>";
            $str .= "Pays : ";
            $str .= $pays;
        }else if($inscription->getCoDirection()){
            $str = "Et<br>";
            $uniteRechercheCoDirecteur = $inscription->getUniteRechercheCoDirecteurLibelle() ? $inscription->getUniteRechercheCoDirecteurLibelle() : "<b>Non renseignée</b>";
            if(!$responsablesURCoDirection && $inscription->getUniteRechercheCoDirecteurLibelle()){
                $responsablesUniteRechercheCoDirecteur = "<b>Aucune direction n'est désignée dans l'application</b>";
            }else if(!$responsablesURCoDirection && !$inscription->getUniteRechercheCoDirecteurLibelle()){
                $responsablesUniteRechercheCoDirecteur = "<b>Non renseigné</b>";
            }else{
                $responsablesUniteRechercheCoDirecteur = $this->htmlifyResponsablesURCoDirecteur($responsablesURCoDirection, true);
            }

            $str .= "<ul>";
            $str .= "<li> Unité d'accueil : ".$uniteRechercheCoDirecteur."</li>";
            $str .= "<li> Directeur de l'unité : ".$responsablesUniteRechercheCoDirecteur."</li>";
            $str .= "</ul>";
        }
        return $str ?? "";
    }

    public function htmlifyResponsablesURDirecteur(Inscription $inscription, array $responsablesURDirection){
        if(!$responsablesURDirection && $inscription->getUniteRecherche()){
            $str = "<b>Aucune direction n'est désignée dans l'application</b>";
        }else if(!$responsablesURDirection && !$inscription->getUniteRecherche()){
            $str = "<b>Non renseigné</b>";
        }else{
            $str = "<ul>";
            foreach($responsablesURDirection as $responsable){
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

    public function htmlifyResponsablesURCoDirecteur(array $responsables, bool $showMoreInformations = false){
        $str = "<b>Aucune information de renseignée</b>";
        if($responsables){
            $str = "<ul>";
                foreach($responsables as $responsable){
                    /** @var Individu $individu */
                    $individu = $responsable->getIndividu();
                    $str .= "<li>";
                    $str .= $individu->getNomComplet();
                    if($showMoreInformations){
                        $str .= $individu->getEmailPro() ? ", ".$individu->getEmailPro() : null;
                    }
                    $str .= "</li>";
                }
            $str .= "</ul>";
        }
        return $str;
    }

    public function htmlifyConventionCollaborationInformations(Inscription $inscription, bool $estSalarie, string $etablissementPartenaire)
    {
        if ($estSalarie) {
            $etablissementLaboratoireUR = $inscription->getEtablissementLaboratoireRecherche();
            $etablissementInscription = $inscription->getEtablissementInscription()?->getStructure();
            $etablissementPartenaire = $etablissementPartenaire ?: " <b>(Aucune information déclarée concernant l'employeur)</b>";
            $str = "- Vu la convention de collaboration entre l’employeur <b>".$etablissementPartenaire."</b>, le salarié doctorant, l’établissement d’inscription <b>".$etablissementInscription."</b>
            (Normandie)";

            if ($inscription->getEtablissementLaboratoireRecherche()) {
                $str .= " et, l’établissement hébergeant le laboratoire de recherche
                d’accueil du salarié doctorant <b>" . $etablissementLaboratoireUR . "</b>.";
            }else{
                $str .= ".";
            }
            return $str;
        }
    }

    public function htmlifyConfidentialiteInformations(Inscription $inscription, ConventionFormationDoctorale $conventionFormationDoctorale)
    {
        if($inscription->getConfidentialite() === null){
            return "<b>Non renseigné</b>";
        }else{
            if($inscription->getConfidentialite()){
                $dateConfidentialite = $inscription->getDateConfidentialite() ? $inscription->getDateConfidentialite()->format("d/m/Y") : null;
                return "Oui <br> 
                        <ul>
                          <li>
                             <b>Date de fin de confidentialité souhaitée (limitée à 10 ans) : </b>" . $dateConfidentialite . "
                          </li>
                          <li>
                             <b>Motivation de la demande de confidentialité par le doctorant et la direction de thèse: </b>" .
                                $conventionFormationDoctorale->getMotivationDemandeConfidentialite() . "
                          </li>
                        </ul>";
            } else {
                return "Non";
            }
        }
    }
}