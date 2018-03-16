<?php

namespace Application\Controller;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Filter\NomFichierFormatter;
use Application\RouteMatch;
use Application\Service\Fichier\Exception\DepotImpossibleException;
use Application\Service\Fichier\Exception\ValidationImpossibleException;
use Application\Service\Fichier\FichierServiceAwareInterface;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareInterface;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\These\TheseServiceAwareInterface;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareInterface;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Form\Element\Hidden;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class FichierTheseController extends AbstractController implements
    TheseServiceAwareInterface, FichierServiceAwareInterface, VersionFichierServiceAwareInterface,
    NotificationServiceAwareInterface
{
    use TheseServiceAwareTrait;
    use FichierServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotificationServiceAwareTrait;

    const UPLOAD_MAX_FILESIZE = '500M';

    public function deposesAction()
    {
        /**
         * Application des filtres et tris par défaut.
         */
        $needsRedirect = false;
        $queryParams = $this->params()->fromQuery();
//        // filtres
//        $etatThese = $this->params()->fromQuery($name = 'etatThese');
//        if ($etatThese === null) { // null <=> paramètre absent
//            // filtrage par défaut : thèse en préparation
//            $queryParams = array_merge($queryParams, [$name => These::ETAT_EN_COURS]);
//            $needsRedirect = true;
//        }
        // tris
        $sort = $this->params()->fromQuery('sort');
        if ($sort === null) { // null <=> paramètre absent
            // tri par défaut : datePremiereInscription
            $queryParams = array_merge($queryParams, ['sort' => 'f.nom', 'direction' => Sortable::ASC]);
            $needsRedirect = true;
        }
        // redirection si nécessaire
        if ($needsRedirect) {
            return $this->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);
        $maxi = $this->params()->fromQuery('maxi', 40);
        $page = $this->params()->fromQuery('page', 1);

        $qb = $this->fichierService->getRepository()->createQueryBuilder('f');
        $qb
            ->addSelect('t, d, val, ver')
            ->join('f.these', 't')
            ->join('t.doctorant', 'd')
            ->leftJoin('f.validites', 'val')
            ->join('f.version', 'ver');

        foreach (explode('+', $sort) as $sortProp) {
            if ($sortProp === 't.titre') {
                // trim et suppression des guillemets
                $sortProp = "TRIM(REPLACE($sortProp, CHR(34), ''))"; // CHR(34) <=> "
            }
            $qb->addOrderBy($sortProp, $dir);
        }

        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(20)
            ->setItemCountPerPage((int)$maxi)
            ->setCurrentPageNumber((int)$page);

        $vm = new ViewModel([
            'fichiers' => $paginator,
            'urlFichierThese' => $this->urlFichierThese(),
        ]);

        return $vm;
    }

    /**
     * Listage des fichiers (thèse ou annexes) déposés.
     *
     * @return ViewModel
     */
    public function listerAction()
    {
        $these = $this->requestedThese();
        $estAnnexe   = $this->params()->fromQuery('annexe',  false);
//        $estExpurge  = $this->params()->fromQuery('expurge', false);
        $estRetraite = $this->params()->fromQuery('retraite', false);
        $inclureValidite = (bool)$this->params()->fromQuery('inclureValidite', false);
        $inclureRetraitement = (bool)$this->params()->fromQuery('inclureRetraitement', false);

        $version = $this->params()->fromQuery('version');

//      $fichiers = $these->getFichiersBy($estAnnexe, $estExpurge, $estRetraite, $version);
        $nature = $estAnnexe ? NatureFichier::CODE_FICHIER_NON_PDF : NatureFichier::CODE_THESE_PDF;
        $fichiers = $this->fichierService->getRepository()->fetchFichiers($these, $nature , $version , $estRetraite);

        $items = array_map(function (Fichier $fichier) use ($these) {
            return [
                'file'          => $fichier,
                'downloadUrl'   => $this->urlFichierThese()->telechargerFichierThese($these, $fichier),
                'apercevoirUrl' => $this->urlFichierThese()->apercevoirFichierThese($these, $fichier),
                'deleteUrl'     => $this->urlFichierThese()->supprimerFichierThese($these, $fichier),
            ];
        }, $fichiers);

        $viewModel = new ViewModel([
            'items' => $items,
            'inclureValidite' => $inclureValidite,
            'inclureRetraitement' => $inclureRetraitement,
        ]);
        $viewModel->setTemplate('application/fichier-these/lister-fichiers');

        return $viewModel;
    }

    /**
     * Action de listage des fichiers déposés répondant aux critères de nature (et version) spécifiés.
     *
     * @return ViewModel
     */
    public function listerFichiersAction()
    {
        $these = $this->requestedThese();

        $nature = $this->params()->fromQuery('nature');
        $nature = $this->fichierService->fetchNatureFichier($nature);
        if ($nature === null) {
            return new JsonModel(['errors' => ["Nature de fichier spécifiée invalide"]]);
        }

        $version = $this->params()->fromQuery('version');
        if ($version !== null) {
            $version = $this->versionFichierService->getRepository()->findOneByCode($version);
            if ($version === null) {
                return new JsonModel(['errors' => ["Version de fichier spécifiée invalide"]]);
            }
        }

        $estRetraite = $this->params()->fromQuery('retraite', false);

        //TODO substituer
//        $fichiers = $these->getFichiersByNatureEtVersion($nature, $version, $estRetraite);
        $fichiers = $this->fichierService->getRepository()->fetchFichiers($these, $nature, $version, $estRetraite);

        $items = array_map(function (Fichier $fichier) use ($these) {
            return [
                'file'          => $fichier,
                'downloadUrl'   => $this->urlFichierThese()->telechargerFichierThese($these, $fichier),
                'apercevoirUrl' => $this->urlFichierThese()->apercevoirFichierThese($these, $fichier),
                'deleteUrl'     => $this->urlFichierThese()->supprimerFichierThese($these, $fichier),
            ];
        }, $fichiers);

        $viewModel = new ViewModel([
            'items' => $items,
            'inclureValidite' => false,
            'inclureRetraitement' => false,
        ]);
        $viewModel->setTemplate('application/fichier-these/lister-fichiers');

        return $viewModel;
    }

    public function telechargerFichierAction()
    {
        $fichier = $this->requestFichier();
        $these = $this->requestedThese();

        if (!$fichier || $fichier->getThese() !== $these) {
            return;
        }

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        $contenuFichier = $this->fichierService->fetchContenuFichier($fichier);
        $fichier->setContenuFichierData($contenuFichier->getData());

        // Envoi du fichier au client (navigateur)
        // NB: $fichier doit être de type \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
        $this->uploader()->download($fichier);
    }

    /**
     * Action de téléversement de fichiers, qualifiés par leur nature et version.
     *
     * @return array|JsonModel
     */
    public function televerserFichierAction()
    {
        $these = $this->requestedThese();
        $retraitement = $this->params()->fromPost('retraitement');
        $validerAuto = (bool)$this->params()->fromPost('validerAuto', false);

        $nature = $this->params()->fromPost('nature');
        $nature = $this->fichierService->fetchNatureFichier($nature);
        if ($nature === null) {
            return new JsonModel(['errors' => ["Nature de fichier spécifiée invalide"]]);
        }

        $version = $this->params()->fromPost('version');
        $version = $this->versionFichierService->getRepository()->findOneByCode($version);
        if ($version === null) {
            return new JsonModel(['errors' => ["Version de fichier spécifiée invalide"]]);
        }

        $uploader = $this->uploader();
        $uploader->getForm()->setUploadMaxFilesize(self::UPLOAD_MAX_FILESIZE);
        $result = $uploader->upload();

        // Si le plugin retourne du JSON, c'est qu'il y a un problème
        if ($result instanceof JsonModel) {
            return $result;
        }

        // Si le plugin retourne un tableau, l'upload a réussi.
        // Enregistrement des fichiers temporaires uploadés.
        if (is_array($result)) {

            // si l'on dépose manuellement une version d'archivage (retraitée manuellement), suppression de toute autre version d'archivage existante
            if ($version->estVersionArchivage()) {
                $versionASupprimer = $version->estVersionCorrigee() ?
                    VersionFichier::CODE_ARCHI_CORR :
                    VersionFichier::CODE_ARCHI;
//                $fichiersTheseRetraites = $these->getFichiersBy(null, null, null, $versionASupprimer);
                $fichiersThese = $this->fichierService->getRepository()->fetchFichiers($these, null, $versionASupprimer, null) ;
                if (! empty($fichiersThese)) {
                    $this->fichierService->deleteFichiers($fichiersThese);
                }
            }

            $nomFichierFormatter = null;
            if ($nature->estThesePdf() || $nature->estFichierNonPdf()) {
               $nomFichierFormatter = new NomFichierFormatter();
            }

            try {
                $fichiers = $this->fichierService->createFichiersFromUpload(
                    $these,
                    $result,
                    $nature,
                    $version,
                    $retraitement,
                    $nomFichierFormatter
                );
            } catch (DepotImpossibleException $die) {
                return new JsonModel(['errors' => [$die->getMessage()]]);
            }

            // tests d'archivabilité (sauf annexes)
            foreach ($fichiers as $fichier) {
                if ($validerAuto && $fichier->supporteTestValidite()) {
                    try {
                        $this->fichierService->validerFichier($fichier);
                    }
                    catch (ValidationImpossibleException $vie) {
//                        $error = sprintf(
//                            "Le test d'archivabilité du fichier '%s' a rencontré un problème indépendant de notre volonté. " .
//                            "Veuillez supprimer le fichier téléversé puis réessayer ultérieurement ou signaler le problème à " .
//                            "l'adresse figurant sur la page 'Contact'.",
//                            $fichier->getNomOriginal());
//                        return new JsonModel(['errors' => [$error]]);
                    }
                }
            }

            // si une thèse est déposée, on notifie de BdD
            if ($nature->estThesePdf()) {
                $subject = "Dépôt d'une thèse";
                $mailViewModel = (new ViewModel())
                    ->setTemplate('application/these/mail/notif-depot-these')
                    ->setVariables([
                        'these'    => $these,
                        'version'  => $version,
                        'subject'  => $subject,
                    ]);
                $this->notificationService->notifierBdD($mailViewModel, $these);
            }

            // si un rapport de soutenance est déposé, on notifie de BdD
            if ($nature->estRapportSoutenance()) {
                $subject = "Dépôt du rapport de soutenance";
                $mailViewModel = (new ViewModel())
                    ->setTemplate('application/these/mail/notif-depot-rapport-soutenance')
                    ->setVariables([
                        'these'    => $these,
                        'subject'  => $subject,
                    ]);
                $this->notificationService->notifierBdD($mailViewModel, $these);
            }
        }

        return new JsonModel(['data' => $result]);
    }

    /**
     * Listage de fichiers déposés quelconques + formulaire de dépôt.
     *
     * @return JsonModel|ViewModel
     */
    public function fichiersAction()
    {
        $these = $this->requestedThese();
        $version = $this->params()->fromQuery('version');

        /** @var VersionFichier $version */
        $version = $this->versionFichierService->getRepository()->findOneBy(['code' => $version]);

        $nature = $this->params()->fromPost('nature');
        $nature = $this->fichierService->fetchNatureFichier($nature);
        if ($nature === null) {
            return new JsonModel(['errors' => ["Nature de fichier spécifiée invalide"]]);
        }

        $titre = "Dépôt " . $nature;

        $form = $this->uploader()->getForm();
        $form->setAttribute('id', uniqid('form-'));
        $form->setUploadMaxFilesize(static::UPLOAD_MAX_FILESIZE);
        $form->addElement((new Hidden('nature'))->setValue($this->idify($nature)));
        $form->addElement((new Hidden('version'))->setValue($this->idify($version)));

        $view = new ViewModel([
            'titre'           => $titre,
            'these'           => $these,
            'uploadUrl'       => $this->urlFichierThese()->televerserFichierThese($these),
            'fichiersListUrl' => $this->urlFichierThese()->listerFichiers($these, $nature, $version, false, ['inclureValidite' => false]),
            'versionFichier'  => $version,
        ]);
        $view->setTemplate('application/these/depot/these');

        return $view;
    }

    /**
     * Suppression d'un fichier déposé.
     */
    public function supprimerFichierAction()
    {
        $fichier = $this->requestFichier();
        $these = $this->requestedThese();

        if (!$fichier || $fichier->getThese() !== $these) {
            // NB: il a fallu abandonner l'exception car faisait planter la suppression
            // de la version de diffusion de la thèse
            // todo: chercher pourquoi
//            throw new RuntimeException("Paramètres reçus invalides.");
            return [];
        }

        $this->fichierService->supprimerFichier($fichier);

        return false;
    }

    /**
     * @return Response|ViewModel
     */
    public function apercevoirFichierAction()
    {
        $fichier = $this->requestFichier();
        $these = $this->requestedThese();
        $imageContentRequested = (bool) (int) $this->params()->fromQuery('content');

        if ($imageContentRequested) {
            return $this->apercuFichier();
        }

        $vm = new ViewModel([
            'fichier' => $fichier,
            'title' => "Aperçu du fichier",
            'apercuUrl' => $this->urlFichierThese()->apercevoirFichierThese($these, $fichier, ['content' => 1]),
        ]);
//        $vm->setTemplate('application/fichier-these/apercevoir-fichier');

        return $vm;
    }

    /**
     * @return Response
     */
    protected function apercuFichier()
    {
        $fichier = $this->requestFichier();
        $nocache = (bool) (int) $this->params()->fromQuery('nocache', '0');

        if (! $fichier->supporteApercu()) {
            throw new LogicException("L'aperçu n'est pas disponible pour le fichier $fichier");
        }

        try {
            $content = $this->fichierService->apercuPremierePage($fichier);
        } catch (RuntimeException $e) {
            $content = "";
        }

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setContent($content);

        $headers = $response->getHeaders();
        $headers
            ->addHeaderLine('Content-Transfer-Encoding', "binary")
            ->addHeaderLine('Content-Type', "image/png")
            ->addHeaderLine('Content-length', strlen($content));

        if ($nocache /*|| $failure*/) {
            $headers
                ->addHeaderLine('Cache-Control', "no-cache")
                ->addHeaderLine('Pragma', 'no-cache');
        }
        else {
            // autorisation de la mise en cache de l'image par le client
            $maxAge = 60 * 60 * 24; // 86400 secondes = 1 jour
            $headers
                ->addHeaderLine('Cache-Control', "private, max-age=$maxAge")
                ->addHeaderLine('Pragma', 'private')// tout sauf 'no-cache'
                ->addHeaderLine('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $maxAge));
        }

        return $response;
    }

    /**
     * @return Fichier
     */
    private function requestFichier()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getFichier();
    }
}
