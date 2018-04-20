<?php

namespace Application\View\Helper;

use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\View\Renderer\PhpRenderer;
use UnicaenApp\Form\Element\SearchAndSelect;

class StructureArrayHelper extends AbstractHelper
{
    /**
     * @return string
     */
    public function render()
    {
        return '';
    }


    public function test()
    {
        return 'test';
    }

    /**
     * @param $structures
     * @param $roles
     * @param $effectifs
     * @param $selected
     * @param $view
     * @return string
     */
    function generateTable($structures, $roles, $effectifs, $selected, $view)
    {
        $texte = '';

        $texte .= '<table class="table table-condensed">';

        $texte .= '    <thead>';
        $texte .= '    <tr>';
        $texte .= '        <th>' . $view->translate("Libellé") . '</th>';
        $texte .= '        <th>' . $view->translate("Sigle") . '</th>';
        $texte .= '        <th>' . $view->translate("Actions") . '</th>';
        $texte .= '    </tr>';
        $texte .= '    </thead>';

        $texte .= '    <tbody>';
        foreach ($structures as $structure) {
            $isSelected = $selected !== null && $structure->getId() === (int)$selected;
            if ($isSelected) {
                $texte .= $this->generateSelectedStructure($structure, $roles, $effectifs, $view);
            } else {
                $texte .= $this->generateUnselectedStructure($structure, $view);
            }
        }

        $texte .= '    </tbody>';

        $texte .= '</table>';

        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @param PhpRenderer $view
     * @return string
     */
    function generateUnselectedStructure($structure, $view)
    {
        $texte = '';
        $texte .= '    <tr class="ecole-doctorale">';
        $texte .= $this->generateLibelleCell($structure, $view);
        $texte .= $this->generateSigleCell($structure);
        $texte .= $this->generateActionCell($structure, $view);
        $texte .= '    </tr>';
        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @param Role[] $roles
     * @param array(string =>IndividuRole[]) $effectifs
     * @param PhpRenderer $view
     * @return string
     */
    function generateSelectedStructure($structure, $roles, $effectifs, $view)
    {
        $texte = '';
        $texte .= '    <tr class="ecole-doctorale selected">';
        $texte .= $this->generateLibelleCell($structure, $view);
        $texte .= $this->generateSigleCell($structure);
        $texte .= $this->generateActionCell($structure, $view);
        $texte .= '    </tr>';
        $texte .= '    <tr class="selected">';
        $texte .= '    <td colspan="3" class="bg-info">';
        $texte .= $this->generateRoleSection($roles, $effectifs, $view);
        $texte .= $this->generateMembreSection($structure, $roles, $effectifs, $view);
        $texte .= $this->generateLogo($structure);
        $texte .= $this->generateAjoutMembre($structure, $roles, $view);
        $texte .= '    </td>';
        $texte .= '    </tr>';

        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @param PhpRenderer $view
     * @return string
     */
    function generateLibelleCell($structure, $view)
    {
        $query = ['selected' => $structure->getId()];
        $texte = '';
        $texte .= '         <td>';
        $texte .= '             <a name="' . $structure->getId() . '"></a>';
        $texte .= '             <a href="' . $view->url(null, [], ['query' => $query], true) . '">';
        $texte .= $view->etab($structure)->getLibelle();
        $texte .= '             </a>';
        $texte .= '         </td>';
        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @return string
     */
    function generateSigleCell($structure)
    {
        $texte = '';
        $texte .= '         <td>';
        $texte .= $structure->getSigle();
        $texte .= '         </td>';
        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @param PhpRenderer $view
     * @return string
     */
    function generateActionCell($structure, $view)
    {
        $nombreSousStructure = count($structure->getStructure()->getStructuresSubstituees());

        $urlModifier = $view->url('etablissement/modifier', ['etablissement' => $structure->getId()], [], true);
        $urlSupprimer = $view->url('etablissement/supprimer', ['etablissement' => $structure->getId()], [], true);
        $urlRestaurer = $view->url('etablissement/restaurer', ['etablissement' => $structure->getId()], [], true);
        $urlSubstituer = $view->url('substitution-modifier', ['cible' => $structure->getStructure()->getId(), [], true]);

        $texte = '';
        $texte .= '         <td>';
        if ($structure->estNonHistorise()) {
            $texte .= '             <a href="' . $urlModifier . '"><span class="glyphicon glyphicon-pencil"></span></a>';
            $texte .= '             <a href="' . $urlSupprimer . '"><span class="glyphicon glyphicon-trash"></span></a>';
            if ($nombreSousStructure > 0) {
                $texte .= '                 <a href="' . $urlSubstituer . '"><span class="badge">' . $nombreSousStructure . '</span></a>';
            }
        } else {
            $texte .= '             <a href="' . $urlRestaurer . '"> Restaurer </a>';
        }
        $texte .= '         </td>';
        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @return string
     */
    function generateLogo($structure)
    {
        $texte = '';
        $content = $structure->getStructure()->getLogoContent();
        if ($content === null) {
            $structure->getStructure()->setCheminLogo(null);
        }

        $texte .= '<div id="logo-div" class="pull-right">';
        $texte .= '<img style="max-width: 200px; max-height: 200px; border: 1px solid black; background-color: white;" src="data:image/png;base64,' . base64_encode($structure->getLogoContent()) . '" />';
        $texte .= '</div>';

        return $texte;
    }

    /**
     * @param Role[] $roles
     * @param array(string =>IndividuRole[]) $effectifs
     * @param PhpRenderer $view
     * @return string
     */
    function generateRoleSection($roles, $effectifs, $view)
    {
        $texte = '';
        $texte .= '<h3> Rôles <span class="badge">' . count($roles) . '</span></h3>';
        $texte .= '<table class="table table-condensed">';
        $texte .= '     <tr>';
        $texte .= '            <th>' . $view->translate("Libellé") . '</th>';
        $texte .= '            <th>' . $view->translate("Effectifs") . '</th>';
        $texte .= '     </tr>';
        foreach ($roles as $role) {
            /** @var Role $role */
            $texte .= '     <tr>';
            $texte .= '     <td>' . $role->getLibelle() . '</td>';
            $texte .= '     <td>' . count($effectifs[$role->getLibelle()]) . '</td>';
            $texte .= '     </tr>';
        }
        $texte .= '</table>';

        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @param Role[] $roles
     * @param array(string =>IndividuRole[]) $effectifs
     * @param PhpRenderer $view
     * @return string
     */
    function generateMembreSection($structure, $roles, $effectifs, $view)
    {
        $canModifierMembre = EcoleDoctoralePrivileges::getResourceId(EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION);

        $membres = [];
        foreach ($effectifs as $effectif) {
            foreach ($effectif as $individuRole) {
                /** @var IndividuRole $individuRole */
                $membres[] = $individuRole->getIndividu();
            }
        }
        $membres = array_unique($membres);

        $texte = '';
        $texte .= '<h3> Membres <span class="badge">' . count($membres) . '</span></h3>';
        $texte .= '<table class="table table-condensed">';
        $texte .= '     <tr>';
        $texte .= '            <th>' . $view->translate("Utilisateur") . '</th>';
        $texte .= '            <th>' . $view->translate("Rôle") . '</th>';
        $texte .= '            <th>' . $view->translate("Actions") . '</th>';
        $texte .= '     </tr>';

        foreach ($roles as $role) {
            /** @var Role $role */
            foreach ($effectifs[$role->getLibelle()] as $individuRole) {
                /** @var IndividuRole $individuRole */
                $texte .= '     <tr>';
                $texte .= '             <td>' . $individuRole->getIndividu()->getNomComplet(false, false, false) . '</td>';
                $texte .= '             <td>' . $role->getLibelle() . '</td>';
                $texte .= '             <td>';
                if ($canModifierMembre) {
                    $urlRetirer = $view->url('etablissement/retirer-individu', ["etablissement" => $structure->getId(), 'etabi' => $individuRole->getId(),], [], true);
                    $texte .= '                <a href="' . $urlRetirer . '">';
                    $texte .= '                <span class="glyphicon glyphicon-trash"></span>';
                    $texte .= '                </a>';
                }
                $texte .= '            </td>';
                $texte .= '     </tr>';

            }
        }
        $texte .= '</table>';
        return $texte;
    }

    /**
     * @param StructureConcreteInterface $structure
     * @param Role[] $roles
     * @param PhpRenderer $view
     * @return string
     */
    function generateAjoutMembre($structure, $roles, $view)
    {
        $canModifierMembre = EcoleDoctoralePrivileges::getResourceId(EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION);

        $texte = '';
        if ($canModifierMembre) {
            $texte .= '<div class="row">';
            $texte .= '<div class="col-md-6">';
            $urlAjouter = $view->url('etablissement/ajouter-individu', ['etablissement' => $structure->getId()], [], true);
            $texte .= '<form method="post" action="' . $urlAjouter . '">';


            $sas = new SearchAndSelect('individu');
            $sas->setLabel($view->translate("Recherche de l'individu à ajouter :"));
            $sas->setAttribute('class', 'individu-finder');
            $sas->setAutocompleteSource($view->url('utilisateur/rechercher-individu', [], [], true));
            $texte .= $view->formcontrolgroup($sas, 'formSearchAndSelect');
            $texte .= $this->generateSelect("role", $roles);
            $texte .= '<br/>';
            $texte .= '<input type="submit" value="' . $view->translate("Ajouter cet individu") . '" style="margin-top: 0;" title="' . $view->translate("Ajouter cet individu comme membre") . '" class="btn btn-default btn-sm" />';
            $texte .= '</form>';
            $texte .= '</div>';
            $texte .= '</div>';
        }
        return $texte;
    }

    function generateSelect($id, $roles)
    {
        $texte = '';
        $texte .= '<select name="' . $id . '" class="form-control" >';
        /** @var \Application\Entity\Db\Role $role */
        foreach ($roles as $role) {
            $texte .= '<option value="' . $role->getId() . '">' . $role->getLibelle() . '</option>';
        }
        $texte .= '</select>';
        return $texte;
    }
}
