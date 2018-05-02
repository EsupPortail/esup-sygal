create sequence NOTIF_ID_SEQ;

delete from notif;

insert into NOTIF(ID, CODE, DESCRIPTION, DESTINATAIRES, TEMPLATE)
  select
    NOTIF_ID_SEQ.nextval,
    'notif-depot-these',
    'Notification lorsqu''un fichier de thèse est téléversé',
    'CODE_DESTINATAIRES',
    '<p>
    Bonjour,
</p>
<p>
    Ceci est un mail envoyé automatiquement par l''application <?php echo $appName ?>.
</p>
<p>
    Vous êtes informé-e que <em><?php echo $version->toString() ?></em> de la thèse de <?php echo $these->getDoctorant() ?> vient d''être déposée.
</p>
<p>
    Cliquez sur <a href="<?php echo $url ?>">ce lien</a> pour accéder à la page correspondante de l''application.
</p>
'
  from dual
;

