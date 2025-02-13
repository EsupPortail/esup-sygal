<?php

namespace Soutenance\Controller;

use Depot\Service\FichierHDR\FichierHDRServiceAwareTrait;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Soutenance\Entity\Intervention;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Parametre\These\SoutenanceParametres;
use Soutenance\Service\Intervention\InterventionServiceAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class InterventionController extends AbstractSoutenanceController
{
    use EntityManagerAwareTrait;
    use FichierTheseServiceAwareTrait;
    use FichierHDRServiceAwareTrait;
    use InterventionServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ParametreServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        return new ViewModel([]);
    }

    public function afficherAction(): ViewModel
    {
        $this->initializeFromType(false, false, false);

        $justificatifs = $this->getJustificatifService()->generateListeJustificatif($this->proposition, true);
        $distanciels = $this->getInterventionService()->getInterventionByPropositionType($this->proposition, Intervention::TYPE_DISTANCIEL);
        $visios = $this->getInterventionService()->getInterventionByPropositionType($this->proposition, Intervention::TYPE_VISIO_TARDIVE);

        $membres = [];
        foreach ($this->proposition->getMembres() as $membre) {
            $membres[$membre->getId()] = $membre;
        }

        return new ViewModel([
            'object' => $this->entity,
            'distanciel' => (!empty($distanciels)) ? current($distanciels) : null,
            'visios' => (!empty($visios)) ? $visios : null,
            'proposition' => $this->proposition,
            'membres' => $membres,
            'justificatifs' => $justificatifs,
            'urlFichier' => $this->type === Proposition::ROUTE_PARAM_PROPOSITION_THESE ? $this->urlFichierThese(): $this->urlFichierHDR(),
            'FORMULAIRE_DELEGUATION' => $this->getParametreService()->getValeurForParametre(SoutenanceParametres::CATEGORIE, SoutenanceParametres::DOC_DELEGATION_SIGNATURE),
            'typeProposition' => $this->type,
        ]);
    }

    public function togglePresidentDistancielAction() : Response
    {
        $this->initializeFromType(false, false, false);
        $interventions = $this->getInterventionService()->getInterventionByPropositionType($this->proposition, Intervention::TYPE_DISTANCIEL);
        $nbInterventions = count($interventions);

        switch ($nbInterventions) {
            case 0: //creation d'une intervention
                $intervention = new Intervention();
                $intervention->setProposition($this->proposition);
                $intervention->setType(Intervention::TYPE_DISTANCIEL);
                $this->getInterventionService()->create($intervention);
                break;
            case 1: //historisation
                $intervention = current($interventions);
                $this->getInterventionService()->historiser($intervention);
                break;
            default: //erreur
                throw new RuntimeException("Plusieurs Intervention de type '" . Intervention::TYPE_DISTANCIEL . " pour la thèse\HDR '" . $this->entity->getId() . "'.");
        }

        return $this->redirect()->toRoute("soutenance_{$this->type}/intervention/afficher", ['id' => $this->entity->getId()], [], true);
    }

    public function ajouterVisioconferenceTardiveAction(): ViewModel
    {
        $this->initializeFromType(false, false, false);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = $request->getFiles();
            $membre = $this->getMembreService()->find($data['membre']);

            if ($membre !== null and ($files !== null and !empty($files))) {

                // 1 - Justificatif
                $files = ['files' => $files->toArray()];
                $nature = $this->isThese()
                    ? $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_DELEGUATION_SIGNATURE)
                    : $this->fichierHDRService->fetchNatureFichier(NatureFichier::CODE_DELEGUATION_SIGNATURE);
                $version = $this->isThese()
                    ? $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG)
                : $this->fichierHDRService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->isThese()
                    ? $this->fichierTheseService->createFichierThesesFromUpload($this->entity, $files, $nature, $version)
                : $this->fichierHDRService->createFichierHDRsFromUpload($this->entity, $files, $nature, $version);
                $justificatif = new Justificatif();
                $justificatif->setProposition($this->proposition);
                $justificatif->setFichier($fichiers[0]);
                $justificatif->setMembre($membre);
                $this->getJustificatifService()->create($justificatif);

                // 2 - Modification du membre
                $membre->setVisio(true);
                $this->getMembreService()->update($membre);

                // 3- Déclaration de l'intervention
                $intervention = new Intervention();
                $intervention->setType(Intervention::TYPE_VISIO_TARDIVE);
                $intervention->setProposition($this->proposition);
                $intervention->setComplement($data['membre']);
                $this->getInterventionService()->create($intervention);
            }
            exit();
        }

        $membres = $this->proposition->getMembres()->toArray();
        $membres = array_filter($membres, function (Membre $membre) {
            return $membre->isVisio() === false;
        });

        return new ViewModel([
            'title' => "Ajout d'une déclaration de visioconférence tardive pour un membre du jury",
            'membres' => $membres,
            'object' => $this->entity,
            'typeProposition' => $this->type,
        ]);
    }

    public function supprimerVisioconferenceTardiveAction(): Response
    {
        $this->initializeFromType(false, false, false);
        $intervention = $this->getInterventionService()->getRequestedIntervention($this);

        /** retrait de la déclaration de viso sur le membre et du justificatif */
        $membre = $this->getMembreService()->find($intervention->getComplement());
        if ($membre !== null) {
            $membre->setVisio(false);
            $this->getMembreService()->update($membre);

            $justificatif = $this->proposition->getJustificatif(NatureFichier::CODE_DELEGUATION_SIGNATURE, $membre);
            if ($justificatif !== null) {
                $this->getJustificatifService()->historise($justificatif);
            }
        }

        $this->getInterventionService()->delete($intervention);

        return $this->redirect()->toRoute("soutenance_{$this->type}/intervention/afficher", ['id' => $this->entity->getId()], [], true);
    }
}