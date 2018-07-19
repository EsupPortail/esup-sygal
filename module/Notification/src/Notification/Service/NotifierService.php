<?php

namespace Notification\Service;

use Notification\Entity\Service\NotifEntityServiceAwareTrait;
use Notification\MessageContainer;
use Notification\Notification;
use Notification\NotificationRenderer;
use UnicaenApp\Service\Mailer\MailerServiceAwareTrait;

/**
 * Service d'envoi de notification par mail.
 *
 * @author Unicaen
 */
class NotifierService
{
    use NotifEntityServiceAwareTrait;
    use MailerServiceAwareTrait;

    /**
     * @var MessageContainer
     */
    protected $messageContainer;

    /**
     * @var NotificationRenderer
     */
    protected $renderer;

    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var array
     */
    protected $defaultOptions = [
        // préfixe à ajouter systématiquement devant le sujet des mails, ex: '[Sygal] '
        'subject_prefix' => '',

        // destinataires à ajouter systématiquement en copie conforme ou cachée de tous les mails
        'cc'  => [],
        'bcc' => [],
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * NotifierService constructor.
     *
     * @param NotificationRenderer $renderer
     */
    public function __construct(NotificationRenderer $renderer)
    {
        $this->renderer = $renderer;
        $this->messageContainer = new MessageContainer();
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * @param Notification $notification
     */
    public function trigger(Notification $notification)
    {
        $notification->prepare();

        $this->sendNotification($notification);

        // collecte des éventuels messages exposés par la notification
        foreach ($notification->getInfoMessages() as $message) {
            $this->messageContainer->setMessage($message, 'info');
        }
        foreach ($notification->getWarningMessages() as $message) {
            $this->messageContainer->setMessage($message, 'warning');
        }
    }

    /**
     * @param Notification $notification
     */
    protected function sendNotification(Notification $notification)
    {
        $mail = $this->createMailForNotification($notification);

        $this->mailerService->send($mail);
    }

    /**
     * @param Notification $notification
     * @return \Zend\Mail\Message
     */
    protected function createMailForNotification(Notification $notification)
    {
        $subjectPrefix = '';
        if (isset($this->options['subject_prefix'])) {
            $subjectPrefix = $this->options['subject_prefix'];
        }

        $subject = trim($subjectPrefix) . " " . $notification->getSubject();
        $to = $notification->getTo();
        $cc = $notification->getCc();
        $bcc = $notification->getBcc();

        $html = $this->renderer->setNotification($notification)->render();

        $mail = $this->mailerService->createNewMessage($html, $subject);
        $mail->setTo($to);

        if ($cc) {
            $mail->setCc($cc);
        }
        if ($bcc) {
            $mail->setBcc($bcc);
        }

        if (isset($this->options['cc'])) {
            $mail->addBcc($this->options['cc']);
        }
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }

        return $mail;
    }

    /**
     * Retourne les éventuels messages exposés lors de la notification.
     *
     * @see MessageContainer::getMessages()
     *
     * @return array
     */
    public function getLogs()
    {
        return $this->messageContainer->getMessages();
    }

    /**
     * @param NotificationFactory $notificationFactory
     * @return self
     */
    public function setNotificationFactory(NotificationFactory $notificationFactory)
    {
        $this->notificationFactory = $notificationFactory;

        return $this;
    }

    /**
     * @return NotificationFactory
     */
    public function getNotificationFactory()
    {
        return $this->notificationFactory;
    }
}