<?php

namespace ApplicationUnitTest\Mail;

class SendMailTest extends \PHPUnit_Framework_TestCase
{
    public function testSendMail()
    {
        $ok = mail('bertrandgauthier.vpc@free.fr', 'test envoi', 'ceci est un test', null, 'O DeliveryMode=b');
        var_dump($ok);
        exec('cat /var/log/exim4/mainlog', $output);
        var_dump(array_pop($output));
    }
}
