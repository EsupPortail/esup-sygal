<?php

namespace StepStar\Service\Xml;

use Application\Constants;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Webmozart\Assert\Assert;
use XMLWriter;

class XmlService
{
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
     * @param array $theses
     * @return string
     */
    public function generateXmlContentForTheses(array $theses): string
    {
        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->setIndentString('  ');
        $this->writer->startDocument('1.0', 'UTF-8', 'yes');
        $this->writer->startElement('THESES');
        foreach ($theses as $these) {
            $this->validateThese($these);
            $this->writer->startElement('THESE');
            $array = $this->generateArray($these);
            foreach ($array as $tag => $text) {
                $this->writer->startElement($tag);
                $this->writer->text($text);
                $this->writer->endElement();
            }
            $this->writer->endElement(); // THESE
        }
        $this->writer->endElement(); // THESES
        $this->writer->endDocument();

        return $this->writer->outputMemory();
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
    private function generateArray(array $these): array
    {
        $directeur = null;
        $membresJury = [];
        foreach ($these['acteurs'] as $acteur) {
            if ($acteur['role']['code'] === Role::CODE_DIRECTEUR_THESE) {
                $directeur = $acteur['individu'];
            }
            elseif ($acteur['role']['code'] === Role::CODE_MEMBRE_JURY) {
                $membresJury[] = $acteur['individu'];
            }
        }

        $data = [
            'CODE_ETUDIANT' => $these['doctorant']['individu']['supannId'], // todo : ou l'id ?
            'CODE_INE' => $these['doctorant']['ine'],
            'NOM_ETUDIANT' => $these['doctorant']['individu']['nomPatronymique'] ?: $these['doctorant']['individu']['nomUsuel'],
            'NOM_ETUDIANT_USUEL' => $these['doctorant']['individu']['nomUsuel'],
            'PRENOM_ETUDIANT' => $these['doctorant']['individu']['prenom1'],
            'DATE_NAISSANCE_ETUDIANT' => $this->formatDate($these['doctorant']['individu']['dateNaissance']),
            'SEXE_ETUDIANT' => $this->formatSexe($these['doctorant']['individu']['civilite']),
            'CODE_ETAB_SOUT' => $these['etablissement']['code'],
            'LIBELLE_ETAB_SOUT' => $this->structureLibelle($these['etablissement']),
            'TITRE' => $these['titre'],
            'DISCIPLINE' => $these['libelleDiscipline'],
            'AVIS_DE_REPRODUCTION' => $these['correctionAutorisee'], // todo : ce n'est pas l'avis de reproduction !
            'DATE_FIN_CONFIDENTIALITE' => $this->formatDate($these['dateFinConfidentialite']),
            'DATE_SOUTENANCE' => $this->formatDate($these['dateSoutenance']),
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

        return array_filter($data);
    }

    private function formatSexe(string $civilite): string
    {
        return [
            Individu::CIVILITE_M => 'M',
            Individu::CIVILITE_MME => 'F',
        ][$civilite];
    }

    private function formatDate(\DateTime $date = null): ?string
    {
        return $date ? $date->format(Constants::DATE_FORMAT) : null;
    }

    private function structureCode(array $structure = null): ?string
    {
        return $structure['structure']['code'] ?? null;
    }

    private function structureLibelle(array $array = null): ?string
    {
        return $array['structure']['libelle'] ?? null;
    }
}