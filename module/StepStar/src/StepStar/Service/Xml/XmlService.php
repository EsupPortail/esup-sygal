<?php

namespace StepStar\Service\Xml;

use Application\Constants;
use Application\Entity\Db\Individu;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Role;
use Application\Service\These\TheseServiceAwareTrait;
use DateTime;
use Doctrine\ORM\Query;
use Faker\Factory;
use Faker\Provider\Person;
use StepStar\Exception\XmlServiceException;
use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;
use XMLWriter;

class XmlService
{
    use TheseServiceAwareTrait;

    const XML_TAG_THESES = 'THESES';
    const XML_TAG_THESE = 'THESE';

    /**
     * @var XMLWriter
     */
    private $writer;

    /**
     * @param XMLWriter $writer
     * @return self
     */
    public function setWriter(XMLWriter $writer): self
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * Exporte dans un fichier XML une seule thèse (spécifiée par son id).
     *
     * Le fichier XML ainsi généré pourra servir d'entrée pour la transformation XSLT en fichiers TEF,
     * cf. {@see generateTefFilesFromXml()}.
     *
     * @param int $theseId
     * @param string $xmlFilePath
     * @param bool $anonymize
     * @throws XmlServiceException
     */
    public function exportTheseToXml(int $theseId, string $xmlFilePath, bool $anonymize = false)
    {
        if (file_exists($xmlFilePath)) {
            throw new XmlServiceException("Le fichier destination spécifié existe déjà : " . $xmlFilePath);
        }

        $thesesXmlContent = $this->generateXmlContentForTheses([$theseId], $anonymize);
        file_put_contents($xmlFilePath, $thesesXmlContent);
    }

    /**
     * Exporte dans un fichier XML un ensemble de thèses (chacune spécifiée par son id),
     * dans le répertoire destination spécifié.
     *
     * Le fichier XML ainsi généré pourra servir d'entrée pour la transformation XSLT en fichiers TEF,
     * cf. {@see generateTefFilesFromXml()}.
     *
     * @param int[] $thesesIds
     * @param string $outputDir
     * @param bool $anonymize
     * @return string
     * @throws XmlServiceException
     */
    public function exportThesesToXml(array $thesesIds, string $outputDir, bool $anonymize = false): string
    {
        if ($outputDir === null) {
            throw new XmlServiceException("Aucun répertoire destination n'a été spécifié");
        }
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $thesesXmlContent = $this->generateXmlContentForTheses($thesesIds, $anonymize);
        $xmlFilePath = $outputDir . '/' . uniqid('theses_') . '.xml';
        file_put_contents($xmlFilePath, $thesesXmlContent);

        return $xmlFilePath;
    }

    /**
     * @param array $thesesIds
     * @param bool $anonymize
     * @return string
     */
    private function generateXmlContentForTheses(array $thesesIds, bool $anonymize = false): string
    {
        $arrayHydratedTheses = $this->fetchThesesAsArrays($thesesIds);

        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->setIndentString('  ');
        $this->writer->startDocument('1.0', 'UTF-8', 'yes');
        $this->writer->startElement(self::XML_TAG_THESES);
        foreach ($arrayHydratedTheses as $these) {
            $this->validateThese($these);
            $this->writer->startElement(self::XML_TAG_THESE);
            $array = $this->convertTheseToArrayForXml($these);
            if ($anonymize) {
                $array = $this->anonymizeTheseArray($array);
            }
            foreach ($array as $tag => $text) {
                $this->writer->startElement($tag);
                $this->writer->text($text);
                $this->writer->endElement();
            }
            $this->writer->endElement(); // self::XML_TAG_THESE
        }
        $this->writer->endElement(); // self::XML_TAG_THESES
        $this->writer->endDocument();

        return $this->writer->outputMemory();
    }


