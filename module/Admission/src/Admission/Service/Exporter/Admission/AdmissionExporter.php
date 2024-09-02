<?php

namespace Admission\Service\Exporter\Admission;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use DateTime;
use RuntimeException;
use UnicaenApp\View\Model\CsvModel;
use ZipArchive;

class AdmissionExporter{

    public function exportToZip(array $csvs, string $zipFilename): ZipArchive
    {
        // Création de l'archive ZIP
        $zip = new ZipArchive();

        foreach ($csvs as $csv) {
            if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) throw new RuntimeException('Impossible de créer le fichier ZIP');

            // Ajout du fichier CSV à l'archive
            if(!$csv instanceof CsvModel)  throw new RuntimeException("Le fichier donné n'est pas au format attendu : CSV");

            $tempCSV = $this->generateCsvContent($csv);
            if(!$tempCSV) throw new RuntimeException("Le contenu du CSV n'a pas pu être récupéré");

            $zip->addFromString($csv->getFilename(), $tempCSV);

            $zip->close();
        }
        return $zip;
    }

    private function generateCsvContent(CsvModel $csvModel): bool|string
    {
        $output = fopen('php://memory', 'r+');
        $delimiter = $csvModel->getDelimiter();
        $enclosure = $csvModel->getEnclosure();

        if ($headers = $csvModel->getHeader()) {
            fputcsv($output, $headers, $delimiter, $enclosure);
        }

        foreach ($csvModel->getData() as $record) {
            fputcsv($output, $record, $delimiter, $enclosure);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    public function exportAdmisCSV($admissions): CsvModel
    {
        //export
        $headers = ['numero_candidat', 'sexe', 'nom_famille', 'nom_usuel', 'prenom', 'prenom2', 'prenom3',	'date_naissance', 'code_commune_naissance',
            'libellé_commune_naissance', 'code_pays_naissance',	'code_nationalite',	'ine', 'adresse_code_pays',	'adresse_ligne1_etage',
            'adresse_ligne2_batiment',	'adresse_ligne3_voie',	'adresse_ligne4_complement', 'adresse_code_postal',	'adresse_code_commune',
            'adresse_cp_ville_etranger', 'numero_telephone1', 'numero_telephone2', 'courriel'
        ];
        $records = [];
        /** @var Admission $admission */
        foreach ($admissions as $admission) {
            $entry = [];
            /** @var Etudiant $etudiant */
            $etudiant = $admission->getEtudiant()->first();
            $entry['numero_candidat'] = $etudiant->getNumeroCandidat();
            $entry['sexe'] = rtrim($etudiant->getSexe(), '.');
            $entry['nom_famille'] = $etudiant->getNomFamille();
            $entry['nom_usuel'] = $etudiant->getNomUsuel();
            $entry['prenom'] = $etudiant->getPrenom();
            $entry['prenom2'] = $etudiant->getPrenom2();
            $entry['prenom3'] = $etudiant->getPrenom3();
            $entry['date_naissance'] = $etudiant->getDateNaissanceFormat();
            $entry['code_commune_naissance'] = $etudiant->getCodeCommuneNaissance();
            $entry['libellé_commune_naissance'] = $etudiant->getLibelleCommuneNaissance();
            $entry['code_pays_naissance'] = $etudiant->getPaysNaissance() ? $etudiant->getPaysNaissance()->getCodePaysApogee() : null;
            $entry['code_nationalite'] = $etudiant->getNationalite() ? $etudiant->getNationalite()->getCodePaysApogee() : null;
            $entry['ine'] = $etudiant->getIne();
            $entry['adresse_code_pays'] = $etudiant->getAdresseCodePays() ? $etudiant->getAdresseCodePays()->getCodePaysApogee() : null;
            $entry['adresse_ligne1_etage'] = $etudiant->getAdresseLigne1Etage();
            $entry['adresse_ligne2_batiment'] = $etudiant->getAdresseLigne2Batiment();
            $entry['adresse_ligne3_voie'] = $etudiant->getAdresseLigne3voie();
            $entry['adresse_ligne4_complement'] = $etudiant->getAdresseLigne4Complement();
            $entry['adresse_code_postal'] = $etudiant->getAdresseCodePostal();
            $entry['adresse_code_commune'] = $etudiant->getAdresseCodeCommune();
            $entry['adresse_cp_ville_etranger'] = $etudiant->getAdresseCpVilleEtrangere();
            $entry['numero_telephone1'] = (string)$etudiant->getNumeroTelephone1();
            $entry['numero_telephone2'] = (string)$etudiant->getNumeroTelephone2();
            $entry['courriel'] = $etudiant->getCourriel();
            $records[] = $entry;
        }
        $filename = (new DateTime())->format('Ymd') . '_admis.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }

    public function exportAdmissionsCSV($admissions): CsvModel
    {
        //export
        $headers = ['numero_candidat', 'origine_admission', 'voie_admission', 'annee_concours', 'code_voeu', 'code_periode', 'code_sise', 'code_formation_psup', 'code_etablissement_affectation'];
        $records = [];
        /** @var Admission $admission */
        foreach ($admissions as $admission) {
            $entry = [];
            /** @var Etudiant $etudiant */
            $etudiant = $admission->getEtudiant()->first();
            $entry['numero_candidat'] = $etudiant->getNumeroCandidat();
            $entry['origine_admission'] = "CO";
            $entry['voie_admission'] = "D";
            $entry['annee_concours'] = null;
            $entry['code_voeu'] = "";
            $entry['code_periode'] = "";
            $entry['code_sise'] = null;
            $entry['code_formation_psup'] = null;
            $entry['code_etablissement_affectation'] = null;
            $records[] = $entry;
        }
        $filename = (new DateTime())->format('Ymd') . '_admissions.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }
}