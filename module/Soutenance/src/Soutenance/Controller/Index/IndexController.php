<?php

namespace Soutenance\Controller\Index;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractController {
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;

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
                $this->redirect()->toRoute('soutenance/index-acteur', [], [], true);
                break;
            case Role::CODE_RAPPORTEUR_JURY :
            case Role::CODE_RAPPORTEUR_ABSENT :
                $this->redirect()->toRoute('soutenance/index-rapporteur', [], [], true);
                break;
            case Role::CODE_ADMIN_TECH :
            case Role::CODE_OBSERVATEUR :
            case Role::CODE_BDD :
            case Role::CODE_UR :
            case Role::CODE_ED :
                $this->redirect()->toRoute('soutenance/index-structure', [], [], true);
                break;
        }
        return new ViewModel();
    }

    public function indexActeurAction()
    {
        $individu = $this->userContextService->getIdentityIndividu();
        $role = $this->userContextService->getSelectedIdentityRole();

        $theses = null;
        switch ($role->getCode()) {
            case Role::CODE_DOCTORANT :
                $theses = $this->getTheseService()->getRepository()->fetchThesesByDoctorantAsIndividu($individu);
                break;
            case Role::CODE_DIRECTEUR_THESE :
            case Role::CODE_CODIRECTEUR_THESE :
                $theses = $this->getTheseService()->getRepository()->fetchThesesByEncadrant($individu);
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
            foreach($membres as $membre_) {
                if ($membre_->getActeur() && $membre_->getActeur()->getIndividu() === $individu) {
                    $membre = $membre_;
                }
            }

            $engagement = $this->getEngagementImpartialiteService()->getEngagementImpartialiteByMembre($membre);
            $avis = $this->getAvisService()->getAvisByMembre($membre);

            return new ViewModel([
                'these' => $these,
                'membre' => $membre,
                'proposition' => $membre->getProposition(),
                'depot' => $these->hasVersionInitiale(),
                'engagement' => $engagement,
                'avis' => $avis,
                'telecharger' => ($avis)?$this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()):null,
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

        return new ViewModel([
            'propositions' => $propositions,
            'role' => $role,
        ]);
    }
}