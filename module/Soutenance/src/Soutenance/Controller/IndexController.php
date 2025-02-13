<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Laminas\View\Model\ViewModel;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;

class IndexController extends AbstractSoutenanceController
{
    use AvisServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use UserContextServiceAwareTrait;

    use EtablissementServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * Cette action a pour but de dispatcher vers l'index correspondant au rôle sélectionné
     */
    public function indexAction()
    {
        $this->initializeFromType(false, false, false);
        $role = $this->userContextService->getSelectedIdentityRole();

        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
            case Role::CODE_HDR_CANDIDAT :
            case Role::CODE_HDR_GARANT :
                $this->redirect()->toRoute('soutenances/index-acteur', [], [], true);
                break;
            case Role::CODE_RAPPORTEUR_JURY :
            case Role::CODE_RAPPORTEUR_ABSENT :
                $this->redirect()->toRoute('soutenances/index-rapporteur', [], [], true);
                break;
            case Role::CODE_ADMIN_TECH :
            case Role::CODE_OBSERVATEUR :
            case Role::CODE_BDD :
            case Role::CODE_RESP_UR :
            case Role::CODE_RESP_ED :
            case Role::CODE_GEST_ED :
            case Role::CODE_GEST_HDR :
                if($this->entity instanceof These){
                    $this->redirect()->toRoute('soutenances/these/index-structure', [], [], true);
                }else{
                    $this->redirect()->toRoute('soutenances/hdr/index-structure', [], [], true);
                }
                break;
        }
        return new ViewModel();
    }

    public function indexActeurAction()
    {
        $this->initializeFromType(false, true);
        /** @var Role $role */
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();

        $theses = null;
        $hdrs = null;
        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $theses = $this->entityService->getRepository()->findThesesByDoctorantAsIndividu($individu);
                break;
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $theses = $this->entityService->getRepository()->findThesesByActeur($individu, $role);
                break;
            case Role::CODE_HDR_CANDIDAT :
                $hdrs = $this->entityService->getRepository()->findHDRByCandidatAsIndividu($individu);
                break;
            case Role::CODE_HDR_GARANT :
                $hdrs = $this->entityService->getRepository()->findHDRByActeur($individu, $role);
                break;
        }

        $vm = new ViewModel();
        if($this->entity instanceof These){
            $vm->setTemplate("soutenance/index/these/index-acteur");
            $vm->setVariables([
                "theses" => $theses,
                'comue' => $this->etablissementService->fetchEtablissementComue(),
            ]);
        }else{
            $vm->setTemplate("soutenance/index/hdr/index-acteur");
            $vm->setVariable([
                "hdrs" => $hdrs,
            ]);
        }
        return $vm;
    }

    public function indexRapporteurAction()
    {
        $this->initializeFromType(false, true);
        $individu = $this->userContextService->getIdentityIndividu();

        if ($this->entity !== null) {
            /** @var Membre[] $membres */
            $membres = $this->proposition->getMembres()->toArray();
            $membre = null;
            foreach ($membres as $membre_) {
                $acteur = $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre_);
//                if ($membre_->getActeur() && $membre_->getActeur()->getIndividu() === $individu) {
                if ($acteur && $acteur->getIndividu() === $individu) {
                    $membre = $membre_;
                }
            }


            $acteurMembre = $membre ? $this->acteurService->getRepository()->findActeurForSoutenanceMembre($membre) : null;
            $engagement = ($membre)?$this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($this->entity, $membre):null;
            $avis = ($membre)?$this->getAvisService()->getAvisByMembre($membre):null;

            $vm = new ViewModel();
            if($this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE){
                $vm->setTemplate("soutenance/index/these/index-rapporteur");
                $vm->setVariables([
                    'these' => $this->entity,
                    'typeProposition' => $this->type,
                    'membre' => $membre,
                    'acteurMembre' => $acteurMembre,
                    'proposition' => $this->proposition,
                    'depot' => $this->entity->hasVersionInitiale(),
                    'engagement' => $engagement,
                    'avis' => $avis,
                    'telecharger' => ($avis) ? $this->urlFichierThese()->telechargerFichierThese($this->entity, $avis->getFichier()) : null,
                ]);
            }else{
                $vm->setTemplate("soutenance/index/hdr/index-rapporteur");
                $vm->setVariables([
                    'hdr' => $this->entity,
                    'typeProposition' => $this->type,
                    'membre' => $membre,
                    'acteurMembre' => $acteurMembre,
                    'proposition' => $this->proposition,
                    'depot' => $this->entity->hasVersionInitiale(),
                    'engagement' => $engagement,
                    'avis' => $avis,
                    'telecharger' => ($avis) ? $this->urlFichierHDR()->telechargerFichierHDR($this->entity, $avis->getFichier()) : null,
                ]);
            }
            return $vm;
        } else {
            //rapporteur jamais redirigé ici actuellement
            if($this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE){
                $acteurs = $this->acteurService->getRapporteurDansTheseEnCours($individu);
                $theses = [];
                foreach ($acteurs as $acteur) $theses[] = $acteur->getThese();

                if (count($theses) == 1) {
                    $this->entity = current($theses);
                    return $this->redirect()->toRoute("soutenance_{$this->type}/index-rapporteur", ['id' => $this->entity->getId()], [], true);
                } else {
                    $vm = new ViewModel();
                    $vm->setTemplate("soutenance/index/these/index-rapporteur");
                    $vm->setVariable("theses", $theses);
                    return $vm;
                }
            }else{
                $acteurs = $this->acteurService->getRapporteurDansHDREnCours($individu);
                $hdrs = [];
                foreach ($acteurs as $acteur) $hdrs[] = $acteur->getHDR();

                if (count($hdrs) == 1) {
                    $this->entity = current($hdrs);
                    return $this->redirect()->toRoute("soutenance_{$this->type}/index-rapporteur", ['id' => $this->entity->getId()], [], true);
                } else {
                    $vm = new ViewModel();
                    $vm->setTemplate("soutenance/index/hdr/index-rapporteur");
                    $vm->setVariable("hdrs", $hdrs);
                    return $vm;
                }
            }
        }
    }

    public function indexStructureAction()
    {
        $this->initializeFromType(false);
        $role = $this->userContextService->getSelectedIdentityRole();
        $propositions = $this->propositionService->findPropositionsByRole($role);

        $etablissementId = $this->params()->fromQuery('etablissement');
        $ecoleDoctoraleId = $this->params()->fromQuery('ecoledoctorale');
        $uniteRechercheId = $this->params()->fromQuery('uniterecherche');
        $etatId = $this->params()->fromQuery('etat');

        if ($etablissementId != '') $propositions = array_filter($propositions, function($proposition) use ($etablissementId) { return $proposition->getThese()->getEtablissement()->getStructure()->getSourceCode() == $etablissementId; });
        if ($ecoleDoctoraleId != '') $propositions = array_filter($propositions, function($proposition) use ($ecoleDoctoraleId) { return $proposition->getThese()->getEcoleDoctorale()->getId() == $ecoleDoctoraleId; });
        if ($uniteRechercheId != '') $propositions = array_filter($propositions, function($proposition) use ($uniteRechercheId) { return $proposition->getThese()->getUniteRecherche()->getId() == $uniteRechercheId; });
        if ($etatId != '') $propositions = array_filter($propositions, function($proposition) use ($etatId) { return $proposition->getEtat()->getId() == $etatId; });

        $propositions = array_filter($propositions, function ($proposition) { return $proposition->getDate(); });

        return new ViewModel([
            'propositions' => $propositions,
            'role' => $role,

            'etablissementId' => $etablissementId,
            'ecoleDoctoraleId' => $ecoleDoctoraleId,
            'uniteRechercheId' => $uniteRechercheId,
            'etatId' => $etatId,
            'etablissements' => $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions(),
            'ecoles' => $this->getEcoleDoctoraleService()->getRepository()->findAll(),
            'unites' => $this->getUniteRechercheService()->getRepository()->findAll(),
            'etats' =>  $this->propositionService->findPropositionEtats(),
        ]);
    }
}