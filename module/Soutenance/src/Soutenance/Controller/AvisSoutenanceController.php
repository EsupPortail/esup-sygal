<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\VersionFichier;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Form\Element\Hidden;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class AvisSoutenanceController extends AbstractController {
    use TheseServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use FichierServiceAwareTrait;

    public function indexAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);
        /** @var Acteur $rapporteur */
        $idRapporteur = $this->params()->fromRoute('rapporteur');
        $rapporteur = $this->getActeurService()->getRepository()->findActeurByIndividu($idRapporteur);

        $validation = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_AVIS_SOUTENANCE, $these));



        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            var_dump($data['avis']);
        }

        $view = $this->createViewForFichierAction(NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE);
        $view->setVariable('isVisible', true);
        $view->setVariable('maxUploadableFilesCount', 3);
        $view->setVariable('these', $these);
        $view->setVariable('rapporteur', $rapporteur);
        $view->setVariable('validation', $validation);
//        $view->setTemplate('application/these/depot/fichier-divers');
        return $view;

    }

    /**
     * @param string $codeNatureFichier
     * @return ViewModel
     */
    private function createViewForFichierAction($codeNatureFichier)
    {
        $these = $this->requestedThese();
        $nature = $this->fichierService->fetchNatureFichier($codeNatureFichier);
        $version = $this->fichierService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        if (!$nature) {
            throw new RuntimeException("Nature de fichier introuvable: " . $codeNatureFichier);
        }

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
//        $form->setUploadMaxFilesize('50M');
        $form->addElement((new Hidden('nature'))->setValue($nature->getCode()));
        $form->addElement((new Hidden('version'))->setValue($version->getCode()));
        $form->get('files')->setLabel("")->setAttribute('multiple', false)/*->setAttribute('accept', '.pdf')*/;

        $view = new ViewModel([
            'these'           => $these,
            'uploadUrl'       => $this->urlFichierThese()->televerserFichierThese($these),
            'fichiersListUrl' => $this->urlFichierThese()->listerFichiers($these, $nature),
            'nature'          => $nature,
            'version'         => $version,
        ]);

        return $view;
    }
}