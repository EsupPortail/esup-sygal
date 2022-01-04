<?php

namespace Application\Service\Notification;

use Application\Entity\Db\FichierThese;
use Application\Entity\Db\These;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\Variable;
use Application\Entity\Db\VersionFichier;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Notification\Notification;
use UnicaenApp\Options\ModuleOptions;
use Zend\View\Helper\Url as UrlHelper;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationFactory extends \Notification\Service\NotificationFactory
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var ModuleOptions
     */
    private $appModuleOptions;

    /**
     * {@inheritdoc}
     */
    public function initNotification(Notification $notification)
    {
        parent::initNotification($notification);

        // injecte le nom de l'appli dans la variable 'appName' utilisée par tous les templates
        $appInfos = $this->appModuleOptions->getAppInfos();
        $appName = $appInfos['nom'];
        $notification->setTemplateVariables([
            'appName' => $appName,
        ]);
    }

    /**
     * Notifie que le retraitement automatique du fichier PDF est terminé.
     *
     * @param string               $destinataires        Emails séparés par une virgule
     * @param FichierThese         $fichierTheseRetraite Fichier retraité concerné
     * @param ValiditeFichier|null $validite             Résultat du test d'archivabilité éventuel
     * @return Notification
     * @return Notification
     */
    public function createNotificationForRetraitementFini($destinataires, FichierThese $fichierTheseRetraite, ValiditeFichier $validite = null)
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = $this->createNotification();
        $notif
            ->setSubject("Retraitement terminé")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-retraitement-fini')
            ->setTemplateVariables([
                'fichierRetraite' => $fichierTheseRetraite,
                'validite' => $validite,
                'url' => '',
            ]);

        return $notif;
    }

    /**
     * Notification à l'issu du remplissage du formulaire RDV BU par le doctorant.
     *
     * @param These $these
     * @param bool $estLaPremiereSaisie
     * @return Notification
     */
    public function createNotificationForRdvBuSaisiParDoctorant(These $these, $estLaPremiereSaisie)
    {
        $subject = sprintf("%s Saisie des informations pour la prise de rendez-vous avec la bibliothèque universitaire", $these->getLibelleDiscipline());
        $to = $this->fetchEmailBu($these);

        $notif = $this->createNotification();
        $notif
            ->setTo($to)
            ->setSubject($subject)
            ->setTemplatePath('application/these/mail/notif-modif-rdv-bu-doctorant')
            ->setTemplateVariables([
                'these' => $these,
                'updating' => !$estLaPremiereSaisie,
            ]);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à la bibliothèque universitaire (%s).", $to);
        $notif->setInfoMessages($infoMessage);

        return $notif;
    }

    /**
     * Notification à l'issue du dépôt d'un fichier de thèse.
     *
     * @param These $these
     * @param VersionFichier $version
     * @return Notification
     */
    public function createNotificationForTheseTeleversee(These $these, VersionFichier $version)
    {
        $to = $this->fetchEmailBdd($these);

        $notif = $this->createNotification('notif-depot-these');
        $notif
            ->setTo($to)
            ->setSubject("Dépôt d'une thèse")
//            ->setTemplatePath('application/these/mail/notif-depot-these') // le template est dans la NotifEntity
            ->setTemplateVariables([
                'these' => $these,
                'version' => $version,
            ]);

        return $notif;
    }

    /**
     * Notification à l'issue du dépôt d'un fichier.
     *
     * @param These $these
     * @return Notification
     */
    public function createNotificationForFichierTeleverse(These $these)
    {
        $to = $this->fetchEmailBdd($these);

        $notif = $this->createNotification();
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        return $notif;
    }

    /**
     * @param These $these
     * @return string
     */
    private function fetchEmailBdd(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);

        return $variable->getValeur();
    }

    /**
     * @param These $these
     * @return string
     */
    private function fetchEmailBu(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BU, $these);

        return $variable->getValeur();
    }

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setAppModuleOptions(ModuleOptions $options)
    {
        $this->appModuleOptions = $options;
    }

    /**
     * Notifie que la fusion de la page de couverture avec la thèse PDF est terminée.
     *
     * @param string $destinataires Emails séparés par une virgule
     * @param These $these
     * @param string $outputFilePath Chemin vers le fichier stocké en local
     * @return Notification
     */
    public function createNotificationFusionFini(string $destinataires, These $these, string $outputFilePath): Notification
    {
        $to = array_map('trim', explode(',', $destinataires));

        $notif = $this->createNotification();
        $notif
            ->setSubject("Ajout de la page de couverture terminé")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-fusion-fini')
            ->setTemplateVariables([
                'these' => $these,
                'outputFilePath' => $outputFilePath,
                'url' => '',
            ]);

        return $notif;
    }
}