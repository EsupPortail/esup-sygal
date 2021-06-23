<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Role;
use Application\Entity\Db\Variable;
use Application\Entity\UserWrapper;
use Application\Exception\DomainException;
use Application\Service\Actualite\ActualiteServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Information\Service\InformationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Http\Response;
use Zend\Validator\EmailAddress as EmailAddressValidator;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractController
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use TheseServiceAwareTrait;
    use ActualiteServiceAwareTrait;
    use InformationServiceAwareTrait;

    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function setAuthenticationService(AuthenticationServiceInterface $authenticationService): void
    {
        $this->authenticationService = $authenticationService;
    }

    public function pretty_print(array $array, $level = 0) {
        foreach($array as $key => $value) {
            for ($i = 0 ; $i < $level ; ++$i) print "&nbsp;|&nbsp;";
            print "<b>". $key ."</b> => ";
            if (is_array($value)) {
                print "<br/>";
                $this->pretty_print($value, $level+1);
            } else {
                print $value;
                print "<br/>";
            }
        }
    }

    public function indexAction()
    {
        /**
         * NB (2019/03/20) : désactiver pour donner l'accès à toutes les thèses pour les rôles doctorant et directeur/co-directeur
         */
//        if ($this->identity() && count($this->userContextService->getSelectableIdentityRoles()) === 0) {
//            // déconnexion applicative
//            $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
//            $this->zfcUserAuthentication()->getAuthAdapter()->logoutAdapters();
//            $this->zfcUserAuthentication()->getAuthService()->clearIdentity();
//
//            return $this->redirect()->toRoute('not-allowed');
//        }

        if (($response = $this->indexForSelectedRole()) instanceof Response) {
            return $response;
        }

        $vm = new ViewModel([
            'role' => $this->userContextService->getSelectedIdentityRole(),
            'estDoctorant' => (bool) $this->userContextService->getIdentityDoctorant(),
            'url' => $this->actualiteService->isActif() ? $this->actualiteService->getUrl() : null,
            'offre' => $this->actualiteService->isOffre() ? $this->getEcoleDoctoraleService()->getOffre() : null,
            'informations' => $this->informationService->getInformations(true),
        ]);

        if ($response instanceof ViewModel) {
            $vm->addChild($response, 'content');
        }

        return $vm;
    }

    public function notAllowedAction()
    {
        $vm = new ViewModel([
            'messages' => [
                'danger' => "<strong>Il semblerait que vous n'êtes pas habilité&middot;e à utiliser cette application.</strong>",
                'info'  => <<<EOS
<strong>NB : </strong>Si vous êtes doctorant&middot;e, vous devez vous connecter avec votre compte étudiant.
EOS
            ],
        ]);

        return $vm;
    }

    /**
     * @return Response|ViewModel|null
     */
    private function indexForSelectedRole()
    {
        $vm = new ViewModel();

        /**
         * Profil "Doctorant".
         */
        if ($role = $this->userContextService->getSelectedRoleDoctorant()) {
            $doctorant = $this->userContextService->getIdentityDoctorant();
            if (! $doctorant) {
                throw new RuntimeException(
                    "Anomalie: le rôle '{$role->getRoleId()}' est sélectionné mais les données d'identité associées sont vides");
            }

            $qb = $this->theseService->getRepository()->createQueryBuilder('t')
                ->andWhereDoctorantIs($doctorant);
            $theses = $qb->getQuery()->getResult();

            /**
             * Si aucune thèse n'a été trouvée pour le doctorant connecté, on le déconnecte!
             */
            if (count($theses) === 0) {
                /** @var AuthenticationServiceInterface $authenticationService */
                $authenticationService = $this->authenticationService;
                $authenticationService->clearIdentity();
                $this->flashMessenger()->addErrorMessage(
                    "Aucune thèse n'a été trouvée vous concernant, vous ne pouvez pas utiliser cette application.");
                return $this->redirect()->toRoute('home');
            }

            /**
             * Sinon redirection vers le détail de sa 1ere thèse trouvée.
             * @todo Gérer le cas où il y a plus d'une thèse.
             */

//            return $this->redirect()->toRoute('these/identite', ['these' => current($thesesEnCours)->getId()]);
            $vm->setVariables([
                'doctorant' => $doctorant,
                'theses' => $theses,
            ]);
            $vm->setTemplate('application/index/partial/doctorant');
        }
        /**
         * Profil "Directeur de thèse".
         */
        elseif ($role = $this->userContextService->getSelectedRoleDirecteurThese()) {
            $vm->setTemplate('application/index/partial/dir-these');
        }
        /**
         * Profil "Ecole doctorale".
         */
        elseif ($role = $this->userContextService->getSelectedRoleDirecteurEcoleDoctorale()) {
            $vm->setTemplate('application/index/partial/ed');
        }
        /**
         * Profil "Unité de recherche".
         */
        elseif ($role = $this->userContextService->getSelectedRoleDirecteurUniteRecherche()) {
            $vm->setTemplate('application/index/partial/ur');
        }
        /**
         * Profil "Maison du doctorat".
         */
        elseif ($role = $this->userContextService->getSelectedRoleBDD()) {
            $vm->setTemplate('application/index/partial/bdd');
        }
        /**
         * Profil "Bibliothèque universitaire".
         */
        elseif ($role = $this->userContextService->getSelectedRoleBU()) {
            $vm->setTemplate('application/index/partial/bu');
        }
        else {
            return null;
        }

        return $vm;
    }

    /**
     * Remplacement de la page de contact de unicaen/app par une autre, soumise à authentification,
     * on l'adresse de contact dépend de l'établissement de l'utilisateur authentifié.
     *
     * @return array|Response
     */
    public function contactAction()
    {
        $userWrapper = $this->userContextService->getIdentityUserWrapper();
        if ($userWrapper === null) {
            return $this->redirect()->toRoute('home');
        }

        $etablissement = $this->findEtablissementUtilisateur($userWrapper);

        $repo = $this->variableService->getRepository();
        $variable = $repo->findByCodeAndEtab(Variable::CODE_EMAIL_ASSISTANCE, $etablissement);
        if ($variable === null) {
            throw new RuntimeException(
                "Anomalie: aucune adresse d'assistance trouvée dans les Variables pour l'établissement '$etablissement'.");
        }

        $contact = $variable->getValeur();

        $v = new EmailAddressValidator();
        if (!$v->isValid($contact)) {
            throw new RuntimeException(
                "Anomalie: l'adresse d'assistance trouvée dans les Variables n'est pas valide: $contact");
        }

        return [
            'etablissement' => $etablissement,
            'contact' => $contact,
            'individu' => $this->userContextService->getIdentityIndividu(),
            'utilisateur' => $this->userContextService->getIdentityDb(),
            'role' => $this->userContextService->getSelectedIdentityRole(),
            'roles' => $this->userContextService->getSelectableIdentityRoles(),
        ];
    }

    /**
     * @param UserWrapper $userWrapper
     * @return Etablissement
     */
    protected function findEtablissementUtilisateur(UserWrapper $userWrapper): Etablissement
    {
        $individu = $this->userContextService->getIdentityDoctorant();
        if ($individu !== null) {
            return $individu->getEtablissement();
        }

        /** @var Role $role */
        $role = $this->userContextService->getSelectedIdentityRole();
        if ($role !== null && $structure = $role->getStructure()) {
            $etablissement =  $this->etablissementService->getRepository()->findByStructureId($structure->getId());
            if ($etablissement) return $etablissement;
        }

        return $this->etablissementService->getRepository()->findOneForUserWrapper($userWrapper);
    }
}