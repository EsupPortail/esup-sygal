<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;

class AdmissionRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un dossier d'Admission à partir de son individu.
     *
     * @param Individu|string $individu
     * @return Admission|null
     */
    public function findOneByIndividu(Individu|string $individu): Admission|null
    {
        return $this->findOneBy(['individu' => $individu]);
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Admission|null
     */
    public function findRequestedAdmission(AbstractActionController $controller, string $param='admission') : ?Admission
    {
        $admissionId = $controller->params()->fromRoute($param);

        return $admissionId ? $this->find($admissionId) : null;
    }
}