<?php

namespace These\Form\TheseSaisie;

use Application\Entity\Db\Role;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;

/**
 * @deprecated
 */
class TheseSaisieHydrator implements HydratorInterface
{
    use ActeurServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * @param These $object
     * @return array|mixed[]
     */
    public function extract(object $object): array
    {
        /** @var Acteur $directeur */
        $directeur = ($object->getId() !== null) ? current($this->getActeurService()->getRepository()->findActeursByTheseAndRole($object, Role::CODE_DIRECTEUR_THESE)) : null;
        $codirecteurs = ($object->getId() !== null) ? $this->getActeurService()->getRepository()->findActeursByTheseAndRole($object, Role::CODE_CODIRECTEUR_THESE) : [];
        usort($codirecteurs, function (Acteur $a, Acteur $b) {
            return $a->getIndividu()->getNomComplet() > $b->getIndividu()->getNomComplet();
        });

        $data = [];

        //Bloc d'identitfication
        $data['titre'] = $object->getTitre();
        $data['doctorant'] = ($object->getDoctorant()) ? [
            'id' => $object->getDoctorant()->getId(),
            'label' => $object->getDoctorant()->getIndividu()->getPrenom() . ' ' . ($object->getDoctorant()->getIndividu()->getNomUsuel() ?? $object->getDoctorant()->getIndividu()->getNomPatronymique())
        ] : null;
        $data['discipline'] = $object->getLibelleDiscipline();

        //structure
        $data['unite-recherche'] = ($object->getUniteRecherche()) ? $object->getUniteRecherche()->getId() : null;
        $data['ecole-doctorale'] = ($object->getEcoleDoctorale()) ? $object->getEcoleDoctorale()->getId() : null;
        $data['etablissement'] = ($object->getEtablissement()) ? $object->getEtablissement()->getId() : null;

        //direction et co-encadrement
        if ($directeur) {
            $data['directeur-individu'] = ['id' => $directeur->getIndividu()->getId(), 'label' => $directeur->getIndividu()->getNomComplet()];
            $data['directeur-qualite'] = ($qualite = $this->getQualiteService()->findQualiteByLibelle($directeur->getQualite())) ? $qualite->getId() : null;
            $data['directeur-etablissement'] = $directeur->getEtablissement()->getId();
        }
        $position = 1;
        foreach ($codirecteurs as $codirecteur) {
            $data['codirecteur' . $position . '-individu'] = ["id" => $codirecteur->getIndividu()->getId(), "label" => $codirecteur->getIndividu()->getNomComplet()];
            $data['codirecteur' . $position . '-qualite'] = ($qualite = $this->getQualiteService()->findQualiteByLibelle($codirecteur->getQualite())) ? $qualite->getId() : null;;
            $data['codirecteur' . $position . '-etablissement'] = $codirecteur->getEtablissement()->getId();
            $position++;
        }

        //Autre (confidentialité)
        $data['confidentialite'] = ($object->getDateFinConfidentialite() !== null) ? 1 : 0;
        $data['fin-confidentialite'] = ($object->getDateFinConfidentialite()) ? $object->getDateFinConfidentialite()->format('Y-m-d') : null;

        $data['domaineHal'] = $object;

        return $data;
    }

    /**
     * @param array $data
     * @param These $object
     * @return These|void
     */
    public function hydrate(array $data, object $object)
    {
        $titre = (isset($data['titre']) and trim($data['titre'])) ? trim($data['titre']) : null;
        /** @var Doctorant|null $doctorant */
        $doctorant = (isset($data['doctorant']) and isset($data['doctorant']['id']) and trim($data['doctorant']['id']) !== null) ? $this->doctorantService->getRepository()->find(trim($data['doctorant']['id'])) : null;
        $discipline = (isset($data['discipline']) and trim($data['discipline'])) ? trim($data['discipline']) : null;
        // decaller pour le moment dans le controller
        /** @var UniteRecherche|null $uniteRecherche */
        $uniteRecherche = (isset($data['unite-recherche']) and trim($data['unite-recherche']) !== '') ? $this->getUniteRechercheService()->getRepository()->find(trim($data['unite-recherche'])) : null;
        /** @var EcoleDoctorale|null $ecoleDoctorale */
        $ecoleDoctorale = (isset($data['ecole-doctorale']) and trim($data['ecole-doctorale']) !== '') ? $this->getEcoleDoctoraleService()->getRepository()->find(trim($data['ecole-doctorale'])) : null;
        /** @var Etablissement $etablissement */
        $etablissement = (isset($data['etablissement']) and trim($data['etablissement']) !== '') ? $this->getEtablissementService()->getRepository()->find(trim($data['etablissement'])) : null;

        //date
        $conf = (isset($data['confidentialite']) and $data['confidentialite'] == true);
        $date = (isset($data['fin-confidentialite'])) ? DateTime::createFromFormat('Y-m-d', $data['fin-confidentialite']) : null;

        //Bloc d'identitfication
        $object->setTitre($titre);
        $object->setDoctorant($doctorant);
        $object->setLibelleDiscipline($discipline);

        //Structures
        $object->setUniteRecherche($uniteRecherche);
        $object->setEcoleDoctorale($ecoleDoctorale);
        $object->setEtablissement($etablissement);

        //dir et codirs
        // !!! Gerer dans le controller (? car non directement lié à l'objet ?)

        // Autre (conf)
        $object->setDateFinConfidentialite($conf ? $date : null);

        return $object;
    }


}