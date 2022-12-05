<?php

namespace StepStar\Service\Xml;

use These\Entity\Db\Diffusion;
use Application\Entity\Db\MailConfirmation;
use These\Entity\Db\MetadonneeThese;
use Application\Entity\Db\Role;
use DateInterval;
use DateTime;
use Individu\Entity\Db\Individu;
use InvalidArgumentException;
use StepStar\Exception\XmlServiceException;
use Webmozart\Assert\Assert;
use XMLWriter;

class XmlService
{
    // doctorant
    const CODE_ETUDIANT = 'CODE_ETUDIANT';
    const CODE_INE = 'CODE_INE';
    const NOM_ETUDIANT = 'NOM_ETUDIANT';
    const NOM_ETUDIANT_USUEL = 'NOM_ETUDIANT_USUEL';
    const PRENOM_ETUDIANT = 'PRENOM_ETUDIANT';
    const EMAIL_PERSO_ETUDIANT = 'EMAIL_PERSO_ETUDIANT';
    const EMAIL_INSTITUTION_ETUDIANT = 'EMAIL_INSTITUTION_ETUDIANT';
    const DATE_NAISSANCE_ETUDIANT = 'DATE_NAISSANCE_ETUDIANT';
    const SEXE_ETUDIANT = 'SEXE_ETUDIANT';
    const NATIONALITE_DOCTORANT = 'NATIONALITE_DOCTORANT';
    const ADRESSE_DOCTORANT = 'ADRESSE_DOCTORANT';
    const CODE_POSTAL_DOCTORANT = 'CODE_POSTAL_DOCTORANT';
    const VILLE_DOCTORANT = 'VILLE_DOCTORANT';
    const PAYS_DOCTORANT = 'PAYS_DOCTORANT';
    const TELEPHONE_DOCTORANT = 'TELEPHONE_DOCTORANT';

    // etablissement
    const CODE_ETAB_SOUT = 'CODE_ETAB_SOUT';
    const LIBELLE_ETAB_SOUT = 'LIBELLE_ETAB_SOUT';

    // inscription
    const DATE_1ERE_INSCR_DOCTORAT = 'DATE_1ERE_INSCR_DOCTORAT';
    const DATE_1ERE_INSCR_ETAB = 'DATE_1ERE_INSCR_ETAB';

    // diplome
    const MENTION_DIPLOME = 'MENTION_DIPLOME';

    // these
    const THESE_ID = 'THESE_ID';
    const DISCIPLINE = 'DISCIPLINE';
    const AVIS_DE_REPRODUCTION = 'AVIS_DE_REPRODUCTION';
    const DATE_DEBUT_CONFIDENTIALITE = 'DATE_DEBUT_CONFIDENTIALITE'; // fictive donc calculée
    const DATE_FIN_CONFIDENTIALITE = 'DATE_FIN_CONFIDENTIALITE';
    const DATE_DEBUT_EMBARGO = 'DATE_DEBUT_EMBARGO'; // fictive donc calculée
    const DATE_FIN_EMBARGO = 'DATE_FIN_EMBARGO';
    const DATE_ABANDON = 'DATE_ABANDON';
    const DATE_TRANSFERT = 'DATE_TRANSFERT';
    const THESE_SUR_TRAVAUX = 'THESE_SUR_TRAVAUX';
    const NNT = 'NNT';

    // métadonnées
    const TITRE = 'TITRE';
    const TITRE_LANGUE = 'TITRE_LANGUE';
    const TITRE_TRADUIT = 'TITRE_TRADUIT';
    const TITRE_TRADUIT_LANGUE = 'TITRE_TRADUIT_LANGUE';
    const RESUME_FRANCAIS = 'RESUME_FRANCAIS';
    const RESUME_ANGLAIS = 'RESUME_ANGLAIS';
    const MOTS_CLES_FRANCAIS_ = 'MOTS_CLES_FRANCAIS_';
    const MOTS_CLES_ANGLAIS_ = 'MOTS_CLES_ANGLAIS_';
    const MOTS_CLES_RAMEAU_ = 'MOTS_CLES_RAMEAU_';

    // financement
    const CONTRAT_DOCTORAL = 'CONTRAT_DOCTORAL';

