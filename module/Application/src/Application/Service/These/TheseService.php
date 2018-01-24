<?php

namespace Application\Service\These;

use Application\Entity\Db\Attestation;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\MetadonneeThese;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Repository\TheseRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRechercheIndividu;
use Application\Entity\Db\VersionFichier;
use Application\Notification\ValidationRdvBuNotification;
use Application\Service\BaseService;
use Application\Service\Notification\NotificationServiceAwareInterface;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\UserContextService;
use Application\Service\Validation\ValidationServiceAwareInterface;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Util;
use UnicaenAuth\Entity\Db\UserInterface;

class TheseService extends BaseService implements ValidationServiceAwareInterface, NotificationServiceAwareInterface
{
    use ValidationServiceAwareTrait;
    use NotificationServiceAwareTrait;

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
     * @param QueryBuilder       $qb
     * @param UserContextService $userContext
     */
    public function decorateQbFromUserContext(QueryBuilder $qb, UserContextService $userContext)
    {
        /**
         * Un doctorant ne peut voir que ses thèses.
         */
        if ($role = $userContext->getSelectedRoleDoctorant()) {
            $qb
                ->andWhere('t.doctorant = :doctorant')
                ->setParameter('doctorant', $userContext->getIdentityDoctorant());
        }
        /**
         * Un directeur d'ED ne voient que les thèses concernant son ED.
         */
        elseif ($role = $userContext->getSelectedRoleDirecteurEcoleDoctorale()) {
            $ids = array_unique(array_map(function(EcoleDoctoraleIndividu $edi) {
                return $edi->getEcole()->getId();
            }, $userContext->getIdentityEcoleDoctoraleIndividu()));
            $qb
                ->andWhere($qb->expr()->in('ed.id', ':ids'))
                ->setParameter('ids', $ids);
        }
        /**
         * Un directeur d'UR ne voient que les thèses concernant son UR.
         */
        elseif ($role = $userContext->getSelectedRoleDirecteurUniteRecherche()) {
            $ids = array_unique(array_map(function(UniteRechercheIndividu $uri) {
                return $uri->getUniteRecherche()->getId();
            }, $userContext->getIdentityUniteRechercheIndividu()));
            $qb
                ->andWhere($qb->expr()->in('ur.id', ':ids'))
                ->setParameter('ids', $ids);
        }
        /**
         * Un directeur de thèse ne voient que les thèses qu'il dirige.
         */
        elseif ($role = $userContext->getSelectedRoleDirecteurThese()) {
            $people = $userContext->getIdentityLdap();
            $qb
                ->join('t.acteurs', 'adt')
                ->join('adt.individu', 'idt', Join::WITH, 'idt.sourceCode = :idtSourceCode')
                ->join('adt.role', 'rdt', Join::WITH, 'rdt.sourceCode = :rdtSourceCode')
                ->setParameter('idtSourceCode', $people->getSupannEmpId())
                ->setParameter('rdtSourceCode', Role::SOURCE_CODE_DIRECTEUR_THESE);

        }
    }

    /**
     * @param array              $wheres
     * @param array              $params
     * @param UserContextService $userContext
     */
    public function decorateSqlQueryFromUserContext(array &$wheres = [], array &$params = [], UserContextService $userContext)
    {
        /**
         * Un doctorant ne peut voir que ses thèses.
         */
        if ($role = $userContext->getSelectedRoleDoctorant()) {
            $wheres[] = 'NUMERO_ETUDIANT = :doctorant';
            $params['doctorant'] = $userContext->getIdentityDoctorant()->getSourceCode();
        }
        /**
         * Un directeur d'ED ne voient que les thèses concernant son ED.
         */
        elseif ($role = $userContext->getSelectedRoleDirecteurEcoleDoctorale()) {
            $sourceCodes = array_unique(array_map(function(EcoleDoctoraleIndividu $edi) {
                return $edi->getEcole()->getSourceCode();
            }, $userContext->getIdentityEcoleDoctoraleIndividu()));
            $wheres[] = sprintf('CODE_ED in (%s)', implode(',', $sourceCodes));
        }
        /**
         * Un directeur d'UR ne voient que les thèses concernant son UR.
         */
        elseif ($role = $userContext->getSelectedRoleDirecteurUniteRecherche()) {
            $sourceCodes = array_unique(array_map(function(UniteRechercheIndividu $uri) {
                return $uri->getUniteRecherche()->getSourceCode();
            }, $userContext->getIdentityUniteRechercheIndividu()));
            $wheres[] = sprintf('CODE_UR in (%s)', implode(',', $sourceCodes));
        }
        /**
         * Un directeur de thèse ne voient que les thèses qu'il dirige.
         */
        elseif ($role = $userContext->getSelectedRoleDirecteurThese()) {
            throw new LogicException("Cas non implémenté");
//            $people = $userContext->getIdentityLdap();
//            $qb
//                ->join('t.acteurs', 'adt')
//                ->join('adt.individu', 'idt', Join::WITH, 'idt.sourceCode = :idtSourceCode')
//                ->join('adt.role', 'rdt', Join::WITH, 'rdt.sourceCode = :rdtSourceCode')
//                ->setParameter('idtSourceCode', $people->getSupannEmpId())
//                ->setParameter('rdtSourceCode', Role::SOURCE_CODE_DIRECTEUR_THESE);
        }
    }

    /**
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
        $orc = '';
        if ($sqlCri != '') {
            $orc[] = '(' . $sqlCri . ')';
        }
        if ($criCode) {
            $orc[] = "(code_doctorant = '" . $criCode . "' OR code_ecole_doct = '" . $criCode . "')";
        }
        $orc = implode(' OR ', $orc);
        $sql .= ' AND (' . $orc . ') ';

        $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql);

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

        $this->entityManager->flush($metadonnee);
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

        $this->entityManager->flush($attestation);
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

        $this->entityManager->flush($attestation);
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

        $this->entityManager->flush($mel);
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

        $this->entityManager->flush($diffusion);
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
        $fichiersVA  = $these->getFichiersByVersion(VersionFichier::CODE_ARCHI,      false);
        $fichiersVAC = $these->getFichiersByVersion(VersionFichier::CODE_ARCHI_CORR, false);

        /** @var Fichier $fichier */
        if ($fichiersVAC->count() > 0) {
            $fichier = $fichiersVAC->first() ?: null;
        } else {
            $fichier = $fichiersVA->first() ?: null;
        }

        // il n'existe pas forcément de fichier en version d'archivage (si la version originale est testée archivable)
        if ($fichier === null) {
            return;
        }

        $fichier->setEstConforme($conforme);

        $this->entityManager->flush($fichier);
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

        $this->entityManager->flush($rdvBu);

        // si tout est renseigné, on valide automatiquement
        if ($rdvBu->isInfosBuSaisies()) {
            $this->validationService->validateRdvBu($these);
            $successMessage = "Validation enregistrée avec succès.";

            // notification (doctorant: à la 1ere validation seulement)
            $notification = new ValidationRdvBuNotification();
            $notification->setThese($these);
            $notification->setNotifierDoctorant(! $this->validationService->existsValidationRdvBuHistorisee($these));
            $this->notificationService->trigger($notification);
            $notificationLog = $this->notificationService->getMessage('<br>', 'info');

            $this->addMessage($successMessage, MessageAwareInterface::SUCCESS);
            $this->addMessage($notificationLog, MessageAwareInterface::INFO);
        }
    }
}