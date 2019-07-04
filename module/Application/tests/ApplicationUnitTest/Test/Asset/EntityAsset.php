<?php

namespace ApplicationUnitTest\Test\Asset;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Attestation;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\ImportObserv;
use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Individu;
use Application\Entity\Db\MetadonneeThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\Role;
use Application\Entity\Db\Source;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\VersionFichier;

/**
 * Données de tests.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class EntityAsset
{
    const SOURCE_TEST = 'Test';

    static public function newUtilisateur()
    {
        $e = new Utilisateur();
        $e->setDisplayName('TEST Hercule');
        $e->setEmail('hercule.test@mail.fr');
        $e->setPassword('azerty');
        $e->setState(1);
        $e->setUsername(uniqid());

        return $e;
    }

    static public function newFichier(NatureFichier $nature, VersionFichier $version)
    {
        $e = new Fichier();
        $e
            ->setNature($nature)
            ->setVersion($version)
            ->setNom(uniqid('Fichier_') . '.png')
            ->setNomOriginal(uniqid('Fichier_') . '.png')
            ->setDescription('Fichier de test')
            ->setTypeMime("image/png")
            ->setTaille(1024);

        return $e;
    }

    static public function newFichierThese(These $these, Fichier $fichier)
    {
        $e = new FichierThese();
        $e
            ->setFichier($fichier)
            ->setThese($these);

        return $e;
    }

    static public function newValidation(These $these, TypeValidation $type, Individu $individu = null)
    {
        $e = new Validation($type, $these, $individu);

        return $e;
    }

    public static function newSource()
    {
        $e = new Source();

        $e->setCode('robinet');
        $e->setImportable(false);
        $e->setLibelle('Robinet');


        return $e;
    }

    public static function newDoctorant(Source $source)
    {
        $e = new Doctorant();

        $e->setSource($source);
        $e->setSourceCode(uniqid());
        $e->setDateNaissance(new \DateTime());
        $e->setNationalite('Terrien');
        $e->setEmail('doc.torant@mail.fr');
        $e->setNomPatronymique(null);
        $e->setNomUsuel('Hochon');
        $e->setPrenom('Paul');
        $e->setCivilite('M.');

        return $e;
    }

    public static function newThese(Doctorant $doctorant, Source $source)
    {
        $e = new These();

        $e->setSource($source);
        $e->setDoctorant($doctorant);

        $e->setCodeUniteRecherche('UMR0000');
        $e->setTitre("Thèse de test");
        $e->setEtatThese(These::ETAT_EN_COURS);
        $e->setLibelleDiscipline('PHILOSOPHIE');
        $e->setResultat(These::RESULTAT_ADMIS);
        $e->setSourceCode(uniqid());

        return $e;
    }

    public static function newAttestation(These $these)
    {
        $e = new Attestation();

        $e
            ->setThese($these)
            ->setExemplaireImprimeConformeAVersionDeposee(true)
            ->setVersionDeposeeEstVersionRef(true);

        return $e;
    }

    public static function newDiffusion(These $these)
    {
        $e = new Diffusion();

        $e
            ->setThese($these)
            ->setAutorisMel(Diffusion::AUTORISATION_OUI_IMMEDIAT)
            ->setCertifCharteDiff(true)
            ->setDroitAuteurOk(true)
            ->setConfidentielle(false);

        return $e;
    }

    public static function newSignalement(These $these)
    {
        $e = new MetadonneeThese();

        $e
            ->setThese($these)
            ->setLangue('fr')
            ->setMotsClesLibresFrancais("mot ; clé")
            ->setMotsClesLibresAnglais("key ; word")
            ->setResume("Résumé")
            ->setResumeAnglais("Abstract")
            ->setTitre("Titre")
            ->setTitreAutreLangue("Title");

        return $e;
    }

    public static function newValiditeFichierThese(Fichier $fichier, $estValide = null)
    {
        $e = new ValiditeFichier();

        $e
            ->setFichier($fichier)
            ->setEstValide($estValide);

        return $e;
    }

    public static function newRdvBu(These $these)
    {
        $e = new RdvBu();

        $e
            ->setThese($these)/*->setConventionMelSignee(true)
            ->setExemplPapierFourni(true)
            ->setCoordDoctorant("06 06 06 06 06")
            ->setDispoDoctorant("Nuit")
            ->setMotsClesRameau("mot clé rameau")
            ->setVersionArchivableFournie(true)*/
        ;

        return $e;
    }

    static public function newIndividu(Source $source)
    {
        $e = new Individu();
        $e
            ->setCivilite('Mme')
            ->setEmail('indi.vidu@unicaen.fr')
            ->setNomUsuel("Hochon")
            ->setPrenom("Paule")
            ->setSourceCode(uniqid())
            ->setSource($source);

        return $e;
    }

    public static function newRoleDirecteurThese(Source $source)
    {
        $e = new Role();
        $e
            ->setSourceCode(Role::CODE_DIRECTEUR_THESE)
            ->setSource($source)
            ->setRoleId(uniqid());

        return $e;
    }

    public static function newActeur(These $these, Source $source, Role $role, Individu $individu)
    {
        $e = new Acteur();
        $e
            ->setThese($these)
            ->setRole($role)
            ->setIndividu($individu)
            ->setSourceCode(uniqid())
            ->setSource($source);

        return $e;
    }

    public static function newImportObservResult(ImportObserv $importObserv)
    {
        $e = new ImportObservResult();

        $e->setImportObserv($importObserv);
        $e->setDateNotif(null);
        $e->setSourceCode(uniqid());
        $e->setDateCreation(new \DateTime('yesterday'));
        $e->setResultat('peu importe');

        return $e;
    }

    public static function newImportObserv()
    {
        $e = new ImportObserv();

        $e->setCode(uniqid());
        $e->setColumnName('COLUMN');
        $e->setDescription('Description');
        $e->setOperation('peu importe');
        $e->setToValue('peu importe');
        $e->setEnabled(true);

        return $e;
    }
}