    // soutenance
    const DATE_SOUTENANCE = 'DATE_SOUTENANCE';
    const DATE_PREVISION_SOUTENANCE = 'DATE_PREVISION_SOUTENANCE';
    const LIEU_SOUTENANCE = 'LIEU_SOUTENANCE';
    const HEURE_SOUTENANCE = 'HEURE_SOUTENANCE';
    const SOUTENANCE_PUB_OU_HUIS = 'SOUTENANCE_PUB_OU_HUIS';

    // cotutelle
    const CODE_ETAB_COTUTELLE = 'CODE_ETAB_COTUTELLE';
    const LIBELLE_ETAB_COTUTELLE = 'LIBELLE_ETAB_COTUTELLE';

    // ed
    const PPN_ECOLE_DOCTORALE = 'PPN_ECOLE_DOCTORALE';
    const CODE_ECOLE_DOCTORALE = 'CODE_ECOLE_DOCTORALE';
    const LIBELLE_ECOLE_DOCTORALE = 'LIBELLE_ECOLE_DOCTORALE';

    // ur
    const PPN_EQUIPE_RECHERCHE_1 = 'PPN_EQUIPE_RECHERCHE_1';
    const IDHAL_EQUIPE_RECHERCHE_1 = 'IDHAL_EQUIPE_RECHERCHE_1';
    const CODE_EQUIPE_RECHERCHE_1 = 'CODE_EQUIPE_RECHERCHE_1';
    const LIBELLE_EQUIPE_RECHERCHE_1 = 'LIBELLE_EQUIPE_RECHERCHE_1';
    const CONVENTION_CIFRE_1 = 'CONVENTION_CIFRE_1';

    // partenaire recherche : établissement
    const PPN_PARTENAIRE_RECHERCHE_ETAB = 'PPN_PARTENAIRE_RECHERCHE_ETAB';
    const CODE_PARTENAIRE_RECHERCHE_ETAB = 'CODE_PARTENAIRE_RECHERCHE_ETAB';
    const LIBELLE_PARTENAIRE_RECHERCHE_ETAB = 'LIBELLE_PARTENAIRE_RECHERCHE_ETAB';
    const CONVENTION_CIFRE_ETAB = 'CONVENTION_CIFRE_ETAB';

    // direction
    const NOM_DIRECTEUR = 'NOM_DIRECTEUR';
    const PRENOM_DIRECTEUR = 'PRENOM_DIRECTEUR';
    const NOM_CODIRECTEUR = 'NOM_CODIRECTEUR';
    const PRENOM_CODIRECTEUR = 'PRENOM_CODIRECTEUR';

    // jury
    const MEMBRE_JURY = 'MEMBRE_JURY';
    const PRESIDENT_JURY = 'PRESIDENT_JURY';
    const RAPPORTEUR_JURY = 'RAPPORTEUR_JURY';

    const DOMAINE = 'DOMAINE';

    private XMLWriter $writer;

    /**
     * @var array Table de correspondance Code discipline SISE => [Codes domaines OAI]
     */
    private array $codesSiseDisciplinesToCodesDomaines = [];

    /**
     * @var array Codes des types de financement identifiant un contrat doctoral
     */
    private array $codesTypeFinancContratDoctoral = [];

    /**
     * @var array Codes des types de financement identifiant une convention CIFRE
     */
    private array $codesOrigFinancCifre = [];

    /**
     * @var XmlServiceException[] Liste des exceptions rencontrées lors de la génération XML.
     */
    private array $exceptions = [];

    /**
     * @var array[]
     */
    private array $theses;

    /**
     * @var array[]
     */
    private array $rejectedTheses;

    /**
     * @var array[]
     */
    private array $validTheses;

    /**
     * @param XMLWriter $writer
     */
    public function setWriter(XMLWriter $writer): void
    {
        $this->writer = $writer;
    }

    /**
     * @param array[] $codesSiseDisciplinesToCodesDomaines
     */
    public function setCodesSiseDisciplinesToCodesDomaines(array $codesSiseDisciplinesToCodesDomaines): void
    {
        $this->codesSiseDisciplinesToCodesDomaines = $codesSiseDisciplinesToCodesDomaines;
    }

    /**
     * @param string[] $codesTypeFinancContratDoctoral
     */
    public function setCodesTypeFinancContratDoctoral(array $codesTypeFinancContratDoctoral): void
    {
        $this->codesTypeFinancContratDoctoral = $codesTypeFinancContratDoctoral;
    }

