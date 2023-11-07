<?php

namespace Admission\Hydrator;

use Admission\Entity\Db\Document;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Individu;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Verification;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Document\DocumentService;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Etudiant\EtudiantServiceAwareTrait;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Validation\ValidationServiceAwareTrait;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Entity\Db\Pays;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

/**
 * @author Unicaen
 */
class VerificationHydrator implements HydratorInterface
{
    use EtudiantServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use DocumentServiceAwareTrait;

    /**
     * @param Verification $object
     * @return array
     */
    public function extract($object): array
    {
        return [
            'commentaire' => $object->getCommentaire(),
            'etat' => $object->getEstComplet(),
            'etudiant' => $object->getEtudiant(),
            'financement' => $object->getFinancement(),
            'inscription' => $object->getInscription(),
            'document' => $object->getDocument(),
            'individu' => $object->getIndividu(),
        ];
    }

        /**
         * @param array $data
         * @param Verification $object
         * @return Verification
         */
        public function hydrate(array $data, $object)
    {
        $estComplet = isset($data['estComplet']) ? $data['estComplet']:null;
        if((isset($data['commentaire']) AND trim($data['commentaire']) !== '') && ($estComplet !== null && $estComplet == false)){
            $commentaire = trim($data['commentaire']);
        }else{
            $commentaire = null;
        }
        /** @var \Individu\Entity\Db\Individu|null $individu */
        $individu = (isset($data['individu']) AND trim($data['individu']) !== '')?$this->getIndividuService()->getRepository()->find($data['individu']):null;
        /** @var Etudiant|null $etudiant */
        $etudiant = (isset($data['etudiant']) AND trim($data['etudiant']) !== '')?$this->getEtudiantService()->getRepository()->find($data['etudiant']):null;
        /** @var Inscription|null $inscription */
        $inscription = (isset($data['inscription']) AND trim($data['inscription']['id']) !== '')?$this->getInscriptionService()->getRepository()->find($data['inscription']['id']):null;
        /** @var Financement|null $financement */
        $financement = (isset($data['financement']) AND trim($data['financement']['id']) !== '')?$this->getFinancementService()->getRepository()->find($data['financement']['id']):null;
        /** @var Document|null $document */
        $document = (isset($data['document']) AND trim($data['document']['id']) !== '')?$this->getDocumentService()->getRepository()->find($data['document']['id']):null;

        $object->setCommentaire($commentaire);
        $object->setEstComplet($estComplet);
        $object->setEtudiant($etudiant);
        $object->setIndividu($individu);
        $object->setInscription($inscription);
        $object->setFinancement($financement);
        $object->setDocument($document);

        return $object;
    }
}