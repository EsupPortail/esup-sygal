<?php

namespace Application\Service\Acteur;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Repository\ActeurRepository;
use Application\Service\BaseService;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;

class ActeurService extends BaseService
{

    /**
     * @return ActeurRepository
     */
    public function getRepository()
    {
        /** @var ActeurRepository $repo */
        $repo = $this->entityManager->getRepository(Acteur::class);

        return $repo;
    }

    public function addActeur41321() {

        $sql = "INSERT INTO ACTEUR (ID, INDIVIDU_ID, THESE_ID, ROLE_ID, QUALITE, LIB_ROLE_COMPL, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_CREATION, HISTO_MODIFICATEUR_ID, HISTO_MODIFICATION, HISTO_DESTRUCTEUR_ID, HISTO_DESTRUCTION, ACTEUR_ETABLISSEMENT_ID) VALUES (101, 36608, 41321, 20, 'Professeur des universites', null, 'SYGAL::TEST_001', 1, 1, TO_DATE('2019-01-15 09:49:30', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-01-15 10:50:53', 'YYYY-MM-DD HH24:MI:SS'), null, null, 2); ";
        $sql .= "INSERT INTO ACTEUR (ID, INDIVIDU_ID, THESE_ID, ROLE_ID, QUALITE, LIB_ROLE_COMPL, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_CREATION, HISTO_MODIFICATEUR_ID, HISTO_MODIFICATION, HISTO_DESTRUCTEUR_ID, HISTO_DESTRUCTION, ACTEUR_ETABLISSEMENT_ID) VALUES (102, 5741, 41321, 20, 'Professeur des universites', null, 'SYGAL::TEST_002', 1, 1, TO_DATE('2019-01-15 10:53:30', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-01-15 10:53:30', 'YYYY-MM-DD HH24:MI:SS'), null, null, 3); ";
        $sql .= "INSERT INTO ACTEUR (ID, INDIVIDU_ID, THESE_ID, ROLE_ID, QUALITE, LIB_ROLE_COMPL, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_CREATION, HISTO_MODIFICATEUR_ID, HISTO_MODIFICATION, HISTO_DESTRUCTEUR_ID, HISTO_DESTRUCTION, ACTEUR_ETABLISSEMENT_ID) VALUES (103, 38221, 41321, 22, 'Maitre de conferences', null, 'SYGAL::TEST_003', 1, 1, TO_DATE('2019-01-15 10:58:39', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-01-15 10:58:39', 'YYYY-MM-DD HH24:MI:SS'), null, null, 2); ";
        $sql .= "INSERT INTO ACTEUR (ID, INDIVIDU_ID, THESE_ID, ROLE_ID, QUALITE, LIB_ROLE_COMPL, SOURCE_CODE, SOURCE_ID, HISTO_CREATEUR_ID, HISTO_CREATION, HISTO_MODIFICATEUR_ID, HISTO_MODIFICATION, HISTO_DESTRUCTEUR_ID, HISTO_DESTRUCTION, ACTEUR_ETABLISSEMENT_ID) VALUES (104, 38483, 41321, 22, 'Maitre de conferences', null, 'SYGAL::TEST_004', 1, 1, TO_DATE('2019-01-15 10:59:19', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-01-15 10:59:19', 'YYYY-MM-DD HH24:MI:SS'), null, null, 2); ";
        $plsql = implode(PHP_EOL, array_merge(['BEGIN'], [$sql], ['END;']));

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $connection->executeQuery($plsql);
        $connection->commit();

    }

    public function removeActeur41321()
    {
        $sql = "DELETE FROM ACTEUR WHERE ID IN (101,102,103,104);";
        $plsql = implode(PHP_EOL, array_merge(['BEGIN'], [$sql], ['END;']));

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $connection->executeQuery($plsql);
        $connection->commit();
    }

    public function restaureValidation()
    {
        $sql = "UPDATE VALIDATION SET HISTO_DESTRUCTION = null, HISTO_DESTRUCTEUR_ID = null WHERE TYPE_VALIDATION_ID = 6 AND THESE_ID = 41321; ";
        $plsql = implode(PHP_EOL, array_merge(['BEGIN'], [$sql], ['END;']));

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $connection->executeQuery($plsql);
        $connection->commit();
    }
}