<?php

namespace These\Fieldset\Generalites;

use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;

class GeneralitesHydrator extends DoctrineObject
{
    use DoctorantServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * @param object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var These $object */
        $data = parent::extract($object);

        $data['doctorant'] = ($object->getDoctorant()) ? [
            'id' => $object->getDoctorant()->getId(),
            'label' => $object->getDoctorant()->getIndividu()->getPrenom() . ' ' . ($object->getDoctorant()->getIndividu()->getNomUsuel() ?? $object->getDoctorant()->getIndividu()->getNomPatronymique())
        ] : null;
        $data['discipline'] = $object->getDiscipline()?->getCode();
        $data["domaineHal"] = $object;

        //Confidentialité
        $data['confidentialite'] = ($object->getDateFinConfidentialite() !== null) ? 1 : 0;

        //Spécifités envisagées
        $data['paysCoTutelle'] = $object->getPaysCoTutelle()?->getId();

        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @return object
     */
    public function hydrate(array $data, object $object): object
    {
        //Nécessaire sinon Doctrine pense que c'est les données appartenant à un fieldset
        $data['doctorant'] = $data['doctorant']['id'] ?? null;

        //date
        $conf = (isset($data['confidentialite']) and $data['confidentialite'] == true);
        $data['dateFinConfidentialite'] = (!empty($data['dateFinConfidentialite']) && $conf) ? $data['dateFinConfidentialite'] : null;

        $titreAcces = (isset($data['titreAcces'])) ? $data['titreAcces'] : null;
        if ($titreAcces) {
            /** @var These $object */
            $titreAcces->setThese($object);
        }

        $date = (isset($data['datePremiereInscription'])) ? \DateTime::createFromFormat('Y-m-d', $data['datePremiereInscription']) : null;
        $anneeUnivPremiereInscription = $date ? $this->anneeUnivService->fromDate($date) : null;

        //si une première année d'inscription est déjà renseignée, on lui ajoute les données
        if ($object->getAnneeUniv1ereInscription()) {
            $theseAnneeUnivPremiereInscription = $object->getAnneeUniv1ereInscription();
            if ($anneeUnivPremiereInscription) {
                $theseAnneeUnivPremiereInscription->setAnneeUniv($anneeUnivPremiereInscription->getPremiereAnnee());
            } else {
                $object->removeAnneesUniv1ereInscription($object->getAnneeUniv1ereInscription());
            }
        } else if ($date) {
            $theseAnneeUnivPremiereInscription = new TheseAnneeUniv();
            if ($anneeUnivPremiereInscription) {
                $theseAnneeUnivPremiereInscription->setAnneeUniv($anneeUnivPremiereInscription->getPremiereAnnee());
                $theseAnneeUnivPremiereInscription->setThese($object);
                $theseAnneeUnivPremiereInscription->setSource($this->sourceService->fetchApplicationSource());
                $theseAnneeUnivPremiereInscription->setSourceCode($this->sourceService->genereateSourceCode());
                $object->addAnneesUniv1ereInscription($theseAnneeUnivPremiereInscription);
            }
        }

        if(isset($data['cotutelle']) && !$data['cotutelle']) {
            $data['etablissementCoTutelle'] = null;
            $data['paysCoTutelle'] = null;
        }

        //problème sinon lors de l'hydratation, objet TitreAcces a comme clé "financements" -> gros mystère
        if (array_key_exists("financements", $data)) {
            $data["titreAcces"] = $data["financements"];
            unset($data["financements"]);
        }

        return parent::hydrate($data, $object);
    }
}