    /**
     * Recherche et hydrate au format array les thèses spécifiées, avec toutes les jointures requises pour
     * exporter les thèses au format XML.
     *
     * @param int[] $thesesIds Ids des thèses concernées
     * @return array[] Thèses trouvées, hydrtaées au format array
     */
    private function fetchThesesAsArrays(array $thesesIds): array
    {
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb
            ->addSelect('e, d, di, ed, ur, es, eds, urs, dc, mails, rdv, a, ai, r')
            ->join('t.etablissement', 'e')
            ->join('t.doctorant', 'd')
            ->join('d.individu', 'di')
            ->join('t.ecoleDoctorale', 'ed')
            ->join('t.uniteRecherche', 'ur')
            ->join('e.structure', 'es')
            ->join('ed.structure', 'eds')
            ->join('ur.structure', 'urs')
            ->leftJoin('d.complements', 'dc')
            ->leftJoin('di.mailsConfirmations', 'mails')
            ->leftJoin('t.rdvBus', 'rdv')
            ->leftJoin('t.acteurs', 'a')
            ->leftJoin('a.individu', 'ai')
            ->leftJoin('a.role', 'r')
            ->where($qb->expr()->in('t.id', $thesesIds));

        $theses = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

        if (count($theses) !== count($thesesIds)) {
            throw new RuntimeException("Certaines thèses spécifiées sont introuvables.");
        }

        return $theses;
    }

    /**
     * @param array $these
     */
    private function validateThese(array $these)
    {
        Assert::isArray($these, "Chaque thèse doit être spécifiée au format array");

        // todo: vérifier aussi le contenu ?
    }

    /**
     * @param array $these
     * @return array
     */
    private function convertTheseToArrayForXml(array $these): array
    {
        $directeur = null;
        $membresJury = [];
        $rapporteurs = [];
        foreach ($these['acteurs'] as $acteur) {
            if ($acteur['role']['code'] === Role::CODE_DIRECTEUR_THESE) {
                $directeur = $acteur['individu'];
            } elseif ($acteur['role']['code'] === Role::CODE_MEMBRE_JURY) {
                $membresJury[] = $acteur['individu'];
            } elseif ($acteur['role']['code'] === Role::CODE_RAPPORTEUR_JURY) {
                $rapporteurs[] = $acteur['individu'];
            }
        }

        $data = [
            'CODE_ETUDIANT' => $these['doctorant']['individu']['supannId'],
            'CODE_INE' => $these['doctorant']['ine'],
            'NOM_ETUDIANT' => $these['doctorant']['individu']['nomPatronymique'] ?: $these['doctorant']['individu']['nomUsuel'],
            'NOM_ETUDIANT_USUEL' => $these['doctorant']['individu']['nomUsuel'],
            'PRENOM_ETUDIANT' => $these['doctorant']['individu']['prenom1'],
            'EMAIL_ETUDIANT_PRO' => $this->doctorantEmailPro($these['doctorant']),
            'EMAIL_ETUDIANT_PERSO' => $this->doctorantEmailPerso($these['doctorant']),
            'DATE_NAISSANCE_ETUDIANT' => $this->date($these['doctorant']['individu']['dateNaissance']),
            'SEXE_ETUDIANT' => $this->sexe($these['doctorant']['individu']['civilite']),
            'CODE_ETAB_SOUT' => $these['etablissement']['code'],
            'LIBELLE_ETAB_SOUT' => $this->structureLibelle($these['etablissement']),
            'TITRE' => $these['titre'],
            'DISCIPLINE' => $these['libelleDiscipline'],
            //'AVIS_DE_REPRODUCTION' => $these['correctionAutorisee'], // todo : ce n'est pas l'avis de reproduction !
            'DATE_FIN_CONFIDENTIALITE' => $this->date($these['dateFinConfidentialite']),
            'DATE_SOUTENANCE' => $this->date($these['dateSoutenance']),
            'CODE_COTUTELLE' => 'xxxxx', // todo : kezako ?
            'NOM_COTUTELLE' => $these['libelleEtabCotutelle'],
            'CODE_ECOLE_DOCTORALE' => $this->structureCode($these['ecoleDoctorale']),
            'LIBELLE_ECOLE_DOCTORALE' => $this->structureLibelle($these['ecoleDoctorale']),
            'CODE_EQUIPE_RECHERCHE_1' => $this->structureCode($these['uniteRecherche']),
            'LIBELLE_EQUIPE_RECHERCHE_1' => $this->structureLibelle($these['uniteRecherche']),
            'NOM_DIRECTEUR' => $directeur['nomUsuel'],
            'PRENOM_DIRECTEUR' => $directeur['prenom1'],
        ];

        foreach ($membresJury as $i => $individu) {
            $index = $i + 1;
            $data['NOM_MEMBRE_JURY_' . $index] = $individu['nomUsuel'];
            $data['PRENOM_MEMBRE_JURY_' . $index] = $individu['prenom1'];
        }

        foreach ($rapporteurs as $i => $individu) {
            $index = $i + 1;
            $data['NOM_RAPPORTEUR_' . $index] = $individu['nomUsuel'];
            $data['PRENOM_RAPPORTEUR_' . $index] = $individu['prenom1'];
        }

//        if (isset($these['metadonnees'])) {
//            $metadonnees = $these['motsClesRameau'][0];
//            foreach (MetadonneeThese::explodeMotsCles($metadonnees['motsClesRameau']) as $i => $mot) {
//                $index = $i + 1;
//                $data['MOT_CLE_' . $index] = $mot;
//            }
//        }

        if (isset($these['rdvBus'])) {
            $rdvBu = $these['rdvBus'][0];
            foreach (RdvBu::explodeMotsClesRameau($rdvBu['motsClesRameau']) as $i => $mot) {
                $index = $i + 1;
                $data['MOT_CLE_' . $index] = $mot;
            }
        }

        return array_filter($data);
    }

