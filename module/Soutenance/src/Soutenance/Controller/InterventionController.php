<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Intervention;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Parametre;
use Soutenance\Service\Intervention\InterventionServiceAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

class InterventionController extends AbstractController {
    use EntityManagerAwareTrait;
    use FichierTheseServiceAwareTrait;
    use InterventionServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;

    public function indexAction()
    {
        return new ViewModel([]);
    }

    public function afficherAction() : ViewModel
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($proposition, true);
        $distanciels = $this->getInterventionService()->getInterventionByTheseAndType($these, Intervention::TYPE_DISTANCIEL);
        $visios = $this->getInterventionService()->getInterventionByTheseAndType($these, Intervention::TYPE_VISIO_TARDIVE);
        $membres = [];
        foreach($proposition->getMembres() as $membre) {
            $membres[$membre->getId()] = $membre;
        }

        return new ViewModel([
            'these' => $these,
            'distanciel' => (!empty($distanciels))?current($distanciels):null,
            'visios' => (!empty($visios))?$visios:null,
            'proposition' => $proposition,
            'membres' => $membres,
            'justificatifs' => $justificatifs,
            'urlFichierThese' => $this->urlFichierThese(),
            'FORMULAIRE_DELEGUATION' => $this->getParametreService()->getParametreByCode(Parametre::CODE_FORMULAIRE_DELEGUATION)->getValeur(),
        ]);
    }

    public function togglePresidentDistancielAction()
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $interventions = $this->getInterventionService()->getInterventionByTheseAndType($these, Intervention::TYPE_DISTANCIEL);
        $nbInterventions = count($interventions);

        switch ($nbInterventions) {
            case 0: //creation d'une intervention
                $intervention = new Intervention();
                $intervention->setThese($these);
                $intervention->setType(Intervention::TYPE_DISTANCIEL);
                $this->getInterventionService()->create($intervention);
                break;
            case 1: //historisation
                $intervention = current($interventions);
                $this->getInterventionService()->historiser($intervention);
                break;
            default: //erreur
                throw new RuntimeException("Plusieurs Intervention de type '".Intervention::TYPE_DISTANCIEL." pour la thèse '".$these->getId()."'.");
        }

        return $this->redirect()->toRoute('soutenance/intervention/afficher', ['these' => $these->getId()], [], true);
    }

    public function ajouterVisioconferenceTardiveAction() : ViewModel
    {
        $these = $this->getTheseService()->getRequestedThese($this);
        $proposition = $this->getPropositionService()->findOneForThese($these);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = $request->getFiles();
            $membre = $this->getMembreService()->find($data['membre']);

            if ($membre !== null and ($files !== null AND !empty($files))) {

                // 1 - Justificatif
                $files = ['files' => $files->toArray()];
                $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_DELEGUATION_SIGNATURE);
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($these, $files, $nature, $version);
                $justificatif = new Justificatif();
                $justificatif->setProposition($proposition);
                $justificatif->setFichier($fichiers[0]);
                $justificatif->setMembre($membre);
                $this->getJustificatifService()->create($justificatif);

                // 2 - Modification du membre
                $membre->setVisio(true);
                $this->getMembreService()->update($membre);

                // 3- Déclaration de l'intervention
                $intervention = new Intervention();
                $intervention->setType(Intervention::TYPE_VISIO_TARDIVE);
                $intervention->setThese($these);
                $intervention->setComplement($data['membre']);
                $this->getInterventionService()->create($intervention);
            }
            exit();
        }

        $membres = $proposition->getMembres()->toArray();
        $membres = array_filter($membres, function(Membre $membre) { return $membre->isVisio() === false; });

        return new ViewModel([
            'title' => "Ajout d'une déclaration de visioconférence tardive pour un membre du jury",
            'membres' => $membres,
            'these' => $these,
        ]);
    }

    public function supprimerVisioconferenceTardiveAction() : Response
    {
        $intervention = $this->getInterventionService()->getRequestedIntervention($this);
        $these = $intervention->getThese();
        $proposition = $this->getPropositionService()->findOneForThese($these);

        /** retrait de la déclaration de viso sur le membre et du justificatif */
        $membre = $this->getMembreService()->find($intervention->getComplement());
        if ($membre !== null) {
            $membre->setVisio(false);
            $this->getMembreService()->update($membre);

            $justificatif = $proposition->getJustificatif(NatureFichier::CODE_DELEGUATION_SIGNATURE, $membre);
            if ($justificatif !== null) {
                $this->getJustificatifService()->historise($justificatif);
            }
        }

        $this->getInterventionService()->delete($intervention);

        return $this->redirect()->toRoute('soutenance/intervention/afficher', ['these' => $these->getId()], [], true);
    }
}