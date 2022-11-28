<?php

namespace Depot\Controller;

use Application\Command\Exception\TimedOutCommandException;
use Application\Controller\AbstractController;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilterAwareTrait;
use Application\RouteMatch;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\View\Helper\Sortable;
use Depot\Entity\Db\FichierThese;
use Depot\Service\FichierThese\Exception\DepotImpossibleException;
use Depot\Service\FichierThese\Exception\ValidationImpossibleException;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Depot\Service\These\DepotServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Exception;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Form\Element\Hidden;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Exception\NotificationException;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class FichierTheseController extends AbstractController
{
    use DepotServiceAwareTrait;
    use TheseServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierTheseServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotifierServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use EventRouterReplacerAwareTrait;

    const FICHIER_THESE_TELEVERSE = 'FICHIER_THESE_DEPOSE';

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
            // tri par défaut : nom
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

        $qb = $this->fichierTheseService->getRepository()->createQueryBuilder('ft');
        $qb
            ->addSelect('f, t, d, val, ver')
            ->join('ft.fichier', 'f')
            ->join('ft.these', 't')
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

        $paginator = new \Laminas\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
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
     * Action de listage des fichiers déposés répondant aux critères de nature (et version) spécifiés.
     *
     * @return ViewModel
     */
    public function listerFichiersAction()
    {
        $these = $this->requestedThese();

        $nature = $this->params()->fromQuery('nature');
        $nature = $this->fichierTheseService->fetchNatureFichier($nature);
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

        $fichiers = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, $nature, $version, $estRetraite);

        $items = array_map(function (FichierThese $fichier) use ($these) {
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
        $viewModel->setTemplate('depot/fichier-these/lister-fichiers');

        return $viewModel;
    }

    public function telechargerFichierAction()
    {
        $fichier = $this->requestFichier();

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
        // NB: $fichierThese->getFichier() doit être de type \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
        $this->uploader()->download($fichier);
    }

    /**
     * Action de téléversement de fichiers, qualifiés par leur nature et version.
     *
     * @return array|JsonModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function televerserFichierAction()
    {
        $these = $this->requestedThese();
        $retraitement = $this->params()->fromPost('retraitement');
        $validerAuto = (bool)$this->params()->fromPost('validerAuto', false);

        $nature = $this->params()->fromPost('nature');
        $nature = $this->fichierTheseService->fetchNatureFichier($nature);
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
                $fichierTheses = $this->fichierTheseService->getRepository()->fetchFichierTheses($these, null, $versionASupprimer, null) ;
                if (! empty($fichierTheses)) {
                    $this->fichierTheseService->deleteFichiers($fichierTheses, $these);
                }
            }

            try {
                $fichierTheses = $this->fichierTheseService->createFichierThesesFromUpload(
                    $these,
                    $result,
                    $nature,
                    $version,
                    $retraitement
                );
            } catch (DepotImpossibleException $die) {
                return new JsonModel(['errors' => [$die->getMessage()]]);
            }

            // tests d'archivabilité (sauf annexes)
            foreach ($fichierTheses as $fichierThese) {
                if ($validerAuto && $fichierThese->supporteTestValidite()) {
                    try {
                        $this->fichierTheseService->validerFichierThese($fichierThese);
                    }
                    catch (ValidationImpossibleException $vie) {
//                        $error = sprintf(
//                            "Le test d'archivabilité du fichier '%s' a rencontré un problème indépendant de notre volonté. " .
//                            "Veuillez supprimer le fichier téléversé puis réessayer ultérieurement ou signaler le problème à " .
//                            "l'adresse figurant sur la page 'Contact'.",
//                            $fichierThese->getNomOriginal());
//                        return new JsonModel(['errors' => [$error]]);
                    }
                }
            }

            // déclenchement d'un événement "fichier de thèse téléversé"
            $this->events->trigger(
                self::FICHIER_THESE_TELEVERSE,
                $these, [
                    'nature' => $nature,
                    'version' => $version,
                ]
            );

            // si une thèse est déposée, on notifie de BdD
            // todo: déplacer ceci dans un service écoutant l'événement "fichier de thèse téléversé" déclenché ci-dessus
            if ($nature->estThesePdf()) {
                $notif = $this->notifierService->getNotificationFactory()->createNotificationForTheseTeleversee($these, $version);
                try {
                    $this->notifierService->trigger($notif);
                } catch (NotificationException $e) {
                    return new JsonModel([
                        'errors' => array_filter([
                            $e->getMessage(),
                            $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                        ])
                    ]);
                }
            }

            // si un rapport de soutenance est déposé, on notifie de BdD
            // todo: déplacer ceci dans un service écoutant l'événement "fichier de thèse téléversé" déclenché ci-dessus
            if ($nature->estRapportSoutenance()) {
                $notif = $this->notifierService->getNotificationFactory()->createNotificationForFichierTeleverse($these);
                $notif
                    ->setSubject("Dépôt du rapport de soutenance")
                    ->setTemplatePath('depot/depot/mail/notif-depot-rapport-soutenance');
                try {
                    $this->notifierService->trigger($notif);
                } catch (NotificationException $e) {
                    return new JsonModel([
                        'errors' => array_filter([
                            $e->getMessage(),
                            $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                        ])
                    ]);
                }
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
        $nature = $this->fichierTheseService->fetchNatureFichier($nature);
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
        $view->setTemplate('depot/depot/depot/these');

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

        if (!$fichier) {
            // NB: il a fallu abandonner l'exception car faisait planter la suppression
            // de la version de diffusion de la thèse
            // todo: chercher pourquoi
//            throw new RuntimeException("Paramètres reçus invalides.");
            return [];
        }

        // s'il s'agit de la thèse corrigée, il faudra supprimer l'éventuelle validation du dépôt
        $supprimerValidationDepotTheseCorrigee = $nature->estThesePdf() && $version->estVersionCorrigee();

        $this->fichierTheseService->supprimerFichierThese($fichier, $these);

        // suppression de l'éventuelle validation du dépôt
        if ($supprimerValidationDepotTheseCorrigee) {
            $this->depotValidationService->unvalidateDepotTheseCorrigee($these);
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
            try {
                return $this->apercuPageDeCouverture();
            } catch (Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                return (new Response())
                    ->setStatusCode(Response::STATUS_CODE_500)
                    ->setContent($e->getMessage()); // comment retrouver le content côté js ?
            }
        }

        $apercuUrl = $this->urlFichierThese()->apercevoirPageDeCouverture($these, [
            'content' => 1,
            'nocache' => 1,
            'ts' => time(), // ajouter un ts garantit que le navigateur ne mettra pas en cache l'image
        ]);

        return new ViewModel([
            'title'     => "Aperçu de la page de couverture",
            'apercuUrl' => $apercuUrl,
        ]);
    }

    /**
     * @throws \Exception Erreur lors de la génération de l'aperçu
     */
    protected function apercuPageDeCouverture(): Response
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

        $qb
            ->addSelect('etab_structureSubstituante')
            ->leftJoin("edstr.structureSubstituante", "etab_structureSubstituante")
            ->addSelect('etablissementSubstituante')
            ->leftJoin("etab_structureSubstituante.etablissement", "etablissementSubstituante");
        $qb
            ->addSelect('edstr_structureSubstituante')
            ->leftJoin("edstr.structureSubstituante", "edstr_structureSubstituante")
            ->addSelect('ecoleDoctoraleSubstituante')
            ->leftJoin("edstr_structureSubstituante.ecoleDoctorale", "ecoleDoctoraleSubstituante");
        $qb
            ->addSelect('urstr_structureSubstituante')
            ->leftJoin("urstr.structureSubstituante", "urstr_structureSubstituante")
            ->addSelect('uniteRechercheSubstituante')
            ->leftJoin("urstr_structureSubstituante.uniteRecherche", "uniteRechercheSubstituante");

        try {
            $these = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs thèses trouvées avec l'id $theseId");
        }

        $filename = uniqid() . '.pdf';
        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);
        $this->fichierTheseService->generatePageDeCouverture($pdcData, $filename);

        $filepath = sys_get_temp_dir() . '/' . $filename; // NB: l'exporter PDF stocke dans sys_get_temp_dir()
        $content = $this->fichierTheseService->generateFirstPagePreview($filepath);
        unlink($filepath);

        /** @var \Laminas\Http\Response $response */
        $response = $this->getResponse();

        return $this->fichierTheseService->createResponseForFileContent($response, $content, "image/png");
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

        $pdcData = $this->theseService->fetchInformationsPageDeCouverture($these);
        try {
            $outputFilePath = $this->fichierTheseService->fusionnerPdcEtThese($these, $pdcData, $versionFichier, $removeFirstPage);
        } catch (TimedOutCommandException $e) {
            // n'arrive jamais car aucun timeout n'a été spécifié lors de l'appel à fusionnerPdcEtThese()
        }

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