    /**
     * @param array $theseAsArray
     * @return array
     */
    private function anonymizeTheseArray(array $theseAsArray): array
    {
        $faker = Factory::create(\Locale::getDefault());

        $fakeValuesArray = [
            'CODE_ETUDIANT' => $faker->randomNumber(8),
            'CODE_INE' => $faker->randomNumber(6),
            'NOM_ETUDIANT' => $faker->lastName,
            'NOM_ETUDIANT_USUEL' => 'Sygal ' . $faker->firstName,
            'PRENOM_ETUDIANT' => $faker->firstName(Person::GENDER_FEMALE),
            'EMAIL_ETUDIANT_PRO' => 'email@pro.org',
            'EMAIL_ETUDIANT_PERSO' => 'email@perso.org',
            'DATE_NAISSANCE_ETUDIANT' => '01/01/2000',
            'SEXE_ETUDIANT' => 'F',
            'CODE_ETAB_SOUT' => 123456,
            'LIBELLE_ETAB_SOUT' => $faker->company,
            'TITRE' => $faker->text(50),
            'DISCIPLINE' => 'PHILOSOPHIE',
            'CODE_ECOLE_DOCTORALE' => $faker->randomNumber(),
            'LIBELLE_ECOLE_DOCTORALE' => $faker->text(20),
            'CODE_EQUIPE_RECHERCHE_1' => $faker->randomNumber(5),
            'LIBELLE_EQUIPE_RECHERCHE_1' => $faker->text(20),
            'NOM_DIRECTEUR' => $faker->lastName,
            'PRENOM_DIRECTEUR' => $faker->firstName,
        ];
        for ($i = 1 ; $i <= 20 ; ++$i) { // peu probable qu'il y a plus de 20 membres ou rapporteurs !
            $fakeValuesArray['NOM_MEMBRE_JURY_' . $i] = $faker->lastName;
            $fakeValuesArray['PRENOM_MEMBRE_JURY_' . $i] = $faker->firstName;
            $fakeValuesArray['NOM_RAPPORTEUR_' . $i] = $faker->lastName;
            $fakeValuesArray['PRENOM_RAPPORTEUR_' . $i] = $faker->firstName;
        }

        // supprime les clés de $fakeValuesArray n'existant pas dans $theseAsArray
        $fakeValuesArray = array_intersect_key($fakeValuesArray, $theseAsArray);

        // remplace les infos réelles avec les infos anonymes
        return array_merge($theseAsArray, $fakeValuesArray);
    }

    private function sexe(string $civilite): string
    {
        return [
            Individu::CIVILITE_M => 'M',
            Individu::CIVILITE_MME => 'F',
        ][$civilite];
    }

    private function date(DateTime $date = null): ?string
    {
        return $date ? $date->format(Constants::DATE_FORMAT) : null;
    }

    private function structureCode(array $structure = null): ?string
    {
        return $structure['structure']['code'] ?? null;
    }

    private function structureLibelle(array $structure = null): ?string
    {
        return $structure['structure']['libelle'] ?? null;
    }

    private function doctorantEmailPro(array $doctorant)
    {
        foreach ($doctorant['complements'] as $complement) {
            if ($complement['emailPro'] !== null) {
                return $complement['emailPro'];
            }
        }

        return $doctorant['individu']['email'];
    }

    private function doctorantEmailPerso(array $doctorant)
    {
        foreach ($doctorant['individu']['mails'] as $mailConfirmation) {
            if ($mailConfirmation['email'] !== null) {
                return $mailConfirmation['email'];
            }
        }

        return null;
    }
}