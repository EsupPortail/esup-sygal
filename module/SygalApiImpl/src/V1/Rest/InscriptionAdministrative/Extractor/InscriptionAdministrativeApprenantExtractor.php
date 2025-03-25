<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Extractor;

use stdClass;
use SygalApiImpl\V1\Extractor\AbstractExtractor;

class InscriptionAdministrativeApprenantExtractor extends AbstractExtractor
{
    private InscriptionAdministrativeExtractor $inscriptionAdministrativeExtractor;

    public function __construct()
    {
        $this->inscriptionAdministrativeExtractor = new InscriptionAdministrativeExtractor();
    }

    public function extract(object $object): array
    {
        $data = parent::extract($object);

        $inscriptionAdministrativeSourceCode = $this->inscriptionAdministrativeExtractor->extractSourceCode($object);

        /** @var array $apprenant */
        $apprenant = $object->apprenant;
        /** @var array $naissance */
        $naissance = $apprenant['naissance'];

        return array_merge($data, [
            'inscription_administrative_id' => $inscriptionAdministrativeSourceCode,
            //
            'code' => $apprenant['code'], // code apprenant pegase
            'INE' => $apprenant['INE'],
            'deuxieme_prenom' => $apprenant['deuxiemePrenom'],
            'genre' => $apprenant['genre'],
            'mail_personnel' => $apprenant['mailPersonnel'],
            'mail_urgence' => $apprenant['mailUrgence'],
            'nom_de_naissance' => $apprenant['nomDeNaissance'],
            'nom_usuel' => $apprenant['nomUsuel'],
            'prenom' => $apprenant['prenom'],
            'prenom_usage' => $apprenant['prenomUsage'],
            'telephone_personnel' => $apprenant['telephonePersonnel'],
            'telephone_urgence' => $apprenant['telephoneUrgence'],
            'troisieme_prenom' => $apprenant['troisiemePrenom'],
            // naissance :
            'commune_de_naissance' => $naissance['communeDeNaissance'],
            'commune_de_naissance_etranger' => $naissance['communeDeNaissanceEtranger'],
            'date_d_obtention_de_la_deuxieme_nationalite' => $naissance['dateDObtentionDeLaDeuxiemeNationalite'],
            'date_de_naissance' => $naissance['dateDeNaissance'],
            'deuxieme_nationalite' => $naissance['deuxiemeNationalite'],
            'libelle_commune_de_naissance' => $naissance['libelleCommuneDeNaissance'],
            'libelle_deuxieme_nationalite' => $naissance['libelleDeuxiemeNationalite'],
            'libelle_nationalite' => $naissance['libelleNationalite'],
            'libelle_pays_de_naissance' => $naissance['libellePaysDeNaissance'],
            'nationalite' => $naissance['nationalite'],
            'pays_de_naissance' => $naissance['paysDeNaissance'],
        ]);
    }

    public function extractSourceCode(stdClass $object): string
    {
        $inscriptionAdministrativeSourceCode = $this->inscriptionAdministrativeExtractor->extractSourceCode($object);

        /** @var array $apprenant */
        $apprenant = $object->apprenant;

        return $inscriptionAdministrativeSourceCode . '|' . $apprenant['code'];
    }
}