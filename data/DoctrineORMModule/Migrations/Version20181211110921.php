<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181211110921 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<EOS
update structure set CHEMIN_LOGO = replace(replace(replace(CHEMIN_LOGO,
  '/ressources/Logos/ED/', ''),
  '/ressources/Logos/UR/', ''),
  '/ressources/Logos/Etab/', '')
where CHEMIN_LOGO is not null
EOS;
        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
