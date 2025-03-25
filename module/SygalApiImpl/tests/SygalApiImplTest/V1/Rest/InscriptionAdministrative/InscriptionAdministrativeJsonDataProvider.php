<?php

namespace SygalApiImplTest\V1\Rest\InscriptionAdministrative;

use Faker\Factory;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use Locale;
use SygalApiImplTest\AbstractJsonDataProvider;

class InscriptionAdministrativeJsonDataProvider extends AbstractJsonDataProvider
{
    protected string $jsonTemplateFileName = 'inscr_admin.json.twig';

    public string $instancePegase = 'PEGAZZZ';

    public function getInitialContext(): array
    {
        $faker = Factory::create(Locale::getDefault());

        return [
            'instancePegase' => $this->instancePegase,
            'codeApprenant' => rand(10000, 99999),
            'noCandidat' => 'SYG' . rand(1000, 9999),
            'ine' => rand(100000000, 999999999),
            'annee' => 2024,
            'statutInscription' => InscriptionAdministrative::STATUT_INSCRIPTION_VALIDE,
            'nom' => $faker->lastName(),
            'prenom' => $faker->firstName(),
        ];
    }
}