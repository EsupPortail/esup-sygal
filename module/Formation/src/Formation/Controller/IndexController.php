<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Parametre\FormationParametres;
use Formation\Service\Justificatif\JustificatifServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class IndexController extends AbstractController
{
    use EntityManagerAwareTrait;
    use DoctorantServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use JustificatifServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        return new ViewModel();
    }

    public function indexDoctorantAction() : ViewModel
    {
        /** @var Doctorant $doctorant */
        $doctorantId = $this->params()->fromRoute('doctorant');
        if ($doctorantId !== null) {
            $doctorant = $this->getEntityManager()->getRepository(Doctorant::class)->find($doctorantId);
        } else {
            $user = $this->userContextService->getIdentityDb();
            $doctorant = $this->doctorantService->getDoctorantsByUser($user);
        }

        if($doctorant) {
            $anneeScolaire = $this->anneeUnivService->courante();
            $debut = $this->anneeUnivService->computeDateDebut($anneeScolaire);
            $fin = $this->anneeUnivService->computeDateFin($anneeScolaire);
            $ouvertes = $this->getEntityManager()->getRepository(Session::class)->findSessionsByDoctorant($doctorant, Etat::CODE_OUVERTE, $debut, $fin, true);
            $preparations = $this->getEntityManager()->getRepository(Session::class)->findSessionsByDoctorant($doctorant, Etat::CODE_PREPARATION, $debut, $fin, true);
            /** @var Inscription[] $inscription */
            $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);

            $terminees = [];
            $encours = [];
            $inscriptionsAbsenceInjustifiees = [];

            /** @var Inscription $inscription */
            foreach ($inscriptions as $inscription) {
                $seances = $inscription->getSession()->getSeances()->toArray();
                $seances = array_filter($seances, function ($a) { return $a->estNonHistorise(); });
                $nbSeance = count($seances);
                if ($nbSeance > 0 /**  AND $inscription->getSession()->estTerminee() **/ AND $inscription->getSession()->getEtat()->getCode() === Etat::CODE_CLOTURER) {
                    $terminees[] = $inscription;
                    if($this->justificatifService->isInscriptionHasAbsenceInjustifiee($inscription)) $inscriptionsAbsenceInjustifiees[] = $inscription;
                } else {
                    if ($inscription->estNonHistorise()) $encours[] = $inscription;
                }
            }
        } else {
            $ouvertes = [];
            $preparations = [];
            $inscriptions = [];
            $terminees = [];
            $encours = [];
            $inscriptionsAbsenceInjustifiees = [];
        }

        return new ViewModel([
            'doctorant' => $doctorant,
            'ouvertes' => $ouvertes,
            'preparations' => $preparations,
            'terminees' => $terminees,
            'encours' => $encours,
            'inscriptions' => $inscriptions,
            'inscriptionsAbsenceInjustifiees' => $inscriptionsAbsenceInjustifiees,
            'delai' => $this->getParametreService()->getValeurForParametre(FormationParametres::CATEGORIE, FormationParametres::DELAI_ENQUETE),
        ]);
    }

    public function indexFormateurAction() : ViewModel
    {
        /** @var Individu $individu */
        $individuId = $this->params()->fromRoute('formateur');
        if ($individuId !== null) {
            $individu = $this->getEntityManager()->getRepository(Individu::class)->find($individuId);
        } else {
            $user = $this->userContextService->getIdentityDb();
            $individu = ($user)?$user->getIndividu():null;
        }

        if($individu) {
            $passees    = $this->getEntityManager()->getRepository(Session::class)->findSessionsPasseesByFormateur($individu);
            $courantes  = $this->getEntityManager()->getRepository(Session::class)->findSessionsCourantesByFormateur($individu);
            $futures    = $this->getEntityManager()->getRepository(Session::class)->findSessionsFuturesByFormateur($individu);
        } else {
            $passees = [];
            $courantes = [];
            $futures = [];
        }

        return new ViewModel([
            'individu' => $individu,
            'passees' => $passees,
            'courantes' => $courantes,
            'futures' => $futures,
        ]);
    }

}