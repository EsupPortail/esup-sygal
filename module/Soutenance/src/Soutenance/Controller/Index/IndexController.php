<?php

namespace Soutenance\Controller\Index;

use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use EngagementImpartialiteServiceAwareTrait;
    use MembreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /** Cette action à pour but de dispatcher vers l'index correspondant au rôle sélectionné */
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
        };

        return new ViewModel([
            'theses' => $theses,
        ]);
    }

    public function indexRapporteurAction()
    {
        $individu = $this->userContextService->getIdentityIndividu();

        $theseId = $this->params()->fromRoute('these');
        if ($theseId !== null) {

            /** @var These $these */
            $these = $this->getTheseService()->getRepository()->find($theseId);
            $proposition = $this->getPropositionService()->findByThese($these);
            /** @var Membre[] $membres */
            $membres = $proposition->getMembres()->toArray();
            $membre = null;
            $rappoteur = null;
            foreach($membres as $membre_) {
                if ($membre_->getActeur()->getIndividu() === $individu) {
                    $membre = $membre_;
                    $rapporteur = $membre;
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
                'urlFichierThese' => $this->urlFichierThese(),
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
        ]);
    }
}