    /**
     * @param string[] $codesOrigFinancCifre
     */
    public function setCodesOrigFinancCifre(array $codesOrigFinancCifre): void
    {
        $this->codesOrigFinancCifre = $codesOrigFinancCifre;
    }

    /**
     * @param array[] $theses
     */
    public function setTheses(array $theses): void
    {
        $this->theses = $theses;
    }

    /**
     * @return XmlServiceException[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return array[] ['these' => array, 'reason' => string]
     */
    public function getRejectedTheses(): array
    {
        return $this->rejectedTheses;
    }

    /**
     * Exporte dans un fichier XML un ensemble de thèses.
     *
     * Le fichier XML ainsi généré pourra servir d'entrée pour la transformation XSLT en fichiers TEF,
     * cf. {@see generateTefFilesFromXml()}.
     *
     * @param string $xmlFilePath
     * @throws \StepStar\Exception\XmlServiceException
     */
    public function exportThesesToXml(string $xmlFilePath)
    {
        if (file_exists($xmlFilePath)) {
            throw new XmlServiceException("Le fichier destination spécifié existe déjà : " . $xmlFilePath);
        }

        $validTheses = $this->validateTheses();

        $xmlContent = $this->generateXmlContentForTheses($validTheses);
        file_put_contents($xmlFilePath, $xmlContent);
    }

    /**
     * @param array[] $theses
     * @return string
     * @throws \StepStar\Exception\XmlServiceException
     */
    private function generateXmlContentForTheses(array $theses): string
    {
        $data = $this->createThesesElementsData($theses);

        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->setIndentString('  ');
        $this->writer->startDocument('1.0', 'UTF-8', 'yes');
        $this->writer->startElement('THESES');
        foreach ($data as $datum) {
            $this->writer->startElement('THESE');
            foreach ($datum as $tag => $value) {
                if (is_array($value)) {//var_dump($tag, $value);
                    foreach ($value as $valueItems) {
                        $this->writer->startElement($tag);
                        foreach ($valueItems as $k => $v) {
                            $this->writer->writeAttribute($k, $v);
                        }
                        $this->writer->endElement();
                    }
                } else {
                    $this->writer->startElement($tag);
                    $this->writer->text($value);
                    $this->writer->endElement();
                }
            }
            $this->writer->endElement(); // THESE
        }
        $this->writer->endElement(); // THESES
        $this->writer->endDocument();

        return $this->writer->outputMemory();
    }

    /**
     * @throws \StepStar\Exception\XmlServiceException
     */
    private function createThesesElementsData(array $theses): array
    {
        $this->exceptions = [];
        $data = [];

        foreach ($theses as $these) {
            $d = $this->createTheseElementData($these);
            try {
                $this->validateTheseElementData($d);
                $data[] = $d;
            } catch (InvalidArgumentException $e) {
                $theseId = $these['id'];
                $this->exceptions[$theseId] = new XmlServiceException(
                    "La thèse {$theseId} a été écartée car elle n'est pas valide : " . $e->getMessage()
                );
            }
        }

        if (empty($data)) {
            throw new XmlServiceException("Impossible de poursuivre, toutes les thèses spécifiées ont été écartées !");
        }

        return $data;
    }

    /**
     * Retourne les seules thèses valides parmi les thèses courantes.
     *
     * Les thèses invalides sont collectées.
     *
     * @return array
     */
    private function validateTheses(): array
    {
        $this->rejectedTheses = [];

        $validTheses = [];
        foreach ($this->theses as $these) {
            try {
                $this->validateThese($these);
                $validTheses[] = $these;
            } catch (\Webmozart\Assert\InvalidArgumentException $e) {
                $this->rejectedTheses[] = ['these' => $these, 'reason' => $e->getMessage()];
            }
        }

        return $validTheses;
    }

    /**
     * @param array $these
     */
    private function validateThese(array $these)
    {
        Assert::notNull($these['ecoleDoctorale'], sprintf("La thèse %d n'est liée à aucune ED", $these['id']));
        Assert::notNull($these['uniteRecherche'], sprintf("La thèse %d n'est liée à aucune UR", $these['id']));
    }

