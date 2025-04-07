<?php

namespace Admission\Filter;

use Admission\Entity\Db\Etudiant;
use Laminas\Filter\AbstractFilter;

class AdmissionEtudiantFormatter extends AbstractFilter {

    public function filter($value)
    {
        // TODO: Implement filter() method.
    }

    public function htmlifyDiplomeIntituleInformations(Etudiant $etudiant){
        if ($etudiant->getNiveauEtude() == 1) {
            return "<table>
                       <tr>
                        <th>Intitulé</th>
                        <th>Année d'obtention</th>
                        <th>Établissement d'obtention</th>
                       </tr>
                       <tr>
                          <td>".$etudiant->getIntituleDuDiplomeNational()."</td>
                          <td>".$etudiant->getAnneeDobtentionDiplomeNational()."</td>
                          <td>".$etudiant->getEtablissementDobtentionDiplomeNational()."</td>
                        </tr>
                     </table>";
        }elseif($etudiant->getNiveauEtude() == 2){
            $typeDiplome = null;
            if($etudiant->getTypeDiplomeAutre() == 1){
                $typeDiplome = "Diplôme obtenu à l'étranger";
            }elseif($etudiant->getTypeDiplomeAutre() == 2){
                $typeDiplome = "Diplôme français ne conférant pas le grade de master";
            }

            return "<table>
                       <tr>
                        <th>Intitulé</th>
                        <th>Année d'obtention</th>
                        <th>Etablissement d'obtention</th>
                        <th>Informations supplémentaires</th>
                       </tr>
                       <tr>
                          <td>".$etudiant->getIntituleDuDiplomeAutre()."</td>
                          <td>".$etudiant->getAnneeDobtentionDiplomeAutre()."</td>
                          <td>".$etudiant->getEtablissementDobtentionDiplomeAutre()."</td>
                          <td>".$typeDiplome."</td>
                        </tr>
                     </table>";
        }
    }
}

