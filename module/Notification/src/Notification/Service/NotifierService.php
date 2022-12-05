<?php

namespace Notification\Service;

use DateTime;
use Doctrine\ORM\ORMException;
use Notification\Entity\NotifMail;
use Notification\Entity\Service\NotifEntityServiceAwareTrait;
use Notification\Exception\NotificationException;
use Notification\MessageContainer;
use Notification\Notification;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Service\Mailer\MailerServiceAwareTrait;
use Laminas\Mail\Message;

/**
 * Service d'envoi de notification par mail.
 *
 * @author Unicaen
 */
class NotifierService
{
    use NotifEntityServiceAwareTrait;
    use MailerServiceAwareTrait;
    use EntityManagerAwareTrait;

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
     * @throws \Notification\Exception\NotificationException
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
     * @throws \Notification\Exception\NotificationException
     */
    protected function sendNotification(Notification $notification)
    {
        $email = $this->createMailForNotification($notification);
        $nMail = new NotifMail();

        $mails = [];
        foreach ($email->getFrom() as $mail) { $mails[] = $mail->getEmail(); }
        $nMail->setFrom(implode(',', $mails));
        $mails = [];
        foreach ($email->getTo() as $mail) { $mails[] = $mail->getEmail(); }
        if ($mails === []) $mails = ["unknown"];
        $nMail->setTo(implode(',', $mails));
        $nMail->setSubject($email->getSubject());
        $body = $email->getBodyText();
//        $body = htmlentities($body);
        $nMail->setBody($body);
        $nMail->setSentOn(new DateTime());
        try {
            $this->entityManager->persist($nMail);
            $this->entityManager->flush($nMail);
        } catch (ORMException $e) {
            throw new NotificationException("Erreur rencontrée lors de l'enregistrement dans NotifMail", null, $e);
        }

        try {
            $message = $this->mailerService->send($email);
        } catch (\Exception $e) {
            throw new NotificationException("Erreur rencontrée lors de l'envoi de la notification", null, $e);
        }

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
     * @return \Laminas\Mail\Message
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