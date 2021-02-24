<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\VersionFichier;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Parametre;
use Soutenance\Form\Justificatif\JustificatifFormAwareTrait;
use Soutenance\Service\Justificatif\JustificatifServiceAwareTrait;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Parametre\ParametreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class JustificatifController extends AbstractController {
    use FichierTheseServiceAwareTrait;
    use JustificatifServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use PropositionServiceAwareTrait;

    use JustificatifFormAwareTrait;

    public function ajouterAction()
    {
        $proposition = $this->getPropositionService()->getRequestedProposition($this);
        $nature = $this->params()->fromRoute('nature');
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $justificatif = new Justificatif();
        $justificatif->setMembre($membre);
        $justificatif->setProposition($proposition);
        $form = $this->getJustificatifForm();
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/justificatif/ajouter', ['proposition' => $proposition->getId(), 'nature' => $nature, 'membre' => $membre->getId()], [], true));
        $form->bind($justificatif);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $files = ['files' => $request->getFiles()->toArray()];

            if (!empty($files)) {
                $nature = $this->fichierTheseService->fetchNatureFichier($nature);
                $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);
                $fichiers = $this->fichierTheseService->createFichierThesesFromUpload($proposition->getThese(), $files, $nature, $version);
                $justificatif->setFichier($fichiers[0]);
                $this->getJustificatifService()->create($justificatif);
            }
        }

        return new ViewModel([
            'title' => "Ajout d'un justificatif pour " . $membre->getDenomination(),
            'form' => $form,
        ]);
    }

    public function retirerAction()
    {
        $justificatif = $this->getJustificatifService()->getRequestedJustificatif($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getJustificatifService()->historise($justificatif);

        return $this->redirect()->toUrl($retour);
    }
}