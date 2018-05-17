<?php

namespace Application\Service\These;

use Application\Entity\Db\Attestation;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\MetadonneeThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Repository\TheseRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Entity\UserWrapper;
use Application\Notification\ValidationRdvBuNotification;
use Application\QueryBuilder\TheseQueryBuilder;
use Application\Service\BaseService;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\UserContextService;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Util;
use UnicaenAuth\Entity\Db\UserInterface;

class TheseService extends BaseService
{
    use ValidationServiceAwareTrait;
    use NotificationServiceAwareTrait;
    use FichierServiceAwareTrait;
    use VariableServiceAwareTrait;

    /**
     * @return TheseRepository
     */
    public function getRepository()
    {
        /** @var TheseRepository $repo */
        $repo = $this->entityManager->getRepository(These::class);

        return $repo;
    }

    /**
     * @param TheseQueryBuilder  $qb
     * @param UserContextService $userContext
     */
    public function decorateQbFromUserContext(TheseQueryBuilder $qb, UserContextService $userContext)
    {
        $role = $userContext->getSelectedIdentityRole();

        if ($role->isTheseDependant()) {
            if ($role->isDoctorant()) {
                $qb
                    ->andWhere('t.doctorant = :doctorant')
                    ->setParameter('doctorant', $userContext->getIdentityDoctorant());
            }
            elseif ($role->isDirecteurThese()) {
                switch (true) {
                    case $identity = $userContext->getIdentityLdap():
                    case $identity = $userContext->getIdentityShib():
                        $userWrapper = UserWrapper::inst($identity);
                        break;
                    default:
                        throw new RuntimeException("Cas imprévu!");
                }
                $qb
                    ->join('t.acteurs', 'adt', Join::WITH, 'adt.role = :role')
                    ->join('adt.individu', 'idt', Join::WITH, 'idt.sourceCode like :idtSourceCode')
                    ->setParameter('idtSourceCode', '%::' . $userWrapper->getSupannId())
                    ->setParameter('role', $role);
            }
            // sinon role = membre jury
            // ...
        }

        elseif ($role->isStructureDependant()) {
            if ($role->isEtablissementDependant()) {
                /**
                 * On ne voit que les thèses de son établissement.
                 */
                $qb
                    ->andWhere('t.etablissement = :etab')
                    ->setParameter('etab', $role->getStructure()->getEtablissement());
            }
            elseif ($role->isEcoleDoctoraleDependant()) {
                /**
                 * On ne voit que les thèses concernant son ED.
                 */
                $qb
                    ->addSelect('ed')->join('t.ecoleDoctorale', 'ed')
                    ->andWhere('ed = :ed')
                    ->setParameter('ed', $role->getStructure()->getEcoleDoctorale());
            }
            elseif ($role->isUniteRechercheDependant()) {
                /**
                 * On ne voit que les thèses concernant son UR.
                 */
                $qb
                    ->addSelect('ur')->join('t.uniteRecherche', 'ur')
                    ->andWhere('ur = :ur')
                    ->setParameter('ur', $role->getStructure()->getUniteRecherche());
            }
        }
    }

    /**
     * Recherche de thèses à l'aide de la vue matérialisée MV_RECHERCHE_THESE.
     *
     * @param string  $text
     * @param integer $limit
     *
     * @return array
     */
    public function rechercherThese($text, $limit = 100)
    {
        if (strlen($text) < 2) return [];

        $text = Util::reduce($text);
        $criteres = explode(' ', $text);

        $sql     = sprintf('SELECT * FROM MV_RECHERCHE_THESE MV WHERE rownum <= %s ', (int)$limit);
        $sqlCri  = '';
        $criCode = 0;

        foreach ($criteres as $c) {
            if (! is_numeric($c)) {
                if ($sqlCri != '') {
                    $sqlCri .= ' AND ';
                }
                $sqlCri .= "haystack LIKE LOWER(q'[%" . $c . "%]')"; // q'[] : double les quotes
            } else {
                $criCode = (int) $c;
            }
        }
        $orc = [];
        if ($sqlCri != '') {
            $orc[] = '(' . $sqlCri . ')';
        }
        if ($criCode) {
            $orc[] = "(code_doctorant = '" . $criCode . "' OR code_ecole_doct = '" . $criCode . "')";
        }
        $orc = implode(' OR ', $orc);
        $sql .= ' AND (' . $orc . ') ';

        try {
            $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la requête", null, $e);
        }

        $theses = [];
        while ($r = $stmt->fetch()) {
            $theses[$r['CODE_THESE']] = [
                'code'           => $r['CODE_THESE'],
                'code-doctorant' => $r['CODE_DOCTORANT'],
            ];
        }

        return $theses;
    }

