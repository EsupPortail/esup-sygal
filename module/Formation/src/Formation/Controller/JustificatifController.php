<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\Justificatif;
use Formation\Entity\Db\Seance;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Justificatif\JustificatifServiceAwareTrait;
use Formation\Service\JustificatifSeance\JustificatifSeanceServiceAwareTrait;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use Formation\Service\Seance\SeanceServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class JustificatifController extends AbstractController
{
    use EntityManagerAwareTrait;
    use SeanceServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use PresenceServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use JustificatifSeanceServiceAwareTrait;
    use FichierStorageServiceAwareTrait;

    public function justifierAbsenceAction(): ViewModel
    {
        $inscription = $this->inscriptionService->getRepository()->getRequestedInscription($this);
        $session = $inscription->getSession();
        $doctorant = $inscription->getDoctorant();

        $seances = $session->getSeances()->toArray();
        $seances = array_filter($seances, function (Seance $a) { return $a->estNonHistorise();});

        $absences = null;
        /** @var Seance $seance */
        foreach ($seances as $seance) {
            $presence = $this->presenceService->getRepository()->findPresenceByInscriptionAndSeance($inscription, $seance);
            if ($presence === null || !$presence->isPresent()) {
                $justificatif = null;
                try {
                    $justificatif = $this->justificatifService->getRepository()->findJustificatifByInscriptionAndSeance($inscription, $seance);
                } catch (RuntimeException $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
                $absences[$seance->getId()] = ["seance" => $seance, "inscription" => $inscription, "justificatif" => $justificatif];
            }
        }

        return new ViewModel([
            "title" => is_array($absences) && count($absences) > 1 ?
                "Justifications des absences de $doctorant à la formation {$session->getFormation()->getLibelle()}" :
                "Justification de l'absence de $doctorant à la formation {$session->getFormation()->getLibelle()}",
            "session" => $session,
            'absences' => $absences,
            'doctorant' => $doctorant,
            'role' => $this->userContextService->getSelectedIdentityRole()
        ]);
    }

    public function ajouterJustificatifAbsenceSeanceAction(): ViewModel
    {
        $seance = $this->seanceService->getRepository()->getRequestedSeance($this);
        $inscription = $this->inscriptionService->getRepository()->getRequestedInscription($this);
        $doctorant = $inscription->getDoctorant();

        $seances = $inscription->getSession()->getSeances()->toArray();
        $seances = array_filter($seances, function (Seance $a) { return $a->estNonHistorise();});
        $jourSeance = $seance->getDebut()->format('Y-m-d');
        //détermination des séances (de la même session) qui possèdent le même jour
        $seancesMemeJour = array_filter($seances, function($s) use ($jourSeance, $seance) {
            return $s !== $seance && $s->getDebut()->format('Y-m-d') === $jourSeance;
        });

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = $request->getFiles();
            if ($files !== null and !empty($files)) {
                $files = ['files' => $files['justificatif']];
                /** @var NatureFichier $nature */
                $nature = $this->natureFichierService->getRepository()->findOneBy(['code' => \Formation\Provider\NatureFichier\NatureFichier::CODE_JUSTIFICATIF_ABSENCE]);
                $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
                $this->fichierService->saveFichiers($fichiers);

                $justificatif = new Justificatif();
                $justificatif->setInscription($inscription);
                $justificatif->setFichier(array_pop($fichiers));

                //si l'on veut ajouter un justificatif pour toutes les séances de la même journée
                if($data->get("justificatif_journee") === "on"){
                    $seances = $seancesMemeJour;

                    //si il y a déjà des justificatifs pour les séances qui ont lieu la même journée
                    //on supprime les justificatifs présents
                    foreach($seances as $seanceMemeJour){
                        $justificatifSeanceMemeJour = $this->justificatifService->getRepository()->findJustificatifByInscriptionAndSeance($inscription, $seanceMemeJour);
                        if($justificatifSeanceMemeJour) $this->justificatifService->delete($justificatifSeanceMemeJour);
                    }
                    $seances[] = $seance;
                }else{
                    $seances = [$seance];
                }

                $this->justificatifService->create($justificatif, $seances);
                $this->flashMessenger()->addSuccessMessage("Le justificatif a été ajouté avec succès.");
            }
        }

        return new ViewModel([
            'title' => "Ajout d'un justificatif pour l'absence du ".$seance->getDebut()->format('d/m/Y').
                " (".$seance->getDebut()->format('H:i')." à ".$seance->getFin()->format('H:i').")",
            'seance' => $seance,
            'doctorant' => $doctorant,
            'seanceMemeJour' => count($seancesMemeJour) > 0,
        ]);
    }

    public function supprimerJustificatifAbsenceSeanceAction(): Response
    {
        $justificatif = $this->justificatifService->getRepository()->getRequestedJustificatif($this);
        $seance = $this->seanceService->getRepository()->getRequestedSeance($this);

        if($seance && $justificatif){
            $justificatifSeance = $this->justificatifSeanceService->getRepository()->findOneByJustificatifAndSeance($justificatif, $seance);
            $justificatif->removeJustificatifSeance($justificatifSeance);
            $this->justificatifService->update($justificatif);

            $this->justificatifSeanceService->delete($justificatifSeance);

            $this->flashMessenger()->addSuccessMessage(
                "Le justificatif pour la séance du {$seance->getDebut()->format('d/m/Y')}
                ({$seance->getDebut()->format('H:i')} à {$seance->getFin()->format('H:i')}) a été supprimé avec succès."
            );
        }else{
            $this->flashMessenger()->addSuccessMessage("Le justificatif et/ou la séance n'a pas pu être récupéré(e).");
        }

        if($redirectUrl = $this->params()->fromQuery('redirect')){
            return $this->redirect()->toUrl($redirectUrl);
        }else{
            return $this->redirect()->toRoute('formation/session/justifier-absence', ["inscription" => $justificatif->getInscription()->getId()], [], true);
        }
    }

    public function telechargerJustificatifAbsenceSeanceAction(): void
    {
        $justificatif = $this->justificatifService->getRepository()->getRequestedJustificatif($this);
        $fichier = $justificatif->getFichier();

        if (!$fichier) {
            return;
        }

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        // Envoi du fichier au client (navigateur)
        $this->uploader()->download($fichier);
    }
}