    /**
     * @param array $data
     */
    private function validateTheseElementData(array $data)
    {
//        Assert::false(
//            isset($data[self::DATE_PREVISION_SOUTENANCE]) && isset($data[self::DATE_ABANDON]),
//            "La thèse possède à la fois une date prévisionnlle de soutenance et une date d'abandon, ce qui apparemment fait planter le web service"
//        );
    }

    /**
     * @param array $these
     * @return array
     */
    private function createTheseElementData(array $these): array
    {
        $directeur = null;
        $codirecteur = null;
        $presidentJury = null;
        $membresJury = [];
        $rapporteurs = [];
        foreach ($these['acteurs'] as $acteur) {
            if ($acteur['role']['code'] === Role::CODE_DIRECTEUR_THESE) {
                $directeur = $acteur['individu'];
            }
            elseif ($acteur['role']['code'] === Role::CODE_CODIRECTEUR_THESE) {
                $codirecteur = $acteur['individu'];
            }
            elseif ($acteur['role']['code'] === Role::CODE_MEMBRE_JURY) {
                $membresJury[] = $acteur['individu'];
            }
            elseif ($acteur['role']['code'] === Role::CODE_PRESIDENT_JURY) {
                $presidentJury = $acteur['individu'];
            }
            elseif ($acteur['role']['code'] === Role::CODE_RAPPORTEUR_JURY) {
                $rapporteurs[] = $acteur['individu'];
            }
        }

        $emailPersoDoctorant = $this->extractEmailPersoFromThese($these);
        $emailInstitutionDoctorant = $this->extractEmailInstitutionFromThese($these);
        if ($emailPersoDoctorant === null && $emailInstitutionDoctorant === null) {
            $emailPersoDoctorant = 'email.inconnu@mail.fr';
        }

        /** @var \DateTime $date1ereInscr */
        $date1ereInscr = $these['datePremiereInscription']; //'2020-01-01',

        $dataReelleSoutenance = $these['dateSoutenance'];
        $dataPrevisSoutenance = $these['datePrevisionSoutenance'];
        if ($dataPrevisSoutenance === null && $date1ereInscr !== null) {
            $dataPrevisSoutenance = clone $date1ereInscr;
            $dataPrevisSoutenance->add(new DateInterval('P3Y')); // + 3 ans
        }

        $domaine = $this->extractDomaineFromThese($these);
        $contratDoctoral = $this->extractContratDoctoralFromThese($these) ? 'oui' : 'non';
        $conventionCifre = $this->extractConventionCifreFromThese($these) ? 'oui' : 'non';

        $metadonnees = current($these['metadonnees']) ?: null;

        $dateFinConfidentialite = $these['dateFinConfidentialite'] ?? null;
        $dateDebutConfidentialite = null;
        if ($dateFinConfidentialite !== null) {
            $dateDebutConfidentialite = ($dataReelleSoutenance ?: $dataPrevisSoutenance) ?: new DateTime();
        }

        $diffusion = current($these['miseEnLignes']) ?: null;

        $dureeEmbargo = $diffusion['autorisEmbargoDuree'] ?? null;
        $dateDebutEmbargo = $dateFinEmbargo = null;
        if ($dureeEmbargo !== null) {
            $interval = Diffusion::EMBARGO_DUREES_PHP_INTERVALS[$dureeEmbargo];
            if ($dateFinConfidentialite !== null) {
                // en cas de confidentialité, prendre la date de fin de confidentialité disponible
                $dateDebutEmbargo = $dateFinConfidentialite;
            } else {
                $dateDebutEmbargo = ($dataReelleSoutenance ?: $dataPrevisSoutenance) ?: new DateTime();
            }
            $dateFinEmbargo = (clone $dateDebutEmbargo)->add(new DateInterval($interval));
        }

        $data = [
            // doctorant
            self::CODE_ETUDIANT => $these['doctorant']['individu']['supannId'], // todo : ou l'id ?
            self::CODE_INE => $these['doctorant']['ine'],
            self::NOM_ETUDIANT => $these['doctorant']['individu']['nomPatronymique'] ?: $these['doctorant']['individu']['nomUsuel'],
            self::NOM_ETUDIANT_USUEL => $these['doctorant']['individu']['nomUsuel'],
            self::PRENOM_ETUDIANT => $these['doctorant']['individu']['prenom1'],
            self::EMAIL_PERSO_ETUDIANT => $emailPersoDoctorant,
            self::EMAIL_INSTITUTION_ETUDIANT => $emailInstitutionDoctorant,
            self::DATE_NAISSANCE_ETUDIANT => $this->formatDate($these['doctorant']['individu']['dateNaissance']),
            self::SEXE_ETUDIANT => $this->formatSexe($these['doctorant']['individu']['civilite']),
            self::NATIONALITE_DOCTORANT => $these['doctorant']['individu']['paysNationalite']['codeIsoAlpha2'] ?? null,
            self::TELEPHONE_DOCTORANT => null, // pas dispo
            self::ADRESSE_DOCTORANT => null, // pas dispo
            self::CODE_POSTAL_DOCTORANT => null, // pas dispo
            self::VILLE_DOCTORANT => null, // pas dispo
            self::PAYS_DOCTORANT => null, // pas dispo

            // inscription
            self::DATE_1ERE_INSCR_DOCTORAT => $this->formatDate($date1ereInscr),
            self::DATE_1ERE_INSCR_ETAB => $this->formatDate($these['dateTransfert']) ?: $this->formatDate($date1ereInscr),

            // diplome
            self::MENTION_DIPLOME => "Doctorat", // constante

            // thèse
            self::THESE_ID => $these['id'],
            self::DISCIPLINE => $these['libelleDiscipline'],
            self::AVIS_DE_REPRODUCTION => $these['correctionAutorisee'], // todo : ce n'est pas l'avis de reproduction !
            self::DATE_DEBUT_CONFIDENTIALITE => $this->formatDate($dateDebutConfidentialite),
            self::DATE_FIN_CONFIDENTIALITE => $this->formatDate($dateFinConfidentialite),
            self::DATE_DEBUT_EMBARGO => $this->formatDate($dateDebutEmbargo),
            self::DATE_FIN_EMBARGO => $this->formatDate($dateFinEmbargo),
            self::DATE_ABANDON => $this->formatDate($these['dateAbandon']),
            self::DATE_TRANSFERT => $this->formatDate($these['dateTransfert']),
            self::DOMAINE => $domaine,
            self::THESE_SUR_TRAVAUX => 'non',
            self::NNT => $diffusion['nnt'] ?? null,
//            self::NNT => '2013FOR31234',

            // métadonnées
            self::TITRE => $metadonnees['titre'] ?: $these['titre'],
            self::TITRE_LANGUE => $langue = $metadonnees['langue'] ?? 'fr',
            self::TITRE_TRADUIT => $metadonnees['titreAutreLangue'],
            self::TITRE_TRADUIT_LANGUE => $langue === 'fr' ? 'en' : 'fr',
            self::RESUME_FRANCAIS => $this->sanitizeXML($metadonnees['resume'] ?? ''),
            self::RESUME_ANGLAIS => $this->sanitizeXML($metadonnees['resumeAnglais'] ?? ''),

            // contrat doctoral
            self::CONTRAT_DOCTORAL => $contratDoctoral,

            // soutenance
            self::DATE_SOUTENANCE => $this->formatDate($dataReelleSoutenance), // https://documentation.abes.fr/aidetheses/index.html#SuiviThesePreparation
            self::DATE_PREVISION_SOUTENANCE => $this->formatDate($dataPrevisSoutenance),
            self::LIEU_SOUTENANCE => null,
            self::HEURE_SOUTENANCE => null,
            self::SOUTENANCE_PUB_OU_HUIS => null,

            // cotutelle
            self::CODE_ETAB_COTUTELLE => 'xxxxx', // todo : kezako ?
            self::LIBELLE_ETAB_COTUTELLE => $these['libelleEtabCotutelle'],
        ];

        // etablissement (possiblement substitué)
        $dataStructureEtablissement = $this->extractStructureEtablissement($these);
        $data[self::CODE_ETAB_SOUT] = $dataStructureEtablissement['code'];
        $data[self::LIBELLE_ETAB_SOUT] = $dataStructureEtablissement['libelle'];

        // ed (possiblement substituée)
        $dataStructureEcoleDoctorale = $this->extractStructureEcoleDoctorale($these);
        $data[self::PPN_ECOLE_DOCTORALE] = $dataStructureEcoleDoctorale['idRef'] ?? null;
        $data[self::CODE_ECOLE_DOCTORALE] = $dataStructureEcoleDoctorale['code'];
        $data[self::LIBELLE_ECOLE_DOCTORALE] = $dataStructureEcoleDoctorale['libelle'];

        // ur (possiblement substituée)
        $dataStructureUniteRecherche = $this->extractStructureUniteRecherche($these);
        $data[self::CONVENTION_CIFRE_1] = $conventionCifre;
        $data[self::PPN_EQUIPE_RECHERCHE_1] = $dataStructureUniteRecherche['idRef'] ?? null;
        $data[self::IDHAL_EQUIPE_RECHERCHE_1] = $dataStructureUniteRecherche['idHal'] ?? null;
        $data[self::CODE_EQUIPE_RECHERCHE_1] = $dataStructureUniteRecherche['code'];
        $data[self::LIBELLE_EQUIPE_RECHERCHE_1] = $dataStructureUniteRecherche['libelle'];

        // ajout de l'établissement comme partenaire de recherche typé "Autre"
        $data[self::CONVENTION_CIFRE_ETAB] = $conventionCifre;
        $data[self::PPN_PARTENAIRE_RECHERCHE_ETAB] = $dataStructureEtablissement['idRef'] ?? null;
        $data[self::CODE_PARTENAIRE_RECHERCHE_ETAB] = $dataStructureEtablissement['code'];
        $data[self::LIBELLE_PARTENAIRE_RECHERCHE_ETAB] = $dataStructureEtablissement['libelle'];

        // mots clefs
        $motsClesLibresFrancais = $metadonnees['motsClesLibresFrancais'] ?? null;
        $motsClesLibresAnglais = $metadonnees['motsClesLibresAnglais'] ?? null;
        $motsClesRameau = $this->extractMotsClefsRameauFromThese($these);
        foreach ($this->explodeMotsClefs($motsClesLibresFrancais) as $i => $mot) {
            $index = $i + 1;
            $data[self::MOTS_CLES_FRANCAIS_ . $index] = $mot;
        }
        foreach ($this->explodeMotsClefs($motsClesLibresAnglais) as $i => $mot) {
            $index = $i + 1;
            $data[self::MOTS_CLES_ANGLAIS_ . $index] = $mot;
        }
        foreach ($this->explodeMotsClefs($motsClesRameau) as $i => $mot) {
            $index = $i + 1;
            $data[self::MOTS_CLES_RAMEAU_ . $index] = $mot;
        }

        // direction
        if ($directeur !== null) {
            $data[self::NOM_DIRECTEUR] = $directeur['nomUsuel'];
            $data[self::PRENOM_DIRECTEUR] = $directeur['prenom1'];
        }
        if ($codirecteur !== null) {
            $data[self::NOM_CODIRECTEUR] = $codirecteur['nomUsuel'];
            $data[self::PRENOM_CODIRECTEUR] = $codirecteur['prenom1'];
        }

        // jury
        if (!empty($membresJury)) {
            $jury = [];
            foreach ($membresJury as $individu) {
                $jury[] = [
                    'prenom' => $individu['prenom1'],
                    'nom' => $individu['nomUsuel'],
                ];
            }
            $data[self::MEMBRE_JURY] = $jury;
        }

        // président de jury
        if ($presidentJury !== null) {
            $array = [
                [
                    'prenom' => $presidentJury['prenom1'],
                    'nom' => $presidentJury['nomUsuel'],
                ],
            ];
            $data[self::PRESIDENT_JURY] = $array;
        }

        // rapporteurs
        if (!empty($rapporteurs)) {
            $array = [];
            foreach ($rapporteurs as $individu) {
                $array[] = [
                    'prenom' => $individu['prenom1'],
                    'nom' => $individu['nomUsuel'],
                ];
            }
            $data[self::RAPPORTEUR_JURY] = $array;
        }

        return array_filter($data);
    }

