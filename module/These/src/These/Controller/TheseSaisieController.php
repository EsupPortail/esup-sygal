<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Form\TheseSaisie\TheseSaisieForm;
use These\Form\TheseSaisie\TheseSaisieFormAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class TheseSaisieController extends AbstractController {
    use ActeurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseSaisieFormAwareTrait;
    use SourceAwareTrait;
    use DomaineHalServiceAwareTrait;

    /** FONCTIONS TEMPORAIRES A DEPLACER PLUS TARD */
    /**
     * @param These $these
     * @param Individu $individu
     * @param string $roleCode
     * @return string
     */
    public function generateCodeSourceActeur(These $these, Individu $individu, string $roleCode) : string
    {
        $code = $these->getId() . "_". $individu->getId() . "_" . $roleCode;
        return $code;
    }

    public function saisieAction()
    {
        $theseId = $this->params()->fromRoute('these');
        if ($theseId !== null) {
            $these = $this->requestedThese();
        } else {
            $these = new These();
            $these->setSource($this->source);
            $these->setSourceCode(uniqid());
        }

        $form = $this->getTheseSaisieForm();
        $domainesHal = $this->domaineHalService->getDomainesHalAsOptions();
        $form->get('domaineHal')->setDomainesHal($domainesHal);

        $form->bind($these);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            //Permet de gérer le cas où aucune sélection n'est effectuée (afin de passer dans l'hydrateur)
            if (!isset($data['domaineHal'])) {
                $data['domaineHal'] = array("domaineHal" => array(""));
            }
            $form->setData($data);

            if ($form->isValid()) {
                if ($theseId === null) {
                    $this->getTheseService()->create($these);
                } else {
                    $this->getTheseService()->update($these);
                }
                //ACTEUR //
                /** Gestion des acteurs */
                if ($data['directeur-individu']['id'] and $data['directeur-qualite'] and $data['directeur-etablissement']) {
                    /** @var Individu $individu */
                    $individu = $this->getIndividuService()->getRepository()->find($data['directeur-individu']['id']);
                    $qualite = $this->getQualiteService()->getQualite($data['directeur-qualite']);
                    /** @var Etablissement $etablissement */
                    $etablissement = $this->getEtablissementService()->getRepository()->find($data['directeur-etablissement']);
                    if ($individu and $qualite and $etablissement) {
                        $this->getActeurService()->creerOrModifierActeur($these, $individu, Role::CODE_DIRECTEUR_THESE, $qualite, $etablissement);
                    }
                }
                $temoins = [];
                for ($i = 1; $i <= TheseSaisieForm::NBCODIR; $i++) {
                    if ($data['codirecteur' . $i . '-individu']['id'] and $data['codirecteur' . $i . '-qualite'] and $data['codirecteur' . $i . '-etablissement']) {
                        /** @var Individu $individu */
                        $individu = $this->getIndividuService()->getRepository()->find($data['codirecteur' . $i . '-individu']['id']);
                        $qualite = $this->getQualiteService()->getQualite($data['codirecteur' . $i . '-qualite']);
                        /** @var Etablissement $etablissement */
                        $etablissement = $this->getEtablissementService()->getRepository()->find($data['codirecteur' . $i . '-etablissement']);
                        if ($individu and $qualite and $etablissement) {
                            $acteur = $this->getActeurService()->creerOrModifierActeur($these, $individu, Role::CODE_CODIRECTEUR_THESE, $qualite, $etablissement);
                            $temoins[] = $acteur->getId();
                        }
                    }
                }
                $codirecteurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);
                foreach ($codirecteurs as $codirecteur) {
                    if (array_search($codirecteur->getId(), $temoins) === false) $this->getActeurService()->historise($codirecteur);
                }


                return $this->redirect()->toRoute('these/saisie', ['these' => $these->getId()], [], true);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }
}