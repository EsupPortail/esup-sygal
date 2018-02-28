<?php

namespace Application\Service\Notification;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\Individu;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\Variable;
use Application\Service\Notification\Notification;
use Application\Service\MailerService;
use Application\Service\MailerServiceAwareInterface;
use Application\Service\MailerServiceAwareTrait;
use Application\Notification\ValidationRdvBuNotification;
use Application\Service\Variable\VariableServiceAwareInterface;
use Application\Service\Variable\VariableServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;
use Zend\Db\Sql\Predicate\In;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

/**
 * Service d'envoi de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationService implements VariableServiceAwareInterface, MailerServiceAwareInterface
{
    use VariableServiceAwareTrait;
    use MailerServiceAwareTrait;
    use MessageAwareTrait;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * NotificationService constructor.
     *
     * @param MailerService     $mailerService
     * @param RendererInterface $renderer
     */
    public function __construct(MailerService $mailerService, RendererInterface $renderer)
    {
        $this->setMailerService($mailerService);
        $this->renderer = $renderer;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Notification à l'issu de la saisie des informations par le doctorant pour la prise de rendez-vous BU.
     *
     * @param ViewModel $viewModel
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierSaisieRdvBUParDoctoroant(ViewModel $viewModel)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BU, $this->getThese());

        $to = $variable->getValeur();
        $viewModel->setVariable('to', $to);

        $this->notifier($viewModel);

        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé à la BU (%s).",
            $to
        );

        $this->setMessage($infoMessage, 'info');

        return $this;
    }

    /**
     * @param array $data
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierBdDUpdateResultat(array $data)
    {
        $these = current($data)['these'];
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);
        $to = $variable->getValeur();

        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-evenement-import')
            ->setVariables([
                'data' => $data,
                'subject' => "Résultats de thèses modifiés",
                'message' => "Vous êtes informé-e que des modifications de résultats de thèses ont été détectées lors de la synchro avec Apogée.",
            ]);

        $viewModel->setVariable('to', $to);

        $this->notifier($viewModel);

        return $this;
    }

    /**
     * @param string               $destinataires   Emails séparés par une virgule
     * @param Fichier              $fichierRetraite Fichier retraité concerné
     * @param ValiditeFichier|null $validite        Résultat du test d'archivabilité éventuel
     * @return ViewModel
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierRetraitementFini($destinataires, Fichier $fichierRetraite, ValiditeFichier $validite = null)
    {
        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-retraitement-fini')
            ->setVariables([
                'subject' => "Retraitement terminé",
                'fichierRetraite' => $fichierRetraite,
                'validite' => $validite,
                'url' => '',
            ]);

        $to = array_map('trim', explode(',', $destinataires));
        $viewModel->setVariable('to', $to);

        $this->notifier($viewModel);

        return $viewModel;
    }

    /**
     * @param array $data
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierDoctorantResultatAdmis(array $data)
    {
        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-resultat-admis-doctorant')
            ->setVariables([
                'subject' => "Votre dossier est complet",
            ]);

        foreach ($data as $array) {
            $these = $array['these']; /* @var These $these */
            $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);
            $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getEmail();

            $viewModel->setVariable('contact', $variable->getValeur());
            $viewModel->setVariable('to', $to);

            $this->notifier($viewModel);
        }

        return $this;
    }

    /**
     * @param ViewModel $viewModel
     * @param These     $these
     * @param bool      $directeursTheseEnCopie
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierCorrectionAttendue(ViewModel $viewModel, These $these, $directeursTheseEnCopie = false)
    {
        $viewModel
            ->setTemplate('application/these/mail/notif-depot-version-corrigee-attendu')
            ->setVariables([
                'these' => $these,
            ]);

        $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getEmail();

        $viewModel->setVariable('to', $to);

        if ($directeursTheseEnCopie) {
            $viewModel->setVariable('cc', $these->getDirecteursTheseEmails());
        }

        $this->notifier($viewModel);

        return $this;
    }

    /**
     * @param ViewModel $viewModel
     * @param These     $these
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierDateButoirCorrectionDepassee(ViewModel $viewModel, These $these)
    {
        $viewModel
            ->setTemplate('application/these/mail/notif-date-butoir-correction-depassee')
            ->setVariables([
                'these' => $these,
            ]);

        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);
        $to = $variable->getValeur();

        $viewModel->setVariable('to', $to);

        $this->notifier($viewModel);

        return $this;
    }

    /**
     * @param ViewModel $viewModel
     * @param These     $these
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierValidationDepotTheseCorrigee(ViewModel $viewModel, These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);

        /** @var Individu[] $unknownMails */
        $unknownMails = [];
        $to = $these->getDirecteursTheseEmails($unknownMails);
        $cc = $variable->getValeur();
        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé au(x) directeur(s) de thèse (%s) avec copie au Bureau des Doctorats (%s)",
            implode(',', $to),
            $cc
        );

        $errorMessage = null;
        if (count($unknownMails)) {
            $temp = current($unknownMails);
            $source = $temp->getSource();
            $errorMessage = sprintf(
                "<strong>NB:</strong> Les directeurs de thèses suivants n'ont pas pu être notifiés " .
                "car leur adresse mail n'est pas connue dans %s : <br> %s",
                $source,
                implode(',', $unknownMails)
            );
        }

        $viewModel->setVariable('to', $to);
        $viewModel->setVariable('cc', $cc);
        $viewModel->setVariable('message', $errorMessage);
        $this->notifier($viewModel);

        $this->setMessages([
            'info' => $infoMessage,
        ]);
        if ($errorMessage) {
            $this->addMessages([
                'danger' => $errorMessage,
            ]);
        }

        return $this;
    }

    /**
     * @param ViewModel $viewModel
     * @param These     $these
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierValidationCorrectionThese(ViewModel $viewModel, These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);

        $to = $variable->getValeur();
        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé aux Bureau des Doctorats (%s)",
            $to
        );

        $viewModel->setVariable('to', $to);
        $this->notifier($viewModel);

        $this->setMessage($infoMessage, 'info');

        return $this;
    }

    /**
     * @param ViewModel $viewModel
     * @param These     $these
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierValidationCorrectionTheseEtudiant(ViewModel $viewModel, These $these)
    {
        $to = $these->getDoctorant()->getEmailPro() ;
        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé à votre doctorant (%s)",
            $to
        );

        $viewModel->setVariable('to', $to);
        $this->notifier($viewModel);

        if ($this->getMessage()) {
            $new_message = "<ul><li>".$this->getMessage() . "</li><li>" . $infoMessage . "</li></ul>";
            $this->setMessage($new_message, 'info');
        } else {
            $this->setMessage($infoMessage, 'info');
        }

        return $this;
    }

    /**
     * @param ViewModel $mailViewModel
     * @param These     $these
     * @return $this
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierBU(ViewModel $mailViewModel, These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BU, $these);
        $to = $variable->getValeur();

        $infoMessage = sprintf(
            "Un mail de notification vient d'être envoyé à la BU (%s).",
            $to
        );

        $mailViewModel->setVariable('to', $to);
        $this->notifier($mailViewModel);

        $this->setMessage($infoMessage, 'info');

        return $this;
    }

    /**
     * @param ViewModel $mailViewModel
     * @param These     $these
     * @return $this
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierBdD(ViewModel $mailViewModel, These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);
        $to = $variable->getValeur();

        $mailViewModel->setVariable('to', $to);
        $this->notifier($mailViewModel);

        return $this;
    }

    /**
     * @param ViewModel $viewModel
     * @deprecated Passer à NotificationService::trigger
     */
    private function notifier(ViewModel $viewModel)
    {
        $html = $this->renderer->render($viewModel);

        $subject = "[SyGAL] " . $viewModel->getVariable('subject');

        $to = $viewModel->getVariable('to');
        $cc = $viewModel->getVariable('cc');
        $bcc = $viewModel->getVariable('bcc');

        $mail = $this->mailerService->createNewMessage($html, $subject);
        $mail->setTo($to);

        if ($cc) {
            $mail->setCc($cc);
        }
        if ($bcc) {
            $mail->setBcc($bcc);
        }

        if (isset($this->options['cc'])) {
            $mail->addCc($this->options['cc']);
        }
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }
        $this->mailerService->send($mail);
    }

    public function trigger(Notification $notification)
    {
        $notification->prepare();
        $html = $this->renderNotification($notification);

        $subject = "[SoDoct] " . $notification->getSubject();
        $to = $notification->getTo();
        $cc = $notification->getCc();
        $bcc = $notification->getBcc();

        $mail = $this->mailerService->createNewMessage($html, $subject);
        $mail->setTo($to);
        if ($cc) {
            $mail->setCc($cc);
        }
        if ($bcc) {
            $mail->setBcc($bcc);
        }
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }

        $this->mailerService->send($mail);

        if ($message = $notification->getResultMessage()) {
            $this->setMessage($message, 'info');
        }
    }

    private function renderNotification(Notification $notification)
    {
        $viewModel = $notification->createViewModel();

        $html = $this->renderer->render($viewModel);

        return $html;
    }

    /**
     * @return array
     * @see MessageAwareTrait::getMessages()
     */
    public function getLogs()
    {
        return $this->getMessages();
    }

    public function notifierLogoAbsentEcoleDoctorale(EcoleDoctorale $ecole) {

        $libelle = $ecole->getLibelle();
        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-logo-absent')
            ->setVariables([
                'subject' => "Logo manquant pour l'ED [".$libelle."]",
                'type' => "l'école doctorale",
                'libelle' => $libelle,
            ]);

        $mails = [];
        foreach ($ecole->getEcoleDoctoraleIndividus() as $individu) {
            /** @var EcoleDoctoraleIndividu $individu */
            $email = $individu->getIndividu()->getEmail();
            if ($email !== null) $mails[] = $email;

        }

        $viewModel->setVariable('to', $mails);
        $this->notifier($viewModel);
    }

    public function notifierLogoAbsentUniteRecherche(UniteRecherche $unite) {

        $libelle = $unite->getLibelle();
        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-logo-absent')
            ->setVariables([
                'subject' => "Logo manquant pour l'UR [".$libelle."]",
                'type' => "l'unité de recherche",
                'libelle' => $libelle,
            ]);

        $mails = [];
        foreach ($unite->getUniteRechercheIndividus() as $individu) {
            /** @var EcoleDoctoraleIndividu $individu */
            $email = $individu->getIndividu()->getEmail();
            if ($email !== null) $mails[] = $email;

        }

        $viewModel->setVariable('to', $mails);
        $this->notifier($viewModel);
    }

    public function notifierLogoAbsentEtablissement(Etablissement $etablissement) {

        $libelle = $etablissement->getLibelle();
        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-logo-absent')
            ->setVariables([
                'subject' => "Logo manquant pour l'Etab [".$libelle."]",
                'type' => "l'établissement",
                'libelle' => $libelle,
            ]);

        //TODO ne pas laisser en dur ... (mail les administrateurs techniques de l'établissement)
        $mails = ["jean-philippe.metivier@unicaen.fr", "bertrand.gauthier@unicaen.fr"];
        $viewModel->setVariable('to', $mails);
        $this->notifier($viewModel);
    }
}