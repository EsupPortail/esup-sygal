<?php

namespace Application\Controller;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilterAwareTrait;
use Application\Filter\NomFichierFormatter;
use Application\RouteMatch;
use Application\Service\Fichier\Exception\DepotImpossibleException;
use Application\Service\Fichier\Exception\ValidationImpossibleException;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Application\View\Helper\Sortable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenApp\Exception\RuntimeException;
use Zend\Console\Request as ConsoleRequest;
use Zend\Form\Element\Hidden;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class FichierTheseController extends AbstractController
{
    use TheseServiceAwareTrait;
    use FichierServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use EventRouterReplacerAwareTrait;

    public function deposesAction()
    {
        /**
         * Application des filtres et tris par défaut.
         */
        $needsRedirect = false;
        $queryParams = $this->params()->fromQuery();
        $data        = $this->params()->fromPost('individu');

        $version = $this->params()->fromQuery('version');
        $sort = $this->params()->fromQuery('sort');

        $individuId = null;
        if (!empty($data['id'])) {
            $individuId = $data['id'];
        }

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
            ->join('d.individu', 'i')
            ->leftJoin('f.validites', 'val')
            ->join('f.version', 'ver');
        if (isset($version) && $version !== '')
        {
            $qb->andWhere('ver.code = :version')
                ->setParameter("version" , $version);
        }
        if (isset($individuId) && $individuId !== '') {
            $qb->andWhere('i.id = :individuId')
                ->setParameter("individuId" , $individuId);
        }


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
        $fichier->setContenuFichierData($contenuFichier);

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
                $notif = $this->notifierService->getNotificationFactory()->createNotificationForTheseTeleversee($these, $version);
                $this->notifierService->trigger($notif);
            }

            // si un rapport de soutenance est déposé, on notifie de BdD
            if ($nature->estRapportSoutenance()) {
                $notif = $this->notifierService->getNotificationFactory()->createNotificationForFichierTeleverse($these);
                $notif
                    ->setSubject("Dépôt du rapport de soutenance")
                    ->setTemplatePath('application/these/mail/notif-depot-rapport-soutenance');
                $this->notifierService->trigger($notif);
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
        $version = $fichier->getVersion();
        $nature = $fichier->getNature();

        if (!$fichier || $fichier->getThese() !== $these) {
            // NB: il a fallu abandonner l'exception car faisait planter la suppression
            // de la version de diffusion de la thèse
            // todo: chercher pourquoi
//            throw new RuntimeException("Paramètres reçus invalides.");
            return [];
        }

        // s'il s'agit de la thèse corrigée, il faudra supprimer l'éventuelle validation du dépôt
        $supprimerValidationDepotTheseCorrigee = $nature->estThesePdf() && $version->estVersionCorrigee();

        $this->fichierService->supprimerFichier($fichier);

        // suppression de l'éventuelle validation du dépôt
        if ($supprimerValidationDepotTheseCorrigee) {
            $this->validationService->unvalidateDepotTheseCorrigee($these);
        }

        return false;
    }

    /**
     * @return Response|ViewModel
     */
    public function apercevoirPageDeCouvertureAction()
    {
        $these = $this->requestedThese();
        $imageContentRequested = (bool) (int) $this->params()->fromQuery('content');

        if ($imageContentRequested) {
            return $this->apercuPageDeCouverture();
        }

        $apercuUrl = $this->urlFichierThese()->apercevoirPageDeCouverture($these, [
            'content' => 1,
            'nocache' => 1,
            'ts' => time(), // ajouter un ts garantit que le navigateur ne mettra pas en cache l'image
        ]);

        $vm = new ViewModel([
            'title'     => "Aperçu de la page de couverture",
            'apercuUrl' => $apercuUrl,
        ]);

        return $vm;
    }

    /**
     * @return Response
     */
    protected function apercuPageDeCouverture()
    {
        // fetch de la thèse AVEC les jointures parcourues dans la génération de la PDC
        $theseId = $this->params('these');
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb
            ->addSelect('etab, etabstr, doct, doctind, ed, ur, edstr, urstr, act, actind')
            ->join('t.etablissement', 'etab')
            ->join('etab.structure', 'etabstr')
            ->join('t.doctorant', 'doct')
            ->join('doct.individu', 'doctind')
            ->leftJoin('t.ecoleDoctorale', 'ed')
            ->leftJoin('t.uniteRecherche', 'ur')
            ->leftJoin('ed.structure', 'edstr')
            ->leftJoin('ur.structure', 'urstr')
            ->leftJoin('t.acteurs', 'act')
            ->leftJoin('act.individu', 'actind')
            ->andWhere('t = :these')
            ->setParameter('these', $theseId);
        try {
            $these = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs thèses trouvées avec l'id $theseId");
        }

        $filename = uniqid() . '.pdf';
        $renderer = $this->getServiceLocator()->get('view_renderer'); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
        $this->fichierService->generatePageDeCouverture($these, $renderer, $filename);

        $filepath = sys_get_temp_dir() . '/' . $filename; // NB: l'exporter PDF stocke dans sys_get_temp_dir()
        try {
            $content = $this->fichierService->generateFirstPagePreview($filepath);
        } catch (RuntimeException $e) {
            $content = "";
        }
        unlink($filepath);

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();

        return $this->fichierService->createResponseForFileContent($response, $content);
    }

    /**
     * Console action.
     */
    public function fusionnerConsoleAction()
    {
        ini_set('memory_limit', '500M');
        ini_set('max_execution_time', '600');

        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new RuntimeException('You can only use this action from a console!');
        }

        $id  = $request->getParam('these');
        $versionFichier  = $request->getParam('versionFichier');
        $removeFirstPage  = (bool) $request->getParam('removeFirstPage');
        $notifier  = $request->getParam('notifier', false);

        if (! $id) {
            throw new RuntimeException("Argument obligatoire manquant: fichier");
        }

        /** @var These $these */
        $these = $this->theseService->getRepository()->find($id);
        if ($these === null) {
            throw new RuntimeException("Aucune thèse trouvée avec cet id : " . $id);
        }

        $outputFilePath = $this->fichierService->fusionneFichierThese($these, $versionFichier, $removeFirstPage);
//        $outputFilePath = "/tmp/recuperer-fusion/35249-samassa-haoua-merged.pdf";

        $this->eventRouterReplacer->replaceEventRouter($this->getEvent());

        echo "Fichier créé avec succès: " . $outputFilePath;
        echo PHP_EOL;

        if ($notifier) {
            $destinataires = $notifier;
            $notif = $this->notifierService->getNotificationFactory()->createNotificationFusionFini($destinataires, $these, $outputFilePath);
            $this->notifierService->trigger($notif);
            echo "Destinataires du courriel envoyé: " . implode(",",$notif->getTo());
            echo PHP_EOL;
        }

        $this->eventRouterReplacer->restoreEventRouter();

        exit(0);
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

    public function recupererFusionAction()
    {
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        $outputFile = $this->params()->fromRoute('outputFile');
        $outputFilePath =  sys_get_temp_dir() ."/". $outputFile;

        if (!is_readable($outputFilePath)) {
            throw new RuntimeException("Le fichier de votre manuscrit n'est plus disponible.");
        }

        /** Retourner un PDF ...  */
        $contenu     = file_get_contents($outputFilePath);
        $content     = is_resource($contenu) ? stream_get_contents($contenu) : $contenu;

        header('Content-Description: File Transfer');
        header('Content-Type: ' . 'application/pdf');
        header('Content-Disposition: attachment; filename=' . $outputFile);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        echo $content;
        exit;
    }
}