    private function extractStructureEtablissement(array $these): array
    {
        return
            $these['etablissement']['structure']['structureSubstituante'][0] ??
            $these['etablissement']['structure'];
    }

    private function extractStructureEcoleDoctorale(array $these): array
    {
        return
            $these['ecoleDoctorale']['structure']['structureSubstituante'][0] ??
            $these['ecoleDoctorale']['structure'];
    }

    private function extractStructureUniteRecherche(array $these): array
    {
        return
            $these['uniteRecherche']['structure']['structureSubstituante'][0] ??
            $these['uniteRecherche']['structure'];
    }

    private function explodeMotsClefs(?string $motsClefs): array
    {
        if (!$motsClefs) {
            return [];
        }

        return array_map('trim', explode(MetadonneeThese::SEPARATEUR_MOTS_CLES, $motsClefs));
    }

    private function extractDomaineFromThese(array $these)
    {
        $codeSiseDiscipline = $these['codeSiseDiscipline'] ?? null;
        if (! $codeSiseDiscipline) {
            return null;
        }

        Assert::integerish($codeSiseDiscipline, "Le code discipline SISE est sensé être un entier");

        if (!isset($this->codesSiseDisciplinesToCodesDomaines[(int)$codeSiseDiscipline])) {
            return null;
        }

        $domaines = (array) $this->codesSiseDisciplinesToCodesDomaines[(int)$codeSiseDiscipline];

        // pour l'instant, on retourne le 1er domaine de la liste
        return reset($domaines);
    }

