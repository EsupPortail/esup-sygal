<?php

namespace Notification\Service;

use Notification\Entity\Service\NotifEntityServiceAwareTrait;
use Notification\MessageContainer;
use Notification\Notification;
use UnicaenApp\Service\Mailer\MailerServiceAwareTrait;
use Zend\Mail\Message;

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
     * @var NotificationRenderingService
     */
    protected $renderingService;

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
     * @param NotificationRenderingService $renderingService
     */
    public function __construct(NotificationRenderingService $renderingService)
    {
        $this->renderingService = $renderingService;
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

        $message = $this->mailerService->send($mail);

        $sendDate = $this->extractDateFromMessage($message) ?: new \DateTime();
        $notification->setSendDate($sendDate);
    }

    /**
     * @param Message $message
     * @return \DateTime|null
     */
    private function extractDateFromMessage(Message $message)
    {
        if ($message->getHeaders()->has('Date')) {
            $messageDate = $message->getHeaders()->get('Date')->getFieldValue();

            return date_create_from_format($messageDate, 'r');
        }

        return null;
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

        $html = $this->renderingService->setNotification($notification)->render();

        $mail = $this->mailerService->createNewMessage($html, $subject);
        $mail->setTo($to);

        if ($cc AND $cc !== []) {
            $mail->setCc($cc);
        }
        if ($bcc AND $bcc !== []) {
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