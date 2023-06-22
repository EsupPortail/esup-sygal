<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Soutenance\Entity\Justificatif;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Proposition;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormAwareTrait;
use Soutenance\Service\Avis\AvisServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Exception\RuntimeException;

class AvisController extends AbstractController
{
    use ActeurServiceAwareTrait;
    use AvisServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use PropositionServiceAwareTrait;
    use TheseServiceAwareTrait;
    use ValidatationServiceAwareTrait;

    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;

    use AvisFormAwareTrait;

    public function indexAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findOneForThese($these);
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        if ($avis !== null) {
            $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
        }

        /** @var AvisForm $form */
        $form = $this->getAvisForm();

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if ($files['files']['rapport']['size'] === 0) {
                $this->flashMessenger()->addErrorMessage("Pas de prérapport de soutenance !");
                $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
            }
            if ($data['avis'] === "Défavorable" && trim($data['motif']) == '') {
                $this->flashMessenger()->addErrorMessage("Vous devez motivez votre avis défavorable en quelques mots.");
                $this->redirect()->toRoute('soutenance/avis-soutenance', ['these' => $these->getId(), 'rapporteur' => $membre->getId()]);
            }

            $form->setData($data);
            if ($form->isValid()) {

//                $fichier = $this->getAvisService()->createAvisFromUpload($files, $membre);


                $fichiers = [];
                if (!empty($files)) {

                    $file = new Fichier();
                    $nature = $this->fichierTheseService->fetchNatureFichier(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
                    $file->setNature($nature);
                    $nfiles['files'][0] =  $files['files']['rapport'] ;


                    $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                    $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($these, $nfiles , $nature, $version);
                }
//                var_dump($fichiers);
//                die();

                $validation = $this->getValidationService()->signerAvisSoutenance($these, $membre->getIndividu());
                $avis = new Avis();
                $avis->setProposition($proposition);
                $avis->setMembre($membre);
                $avis->setFichierThese($fichiers[0]);
                $avis->setValidation($validation);
                $avis->setAvis($data['avis']);
                $avis->setMotif($data['motif']);
                $this->getAvisService()->create($avis);

                /**
                 * N.B. :  Après un dépôt penser à vérifier :
                 *   - peu importe l'avis il faut notifier à chaque dépot d'un avis ;
                 *   - si tous les avis sont déposés penser à notifier le bureau des doctorats.
                 */
                $allAvis = $this->getAvisService()->getAvisByThese($these);
                $allRapporteurs = $this->getMembreService()->getRapporteursByProposition($proposition);

                if ($avis->getAvis() === Avis::FAVORABLE) {
                    try {
                        $notif = $this->soutenanceNotificationFactory->createNotificationAvisFavorable($these, $avis);
                        $this->notifierService->trigger($notif);
                    } catch (\Notification\Exception\RuntimeException $e) {
                        // aucun destinataire, todo : cas à gérer !
                    }
                }
                if ($avis->getAvis() === Avis::DEFAVORABLE) {
                    try {
                        $notif = $this->soutenanceNotificationFactory->createNotificationAvisDefavorable($these, $avis);
                        $this->notifierService->trigger($notif);
                    } catch (\Notification\Exception\RuntimeException $e) {
                        // aucun destinataire, todo : cas à gérer !
                    }
                }

                /** TODO ajouter un prédicat dans thèse ou soutenance ??? */
                if (count($allAvis) === count($allRapporteurs)) {
                    try {
                        $notif1 = $this->soutenanceNotificationFactory->createNotificationAvisRendus($these);
                        $this->notifierService->trigger($notif1);
                        $notif2 = $this->soutenanceNotificationFactory->createNotificationAvisRendusDirection($these);
                        $this->notifierService->trigger($notif2);
                    } catch (\Notification\Exception\RuntimeException $e) {
                        // aucun destinataire, todo : cas à gérer !
                    }
                }

                $this->redirect()->toRoute('soutenance/avis-soutenance/afficher', ['these' => $these->getId(), 'membre' => $membre->getId()], [], true);
            }
        }
        return new ViewModel([
            'form' => $form,
            'these' => $these,
            'rapporteur' => $membre->getActeur(),
        ]);
    }

    public function afficherAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        $rapporteur = $membre->getActeur();

        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);

        return new ViewModel([
            'these' => $these,
            'rapporteur' => $rapporteur,
            'membre' => $membre,
            'avis' => $avis,
            'url' => $this->urlFichierThese()->telechargerFichierThese($these, $avis->getFichier()),
        ]);
    }

    public function annulerAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        /** @var Avis $avis */
        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $this->getAvisService()->historiser($avis);

        $this->redirect()->toRoute('soutenance/index-rapporteur', ['these' => $these->getId()], [], true);
    }


    public function telechargerAction()
    {
        $these = $this->requestedThese();
        $membre = $this->getMembreService()->getRequestedMembre($this, 'rapporteur');
        $avis = $this->getAvisService()->getAvisByMembre($membre);
        $fichier = $avis->getFichier();

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        $this->uploader()->download($fichier);
    }
}