    private function extractContratDoctoralFromThese(array $these): bool
    {
        foreach ($these['financements'] as $f) {
            if (in_array($f['codeTypeFinancement'], $this->codesTypeFinancContratDoctoral)) {
                return true;
            }
        }

        return false;
    }

    private function extractConventionCifreFromThese(array $these): bool
    {
        foreach ($these['financements'] as $f) {
            $origFinanc = $f['origineFinancement'] ?? null;
            if ($origFinanc === null) {
                continue;
            }
            if (in_array($origFinanc['code'], $this->codesOrigFinancCifre)) {
                return true;
            }
        }

        return false;
    }

    private function extractEmailInstitutionFromThese(array $these): ?string
    {
        return $these['doctorant']['individu']['email'] ?? null;
    }

    private function extractEmailPersoFromThese(array $these): ?string
    {
        foreach ((array) $these['mailsConfirmations'] as $mailConfirmation) {
            if ($mailConfirmation['etat'] === MailConfirmation::CONFIRME) {
                return $mailConfirmation['email'];
            }
        }

        return null;
    }

    private function extractMotsClefsRameauFromThese(array $these): ?string
    {
        foreach ((array) $these['rdvBus'] as $rdvBu) {
            return $rdvBu['motsClesRameau'] ?? null;
        }

        return null;
    }

