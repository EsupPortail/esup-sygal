<?php

namespace These\Fieldset\Generalites;

use Application\Entity\Db\Source;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Laminas\Hydrator\HydratorInterface;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;

class GeneralitesHydrator implements HydratorInterface //extends DoctrineObject
{
    use EtablissementServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];

        $data['etablissement'] = ($object->getEtablissement()) ? $object->getEtablissement()->getId() : null;
        $data['titre'] = $object->getTitre();
        $data['doctorant'] = ($object->getDoctorant()) ? [
            'id' => $object->getDoctorant()->getId(),
            'label' => $object->getDoctorant()->getIndividu()->getPrenom() . ' ' . ($object->getDoctorant()->getIndividu()->getNomUsuel() ?? $object->getDoctorant()->getIndividu()->getNomPatronymique())
        ] : null;
        $data['discipline'] = $object->getLibelleDiscipline();

        $data["domaineHal"] = $object;
        //Autre (confidentialité)
        $data['confidentialite'] = ($object->getDateFinConfidentialite() !== null) ? 1 : 0;
        $data['fin-confidentialite'] = ($object->getDateFinConfidentialite()) ? $object->getDateFinConfidentialite()->format('Y-m-d') : null;
        $data['datePremiereInscription'] = ($object->getDatePremiereInscription()) ? $object->getDatePremiereInscription()->format('Y-m-d') : null;

        $data["titreAcces"] = $object;
        return $data;
    }

    /**
     * @param array $data
     * @param These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        /** @var Etablissement $etablissement */
        $etablissement = (isset($data['etablissement']) and trim($data['etablissement']) !== '') ? $this->getEtablissementService()->getRepository()->find(trim($data['etablissement'])) : null;
        if($etablissement) $object->setEtablissement($etablissement);

        $titre = (isset($data['titre']) and trim($data['titre'])) ? trim($data['titre']) : null;
        $object->setTitre($titre);

        /** @var Doctorant|null $doctorant */
        $doctorant = (isset($data['doctorant']) and isset($data['doctorant']['id']) and trim($data['doctorant']['id']) !== null) ? $this->doctorantService->getRepository()->find(trim($data['doctorant']['id'])) : null;
        if($doctorant) $object->setDoctorant($doctorant);

        $discipline = (isset($data['discipline']) and trim($data['discipline'])) ? trim($data['discipline']) : null;
        if($discipline) $object->setLibelleDiscipline($discipline);

        //date
        $conf = (isset($data['confidentialite']) and $data['confidentialite'] == true);
        $date = (!empty($data['fin-confidentialite']) && $conf) ? \DateTime::createFromFormat('Y-m-d', $data['fin-confidentialite']) : null;
        $object->setDateFinConfidentialite($date);

        $date = (isset($data['datePremiereInscription'])) ? \DateTime::createFromFormat('Y-m-d', $data['datePremiereInscription']) : null;
        if($date) $object->setDatePremiereInscription($date);

        $anneeUnivPremiereInscription = $date ? $this->anneeUnivService->fromDate($date) : null;

        if(!empty($object->getAnneeUniv1ereInscription())){
            $theseAnneeUnivPremiereInscription = $object->getAnneeUniv1ereInscription();
            if($anneeUnivPremiereInscription){
                $theseAnneeUnivPremiereInscription->setAnneeUniv($anneeUnivPremiereInscription->getPremiereAnnee());
                $theseAnneeUnivPremiereInscription->setThese($object);
            }else{
                $object->removeAnneesUniv1ereInscription($object->getAnneeUniv1ereInscription());
            }
        }else if($date){
            $theseAnneeUnivPremiereInscription = new TheseAnneeUniv();
            if($anneeUnivPremiereInscription){
                $theseAnneeUnivPremiereInscription->setAnneeUniv($anneeUnivPremiereInscription->getPremiereAnnee());
                $theseAnneeUnivPremiereInscription->setThese($object);
                $theseAnneeUnivPremiereInscription->setSource($this->sourceService->fetchApplicationSource());
                $theseAnneeUnivPremiereInscription->setSourceCode($this->sourceService->genereateSourceCode());
                $object->addAnneesUniv1ereInscription($theseAnneeUnivPremiereInscription);
            }
        }

        return $object; //parent::hydrate($data, $object);
    }
}