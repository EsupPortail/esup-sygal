<?php

namespace Application\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Entity\Db\These;
use Application\Form\RechercherCoEncadrantFormAwareTrait;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\CoEncadrant\CoEncadrantServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CoEncadrantController extends AbstractActionController {
    use ActeurServiceAwareTrait;
    use CoEncadrantServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use RechercherCoEncadrantFormAwareTrait;

    public function indexAction()
    {
        $form = $this->getRechercherCoEncadrantForm();
        $form->setAttribute('action', $this->url()->fromRoute('co-encadrant', [], [], true));
        //todo !doit remonter un acteur
        $form->setUrlCoEncadrant($this->url()->fromRoute('co-encadrant/rechercher-co-encadrant', [], [], true));
        $form->get('bouton')->setLabel("Afficher l'historique de co-encadrement");

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['co-encadrant']['id'] !== "") {
                $this->redirect()->toRoute('co-encadrant/historique',['co-encadrant' => $data['co-encadrant']['id']]);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function rechercherCoEncadrantAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $acteurs = $this->getCoEncadrantService()->findByText($term);
            $result = [];
            foreach ($acteurs as $acteur) {
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $acteur->getIndividu()->getPrenom() . ' ' . $acteur->getIndividu()->getNomUsuel();
                $extra = ($acteur->getIndividu()->getEmail())?:$acteur->getIndividu()->getSourceCode();
                $result[$acteur->getIndividu()->getId()] = array(
                    'id' => $acteur->getId(),   // identifiant unique de l'item
                    'label' => $label,          // libellé de l'item
                    'extra' => $extra,          // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }

    public function historiqueAction()
    {
        $coencadrant = $this->getCoEncadrantService()->getRequestedCoEncadrant($this);
        $theses = $this->getTheseService()->getRepository()->fetchThesesByCoEncadrant($coencadrant->getIndividu());

        $encours = []; $closes = [];
        foreach ($theses as $these) {
            if ($these->getEtatThese() === These::ETAT_EN_COURS) {
                $encours[] = $these;
            } else {
                $closes[] = $these;
            }
        }

        return new ViewModel([
            'coencadrant' => $coencadrant,
            'encours' => $encours,
            'closes' => $closes,
        ]);
    }

    public function ajouterCoEncadrantAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        $form = $this->getRechercherCoEncadrantForm();
        $form->setAttribute('action', $this->url()->fromRoute('these/ajouter-co-encadrant', [], [], true));
        $form->setUrlCoEncadrant($this->url()->fromRoute('utilisateur/rechercher-individu', [], [], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            if (isset($data['co-encadrant']['id'])) {
                /** @var Individu $individu */
                $individu = $this->getIndividuService()->getRepository()->find($data['co-encadrant']['id']);
                $this->getActeurService()->ajouterCoEncradrant($these, $individu);
            }
        }

        return new ViewModel([
            'title' => "Ajout d'un co-encadrant pour la thèse de ". $these->getDoctorant()->getIndividu()->getPrenom() . " " . $these->getDoctorant()->getIndividu()->getNomUsuel(),
            'form' => $form,
        ]);
    }

    public function retirerCoEncadrantAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        $acteurId = $this->params()->fromRoute('co-encadrant');

        /** @var Acteur $acteur */
        $acteur = $this->getActeurService()->getRepository()->find($acteurId);
        if ($acteur !== null AND $acteur->getThese() === $these) $this->getActeurService()->delete($acteur);

        $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);

    }
}