    private function formatSexe(string $civilite): ?string
    {
        return [
            Individu::CIVILITE_M => 'M',
            Individu::CIVILITE_MME => 'F',
        ][$civilite] ?? null;
    }

    private function formatDate(DateTime $date = null): ?string
    {
        return $date ? $date->format('Y-m-d') : null;
    }

    /**
     * Removes invalid characters from a UTF-8 XML string
     *
     * @param string a XML string potentially containing invalid characters
     * @return string
     */
    private function sanitizeXML(string $string): string
    {
        if (!empty($string))
        {
            // remove EOT+NOREP+EOX|EOT+<char> sequence (FatturaPA)
            $string = preg_replace('/(\x{0004}(?:\x{201A}|\x{FFFD})(?:\x{0003}|\x{0004}).)/u', '', $string);

            $regex = '/(
            [\xC0-\xC1] # Invalid UTF-8 Bytes
            | [\xF5-\xFF] # Invalid UTF-8 Bytes
            | \xE0[\x80-\x9F] # Overlong encoding of prior code point
            | \xF0[\x80-\x8F] # Overlong encoding of prior code point
            | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
            | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
            | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
            | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
            | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
            | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
            | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
            | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
        )/x';
            $string = preg_replace($regex, '', $string);

            $result = "";
            $length = strlen($string);
            for ($i=0; $i < $length; $i++)
            {
                $current = ord($string[$i]);
                if (($current == 0x9) ||
                    ($current == 0xA) ||
                    ($current == 0xD) ||
                    (($current >= 0x20) && ($current <= 0xD7FF)) ||
                    (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                    (($current >= 0x10000) && ($current <= 0x10FFFF)))
                {
                    $result .= chr($current);
                }
            }
            $string = $result;
        }

        return $string;
    }
}