<?php

namespace Application\Service\Notification;

use Application\Entity\Db\Individu;
use Application\Entity\Db\These;
use Application\Service\Notification\Notification;
use Application\Service\Env\EnvServiceAwareInterface;
use Application\Service\Env\EnvServiceAwareTrait;
use Application\Service\MailerService;
use Application\Service\MailerServiceAwareInterface;
use Application\Service\MailerServiceAwareTrait;
use Application\Notification\ValidationRdvBuNotification;
use UnicaenApp\Traits\MessageAwareTrait;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

/**
 * Service d'envoi de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationService implements EnvServiceAwareInterface, MailerServiceAwareInterface
{
    use EnvServiceAwareTrait;
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
        $to = $this->envService->findOneByAnnee()->getEmailBU();
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
        $viewModel = (new ViewModel())
            ->setTemplate('application/these/mail/notif-evenement-import')
            ->setVariables([
                'data' => $data,
                'subject' => "Résultats de thèses modifiés",
                'message' => "Vous êtes informé-e que des modifications de résultats de thèses ont été détectées lors de la synchro avec Apogée.",
            ]);

        $env = $this->envService->findOneByAnnee();
        $to = $env->getEmailBdD();
        $bcc = 'bertrand.gauthier@unicaen.fr';

        $viewModel->setVariable('to', $to);
        $viewModel->setVariable('bcc', $bcc);

        $this->notifier($viewModel);

        return $this;
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

            $env = $this->envService->findOneByAnnee();

            $viewModel->setVariable('contact', $env->getEmailBdD());

            $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getEmail();
            $bcc = 'bertrand.gauthier@unicaen.fr';

            $viewModel->setVariable('to', $to);
            $viewModel->setVariable('bcc', $bcc);

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
        $bcc = 'bertrand.gauthier@unicaen.fr';

        $viewModel->setVariable('to', $to);
        $viewModel->setVariable('bcc', $bcc);

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

        $env = $this->envService->findOneByAnnee();

        $to = $env->getEmailBdD();
        $bcc = 'bertrand.gauthier@unicaen.fr';

        $viewModel->setVariable('to', $to);
        $viewModel->setVariable('bcc', $bcc);

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
        $env = $this->envService->findOneByAnnee();

        /** @var Individu[] $unknownMails */
        $unknownMails = [];
        $to = $these->getDirecteursTheseEmails($unknownMails);
        $cc = $env->getEmailBdD();
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
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierValidationCorrectionThese(ViewModel $viewModel)
    {
        $env = $this->envService->findOneByAnnee();

        $to = $env->getEmailBdD();
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
     * @return static
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierValidationCorrectionTheseEtudiant(ViewModel $viewModel, These $these)
    {
        $env = $this->envService->findOneByAnnee();

        $to = $these->getDoctorant()->getEmail() ;
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
     * @return $this
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierBU(ViewModel $mailViewModel)
    {
        $to = $this->envService->findOneByAnnee()->getEmailBU();

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
     * @return $this
     * @deprecated Utiliser trigger(Notification)
     */
    public function notifierBdD(ViewModel $mailViewModel)
    {
        $to = $this->envService->findOneByAnnee()->getEmailBdD();

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

        $subject = "[SoDoct] " . $viewModel->getVariable('subject');
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
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }

        $this->mailerService->send($mail);
    }

    public function trigger(Notification $notification)
    {
        $env = $this->envService->findOneByAnnee();

        $notification->prepare(['env' => $env]);
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
}