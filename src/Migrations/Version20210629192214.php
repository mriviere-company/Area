<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210629192214 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE salon_server (salon_id INT NOT NULL, server_id INT NOT NULL, INDEX IDX_A09299A4C91BDE4 (salon_id), INDEX IDX_A09299A1844E6B7 (server_id), PRIMARY KEY(salon_id, server_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE salon_server ADD CONSTRAINT FK_A09299A4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE salon_server ADD CONSTRAINT FK_A09299A1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE salon DROP FOREIGN KEY FK_F268F4171844E6B7');
        $this->addSql('DROP INDEX UNIQ_F268F4171844E6B7 ON salon');
        $this->addSql('ALTER TABLE salon DROP server_id');
        $this->addSql('ALTER TABLE server DROP FOREIGN KEY FK_5A6DD5F64C91BDE4');
        $this->addSql('DROP INDEX UNIQ_5A6DD5F64C91BDE4 ON server');
        $this->addSql('ALTER TABLE server DROP salon_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE salon_server');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('ALTER TABLE salon ADD server_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE salon ADD CONSTRAINT FK_F268F4171844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F268F4171844E6B7 ON salon (server_id)');
        $this->addSql('ALTER TABLE server ADD salon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE server ADD CONSTRAINT FK_5A6DD5F64C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A6DD5F64C91BDE4 ON server (salon_id)');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
