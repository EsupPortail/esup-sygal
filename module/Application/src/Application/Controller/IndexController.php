<?php

namespace Application\Controller;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractController
{
    use TheseServiceAwareTrait;

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

//        $config = ($this->getServiceLocator()->get('config'));
//        $this->pretty_print($config);


        if ($this->identity() && count($this->userContextService->getSelectableIdentityRoles()) === 0) {
            return $this->redirect()->toRoute('not-allowed');
        }

        if (($response = $this->indexForSelectedRole()) instanceof Response) {
            return $response;
        }

        $vm = new ViewModel([
            'role' => $this->userContextService->getSelectedIdentityRole(),
            'estDoctorant' => (bool) $this->userContextService->getIdentityDoctorant(),
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
                ->andWhereDoctorantIs($doctorant)
                ->andWhereEtatIs(These::ETAT_EN_COURS);
            $thesesEnCours = $qb->getQuery()->getResult();

            /**
             * Si le doctorant connecté n'a aucune thèse en cours, on le déconnecte!
             */
            if (count($thesesEnCours) === 0) {
                /** @var AuthenticationServiceInterface $authenticationService */
                $authenticationService = $this->getServiceLocator()->get('Zend\\Authentication\\AuthenticationService');
                $authenticationService->clearIdentity();
                $this->flashMessenger()->addErrorMessage(
                    "Aucune thèse en cours n'a été trouvée vous concernant, vous ne pouvez pas utiliser cette application.");
                return $this->redirect()->toRoute('home');
            }

            /**
             * Sinon redirection vers le détail de sa 1ere thèse trouvée.
             * @todo Gérer le cas où il y a plus d'une thèse.
             */

//            return $this->redirect()->toRoute('these/identite', ['these' => current($thesesEnCours)->getId()]);
            $vm->setVariables([
                'doctorant' => $doctorant,
                'theses' => $thesesEnCours,
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
         * Profil "Bureau des doctorats".
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
}