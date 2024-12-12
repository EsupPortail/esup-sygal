<?php

namespace Formation\Renderer;

use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class FormationSessionTemplateVariable extends AbstractTemplateVariable
{
    private Session $session;

    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    /** @noinspection PhpUnused */
    public function getPeriode() : string {
        $jour_debut = $this->session->getDateDebut()->format('d/m/Y');
        $jour_fin = $this->session->getDateFin()->format('d/m/Y');

        if ($jour_debut === $jour_fin) return $jour_debut;
        return $jour_debut." au ".$jour_fin;
    }

    /** @noinspection PhpUnused */
    public function getSeancesAsTable() : string
    {
        $seances = $this->session->getSeances()->toArray();
        $seances = array_filter($seances, function(Seance $a) { return $a->estNonHistorise();});
        usort($seances, function (Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut(); });

        if (empty($seances)) return "Aucune séance d'associée à cette session de formation.";

        $texte  = '<table>';
        $texte .= '<thead><tr>';
        $texte .= '<th> Jour de la séance </th><th> Heure de début </th><th> Heure de fin </th><th> Lieu </th>';
        $texte .= '</tr></thead>';
        $texte .= '<tbody>';
        /** @var Seance $seance */
        foreach ($seances as $seance) {
            $texte .= '<tr>';
            $texte .= '<td>'.$seance->getDebut()->format('d/m/Y').'</td>';
            $texte .= '<td>'.$seance->getDebut()->format('H:i').'</td>';
            $texte .= '<td>'.$seance->getFin()->format('H:i').'</td>';
            $texte .= '<td>';
            if ($seance->getLieu() !== null) {
                if ($seance->getLien() !== null){
                    $texte .= "Lieu :".$seance->getLieu();
                    $texte .= "<br>";
                    $texte .= '<a href="'.$seance->getLien().'">'.$seance->getLien().'</a>';
                    if ($seance->getMotDePasse()) $texte .= "<br> (mot de passe: " . $seance->getMotDePasse() . ")";
                }else{
                    $texte .= $seance->getLieu();
                }
            } else {
                if ($this->session->getModalite() === Session::MODALITE_PRESENTIEL) {
                    $texte .= "<em> Lieu non renseigné </em>";
                } else {
                    if($seance->getLien() !== null){
                        $texte .= "Distanciel (lien : <a href=".$seance->getLien().">".$seance->getLien()."</a>)";
                        $texte .= $seance->getMotDePasse() ? "<br> (mot de passe: ".$seance->getMotDePasse().")" : "";
                    }else{
                        $texte .= "Distanciel <em>(lien non renseigné)</em>";
                    }
                }
            }
            $texte .='</td>';
            $texte .= '</tr>';
        }
        $texte .= '</tbody>';
        $texte .= '</table>';
        return $texte;
    }

    /** @noinspection PhpUnused */
    public function getSeancesSansLieuAsTable() : string
    {
        $seances = $this->session->getSeances()->toArray();
        $seances = array_filter($seances, function(Seance $a) { return $a->estNonHistorise();});
        usort($seances, function (Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut(); });

        if (empty($seances)) return "Aucune séance d'associée à cette session de formation.";

        $texte  = '<table>';
        $texte .= '<thead><tr>';
        $texte .= '<th> Jour de la séance </th><th> Heure de début </th><th> Heure de fin </th><th> Lieu </th>';
        $texte .= '</tr></thead>';
        $texte .= '<tbody>';
        /** @var Seance $seance */
        foreach ($seances as $seance) {
            $texte .= '<tr>';
            $texte .= '<td>'.$seance->getDebut()->format('d/m/Y').'</td>';
            $texte .= '<td>'.$seance->getDebut()->format('H:i').'</td>';
            $texte .= '<td>'.$seance->getFin()->format('H:i').'</td>';
            $texte .= '<td><em> Non visible </em></td>';
            $texte .= '</tr>';
        }
        $texte .= '</tbody>';
        $texte .= '</table>';
        return $texte;
    }

    /** @noinspection PhpUnused */
    public function getDenominationResponsable() : string
    {
        $responsable = $this->session->getResponsable();
        return $responsable?$responsable->getNomComplet():"Aucun responsable de désigner pour cette session";
    }
}