    /**
     * @param These           $these
     * @param MetadonneeThese $metadonnee
     */
    public function updateMetadonnees(These $these, MetadonneeThese $metadonnee)
    {
        if (! $metadonnee->getId()) {
            $metadonnee->setThese($these);
            $these->addMetadonnee($metadonnee);

            $this->entityManager->persist($metadonnee);
        }

        try {
            $this->entityManager->flush($metadonnee);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * @param These       $these
     * @param Attestation $attestation
     */
    public function updateAttestation(These $these, Attestation $attestation)
    {
        if (! $attestation->getId()) {
            $attestation->setThese($these);
            $these->addAttestation($attestation);

            $this->entityManager->persist($attestation);
        }

        try {
            $this->entityManager->flush($attestation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * Supprime l'Attestation (éventuelle) d'une These.
     *
     * @param These         $these Thèse concernée
     * @param UserInterface $destructeur Auteur de l'historisation, le cas échéant
     */
    public function deleteAttestation(These $these, UserInterface $destructeur = null)
    {
        $attestation = $these->getAttestation();
        if ($attestation === null) {
            return;
        }

        if ($destructeur) {
            $attestation->historiser($destructeur);
        } else {
            $these->removeAttestation($attestation);
            $this->entityManager->remove($attestation);
        }

        try {
            $this->entityManager->flush($attestation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * @param These       $these
     * @param Diffusion $mel
     */
    public function updateDiffusion(These $these, Diffusion $mel)
    {
        if (! $mel->getId()) {
            $mel->setThese($these);
            $these->addDiffusion($mel);

            $this->entityManager->persist($mel);
        }

        try {
            $this->entityManager->flush($mel);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * Supprime la Diffusion (éventuelle) d'une These.
     *
     * @param These         $these Thèse concernée
     * @param UserInterface $destructeur Auteur de l'historisation, le cas échéant
     */
    public function deleteDiffusion(These $these, UserInterface $destructeur = null)
    {
        $diffusion = $these->getDiffusion();
        if ($diffusion === null) {
            return;
        }

        if ($destructeur) {
            $diffusion->historiser($destructeur);
        } else {
            $these->removeDiffusion($diffusion);
            $this->entityManager->remove($diffusion);
        }

        try {
            $this->entityManager->flush($diffusion);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * Recherche le fichier de la version d'archivage de la thèse (corrigée, le cas échéant)
     * et modifie son témoin de conformité.
     *
     * @param These  $these
     * @param string $conforme "1" (conforme), "0" (non conforme) ou null (i.e. pas de réponse)
     */
    public function updateConformiteTheseRetraitee(These $these, $conforme = null)
    {
//        $fichiersVA  = $these->getFichiersByVersion(VersionFichier::CODE_ARCHI,      false);
//        $fichiersVAC = $these->getFichiersByVersion(VersionFichier::CODE_ARCHI_CORR, false);
        $fichiersVA  = $this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI);
        $fichiersVAC = $this->fichierService->getRepository()->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI_CORR);


        /** @var Fichier $fichier */
        if (! empty($fichiersVAC)) {
            $fichier = current($fichiersVAC) ?: null;
        } else {
            $fichier = current($fichiersVA) ?: null;
        }

        // il n'existe pas forcément de fichier en version d'archivage (si la version originale est testée archivable)
        if ($fichier === null) {
            return;
        }

        $fichier->setEstConforme($conforme);

        try {
            $this->entityManager->flush($fichier);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }
    }

    /**
     * @param These $these
     * @param RdvBu $rdvBu
     */
    public function updateRdvBu(These $these, RdvBu $rdvBu)
    {
        if (! $rdvBu->getId()) {
            $rdvBu->setThese($these);
            $these->addRdvBu($rdvBu);

            $this->entityManager->persist($rdvBu);
        }

        try {
            $this->entityManager->flush($rdvBu);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement", null, $e);
        }

        // si tout est renseigné, on valide automatiquement
        if ($rdvBu->isInfosBuSaisies()) {
            $this->validationService->validateRdvBu($these);
            $successMessage = "Validation enregistrée avec succès.";

            // notification BDD et BU + doctorant (à la 1ere validation seulement)
            $notifierDoctorant = ! $this->validationService->existsValidationRdvBuHistorisee($these);
            $notification = new ValidationRdvBuNotification();
            $notification->setThese($these);
            $notification->setNotifierDoctorant($notifierDoctorant);
            $this->notificationService->triggerValidationRdvBu($notification);
            $notificationLog = $this->notificationService->getMessage('<br>', 'info');
//
            $this->addMessage($successMessage, MessageAwareInterface::SUCCESS);
//            $this->addMessage($notificationLog, MessageAwareInterface::INFO);
        }
    }
}