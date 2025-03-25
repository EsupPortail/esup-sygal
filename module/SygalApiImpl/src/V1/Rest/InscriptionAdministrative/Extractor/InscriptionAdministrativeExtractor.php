<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Extractor;

use stdClass;
use SygalApiImpl\V1\Extractor\AbstractExtractor;
use Webmozart\Assert\Assert;

class InscriptionAdministrativeExtractor extends AbstractExtractor
{
    public function extract(object $object): array
    {
        $data = parent::extract($object);

        $numeroCandidature = $this->extractNumeroCandidature($object);

        /** @var array $inscription */
        $inscription = $object->inscription;

        /** @var array $periode */
        $periode = $inscription['periode'];

        return array_merge($data, [
            'ecole_doct_id' => $inscription['ecoleDoctorale'],
            //
            'no_candidat' => $numeroCandidature,
            'date_inscription' => $inscription['dateInscription'],
            'date_annulation' => $inscription['dateAnnulation'] ?? null,
            'cesure' => $inscription['cesure'],
            'chemin' => $inscription['chemin'],
            'formation' => $inscription['formation'],
            'mobilite' => $inscription['mobilite'],
            'origine' => $inscription['origine'],
            'principale' => $inscription['principale'],
            'regime_inscription_libelle' => $inscription['regimeInscription']['libelle'] ?? null,
            'statut_inscription' => $inscription['statutInscription'],
            'code_structure_etablissement_du_chemin' => $inscription['codeStructureEtablissementDuChemin'],
            //
            // Il n'est pas possible d'importer les périodes d'inscription dans une table dédiée parce que
            // l'id de l'inscription administrative est forgé à partir de l'établissement, de l'apprenant,
            // de la formation et de la période. Si on le faisait, on aurait une duplication de chaque inscription
            // par le nombre de périodes.
            'periode_code' => $periode['code'],
            'periode_libelle' => $periode['libelle'],
            'periode_date_debut' => $periode['dateDebut'],
            'periode_date_fin' => $periode['dateFin'],
            'periode_annee_universitaire' => $periode['anneeUniversitaire'],
        ]);
    }

    public function extractSourceCode(stdClass $object): string
    {
        // NB : l'id de l'inscription administrative est en réalité forgé (côté Pégase) à partir des attributs suivants :
        //   - la structure organisationnelle (code UAI, ex : 'ETAB00'),
        //   - l'apprenant (code apprenant, ex : '000001059'),
        //   - le chemin formation (ex : 'M-BIO-SANT>M1-BIO-SANT'),
        //   - la période (ex : 'PER-2023').
        //
        // Ex d'id reçu : "ETAB00@000001059@M-BIO-SANT>M1-BIO-SANT@PER-2023"
        //
        // Cela pose problème : si l'inscription est modifiée dans Pégase (i.e. la valeur de l'un des attributs change),
        // SyGAL crée une nouvelle inscription !

        return $object->id;
    }

    public function extractNumeroCandidature(stdClass $object): string
    {
        /** @var array $inscription */
        $inscription = $object->inscription;

        Assert::keyExists($inscription, 'noCandidat');
        Assert::notEmpty($inscription['noCandidat']);

        return $inscription['noCandidat'];
    }
}