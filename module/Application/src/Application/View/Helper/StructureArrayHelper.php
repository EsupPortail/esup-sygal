<?php

namespace Application\View\Helper;

use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Provider\Privilege\EtablissementPrivileges;
use Application\Provider\Privilege\UniteRecherchePrivileges;
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
        //TODO adapter au type de structure concrete
        $canModifier = $view->isAllowed(EtablissementPrivileges::getResourceId(EtablissementPrivileges::ETABLISSEMENT_MODIFICATION));

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
        if ($canModifier) $texte .= $this->generateAjoutMembre($structure, $roles, $view);
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
        $id = $structure->getId();
//        $libelle = $structure->getStructure()->getLibelle();
        $query = ['selected' => $id];
        $texte = '';
        $texte .= '         <td>';
        $texte .= '             <a name="' . $id . '"></a>';
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
        $sigle = $structure->getStructure()->getSigle();

        $texte = '';
        $texte .= '         <td>';
        $texte .= $sigle;
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

        $hasSousStructure = !($structure->getStructure()->getStructuresSubstituees()->isEmpty());
//        $hasSousStructure = false;
//        var_dump($hasSousStructure);

        $prefix = '';
        $type = '';
        $privilege = null;

        $typeStructure = $structure->getStructure()->getTypeStructure();
        switch(true) {
            case $typeStructure->isEtablissement() :
                $prefix = 'etablissement';
                $type = 'etablissement';
                $privilege = EtablissementPrivileges::ETABLISSEMENT_MODIFICATION;
                break;
            case $typeStructure->isEcoleDoctorale() :
                $prefix = 'ecole-doctorale';
                $type = 'ecoleDoctorale';
                $privilege = EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION;
                break;
            case $typeStructure->isUniteRecherche() :
                $prefix = 'unite-recherche';
                $type = 'uniteRecherche';
                $privilege = UniteRecherchePrivileges::UNITE_RECH_MODIFICATION;
                break;
        }

        $canModifier = $view->isAllowed(EtablissementPrivileges::getResourceId($privilege));
        $canSupprimer = $view->isAllowed(EtablissementPrivileges::getResourceId($privilege));
        $canSubstituer = $view->isAllowed(EtablissementPrivileges::getResourceId($privilege));
        $urlModifier  = $view->url($prefix . '/modifier',  [$type => $structure->getId()], [], true);
        $urlSupprimer = $view->url($prefix . '/supprimer', [$type => $structure->getId()], [], true);
        $urlRestaurer = $view->url($prefix . '/restaurer', [$type => $structure->getId()], [], true);
        $urlSubstituer = $view->url('substitution-modifier', ['cible' => $structure->getStructure()->getId(), [], true]);

        $texte = '';
        $texte .= '         <td>';
        if ($structure->estNonHistorise()) {
            if ($canModifier)   $texte .= '             <a href="' . $urlModifier . '"><span class="glyphicon glyphicon-pencil" title="Éditer"></span></a>';
            if ($canSupprimer)  $texte .= '             <a href="' . $urlSupprimer . '"><span class="glyphicon glyphicon-trash" title="Supprimer"></span></a>';
//            if ($nombreSousStructure > 0) {
            if ($hasSousStructure) {
                if ($canSubstituer)  $texte .= '                 <a href="' . $urlSubstituer . '">';
//                $texte .= '<span class="badge">' .$nombreSousStructure. '</span>';
                $texte .= '<span class="glyphicon glyphicon-link" title="Éditer la substituion"></span>';
                if ($canSubstituer)  $texte .= '</a>';
            }
        } else {
            if ($canSupprimer)   $texte .= '             <a href="' . $urlRestaurer . '"> Restaurer </a>';
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
        $prefix = '';
        $type = '';
        $privilege = null;
        $people = '';
        switch(true) {
            case $structure->getStructure()->getTypeStructure()->isEtablissement() :
                $prefix = 'etablissement';
                $type = 'etablissement';
                $people = 'etabi';
                $privilege = EtablissementPrivileges::ETABLISSEMENT_MODIFICATION;
                break;
            case $structure->getStructure()->getTypeStructure()->isEcoleDoctorale() :
                $prefix = 'ecole-doctorale';
                $type = 'ecoleDoctorale';
                $people = 'edi';
                $privilege = EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION;
                break;
            case $structure->getStructure()->getTypeStructure()->isUniteRecherche() :
                $prefix = 'unite-recherche';
                $type = 'uniteRecherche';
                $people = 'edi';
                $privilege = UniteRecherchePrivileges::UNITE_RECH_MODIFICATION;
                break;
        }

        $canModifier = $view->isAllowed(EtablissementPrivileges::getResourceId($privilege));

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
                if ($canModifier) {
                    $urlRetirer = $view->url($prefix . '/retirer-individu', [$type => $structure->getId(), $people => $individuRole->getId(),$people], [], true);
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
        $prefix = '';
        $type = '';
        $privilege = null;
        switch(true) {
            case $structure->getStructure()->getTypeStructure()->isEtablissement() :
                $prefix = 'etablissement';
                $type = 'etablissement';
                $privilege = EtablissementPrivileges::ETABLISSEMENT_MODIFICATION;
                break;
            case $structure->getStructure()->getTypeStructure()->isEcoleDoctorale() :
                $prefix = 'ecole-doctorale';
                $type = 'ecoleDoctorale';
                $privilege = EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION;
                break;
            case $structure->getStructure()->getTypeStructure()->isUniteRecherche() :
                $prefix = 'unite-recherche';
                $type = 'uniteRecherche';
                $privilege = UniteRecherchePrivileges::UNITE_RECH_MODIFICATION;
                break;
        }

        $texte = '';
        $texte .= '<div class="row">';
        $texte .= '<div class="col-md-6">';
        $urlAjouter = $view->url($prefix . '/ajouter-individu', [$type => $structure->getId()], [], true);
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
