<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;

    use EtablissementServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * Cette action a pour but de dispatcher vers l'index correspondant au rôle sélectionné
     */
    public function indexAction()
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
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
                $this->redirect()->toRoute('soutenances/index-structure', [], [], true);
                break;
        }
        return new ViewModel();
    }

    public function indexActeurAction()
    {
        /** @var Role $role */
        $role = $this->userContextService->getSelectedIdentityRole();
        $individu = $this->userContextService->getIdentityIndividu();

        $theses = null;
        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $theses = $this->getTheseService()->getRepository()->findThesesByDoctorantAsIndividu($individu);
                break;
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $theses = $this->getTheseService()->getRepository()->findThesesByActeur($individu, $role, [These::ETAT_EN_COURS]);
                break;
        }

        return new ViewModel([
            'theses' => $theses,
        ]);
    }

    public function indexRapporteurAction()
    {
        $individu = $this->userContextService->getIdentityIndividu();
        $these = $this->requestedThese();

        if ($these !== null) {
            /** @var These $these */
            $proposition = $this->getPropositionService()->findByThese($these);
            /** @var Membre[] $membres */
            $membres = $proposition->getMembres()->toArray();
            $membre = null;
            foreach ($membres as $membre_) {
                if ($membre_->getActeur() && $membre_->getActeur()->getIndividu() === $individu) {
                    $membre = $membre_;
                }
            }

            $engagement = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($these, $membre);
            $avis = $this->getAvisService()->getAvisByMembre($membre);

            return new ViewModel([
                'these' => $these,
                'membre' => $membre,
                'proposition' => $membre->getProposition(),
                'depot' => $these->hasVersionInitiale(),
                'engagement' => $engagement,
                'avis' => $avis,
                'telecharger' => ($avis) ? $this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()) : null,
            ]);
        } else {
            $acteurs = $this->getActeurService()->getRapporteurDansTheseEnCours($individu);
            $theses = [];
            foreach ($acteurs as $acteur) $theses[] = $acteur->getThese();

            if (count($theses) == 1) {
                $these = current($theses);
                return $this->redirect()->toRoute('soutenance/index-rapporteur', ['these' => $these->getId()], [], true);
            } else {
                return new ViewModel([
                    'theses' => $theses,
                ]);
            }
        }
    }

    public function indexStructureAction()
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        $propositions = $this->getPropositionService()->getPropositionsByRole($role);


        $etablissementId = $this->params()->fromQuery('etablissement');
        $ecoleDoctoraleId = $this->params()->fromQuery('ecoledoctorale');
        $uniteRechercheId = $this->params()->fromQuery('uniterecherche');
        $etatId = $this->params()->fromQuery('etat');

        if ($etablissementId != '') $propositions = array_filter($propositions, function($proposition) use ($etablissementId) { return $proposition->getThese()->getEtablissement()->getId() == $etablissementId; });
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
            'etablissements' => $this->getEtablissementService()->getRepository()->findAllEtablissementsMembres(),
            'ecoles' => $this->getEcoleDoctoraleService()->getRepository()->findAll(true),
            'unites' => $this->getUniteRechercheService()->getRepository()->findAll(true),
            'etats' =>  $this->getPropositionService()->getPropositionEtats(),
        ]);
    }
}