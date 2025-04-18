<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\AnneeUniv;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\FormationExterne;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use Formation\Provider\Parametre\FormationParametres;
use Formation\Provider\Validation\TypeValidation;
use Formation\Service\Formation\FormationExterne\FormationExterneServiceAwareTrait;
use Formation\Service\Validation\ValidationFormationExterne\ValidationFormationExterneServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;
use Validation\Entity\Db\ValidationFormationExterne;

class IndexController extends AbstractController
{
    use EntityManagerAwareTrait;
    use DoctorantServiceAwareTrait;
    use ParametreServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use FormationExterneServiceAwareTrait;
    use ValidationFormationExterneServiceAwareTrait;

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

        $anneeUnivCourante = $this->anneeUnivService->courante();
        $formationsExternes = [];
        if($doctorant) {
            $debut = $this->anneeUnivService->computeDateDebut($anneeUnivCourante);
            $fin = $this->anneeUnivService->computeDateFin($anneeUnivCourante);
            $ouvertes = $this->getEntityManager()->getRepository(Session::class)->findSessionsByDoctorant($doctorant, Etat::CODE_OUVERTE, $debut, $fin, true);
            $preparations = $this->getEntityManager()->getRepository(Session::class)->findSessionsByDoctorant($doctorant, Etat::CODE_PREPARATION, $debut, $fin, true);
            /** @var Inscription[] $inscription */
            $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);

            $formationsExternes = $this->formationExterneService->getRepository()->fetchFormationsExternesByDoctorant($doctorant, 'debut', 'ASC', true);
            $formationsExternesAvecAnneeUniv = $this->getFormationsExternesAvecAnneeUniv($formationsExternes, $anneeUnivCourante);
        } else {
            $ouvertes = [];
            $preparations = [];
            $inscriptions = [];
            $formationsExternesAvecAnneeUniv = [];
        }

        return new ViewModel([
            'doctorant' => $doctorant,
            'ouvertes' => $ouvertes,
            'preparations' => $preparations,
            'inscriptions' => $inscriptions,
            'formationsExternes' => $formationsExternesAvecAnneeUniv,
            'role' => $this->userContextService->getSelectedIdentityRole(),
            'anneeUnivCourante' => $anneeUnivCourante,
            'anneesUniv' => $this->formationExterneService->getAnneesUnivAsOptions($formationsExternes),
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

    public function indexMaisonDoctoratAction() : ViewModel
    {
        $these = $this->requestedThese();
        $doctorant = $these->getDoctorant();

        $anneeUnivCourante = $this->anneeUnivService->courante();

        /** @var Inscription[] $inscription */
        $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);

        $formationsExternes = $this->formationExterneService->getRepository()->fetchFormationsExternesByDoctorant($doctorant, 'debut', 'ASC', true);
        $formationsExternesAvecAnneeUniv = $this->getFormationsExternesAvecAnneeUniv($formationsExternes, $anneeUnivCourante);

        return new ViewModel([
            'these' => $these,
            'doctorant' => $doctorant,
            'inscriptions' => $inscriptions,
            'formationsExternes' => $formationsExternesAvecAnneeUniv,
            'role' => $this->userContextService->getSelectedIdentityRole(),
            'anneeUnivCourante' => $anneeUnivCourante,
            'anneesUniv' => $this->formationExterneService->getAnneesUnivAsOptions($formationsExternes),
            'delai' => $this->getParametreService()->getValeurForParametre(FormationParametres::CATEGORIE, FormationParametres::DELAI_ENQUETE),
        ]);
    }

    private function getFormationsExternesAvecAnneeUniv(array $formationsExternes, AnneeUniv $anneeUnivCourante): array
    {
        $formationsExternesAvecAnneeUniv = [];
        /** @var FormationExterne $formationExterne */
        foreach ($formationsExternes as $formationExterne) {
            $premiereAnnee = null;
            if ($formationExterne->getDebut()) {
                $anneeUniv = $this->anneeUnivService->fromDate($formationExterne->getDebut());
                $premiereAnnee = $anneeUniv->getPremiereAnnee();
            }
            /** @var ValidationFormationExterne|null $validation */
            $validation =
                $this->validationFormationExterneService->getRepository()->findValidationByFormationExterneAndCode($formationExterne, TypeValidation::CODE_VALIDATION_FORMATION_EXTERNE);
            if (!$validation) $validation = $this->validationFormationExterneService->getRepository()->findValidationByFormationExterneAndCode($formationExterne, TypeValidation::CODE_REFUS_FORMATION_EXTERNE);
            $formationsExternesAvecAnneeUniv[] = array("formationExterne" => $formationExterne, "anneeUniv" => $premiereAnnee ?? $anneeUnivCourante->getPremiereAnnee(), "validation" => $validation);
        }

        return $formationsExternesAvecAnneeUniv;
    }
}