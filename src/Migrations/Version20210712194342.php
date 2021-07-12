<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210712194342 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ally_peace DROP FOREIGN KEY FK_ABA304FC1C6E3E76');
        $this->addSql('ALTER TABLE ally_peace ADD CONSTRAINT FK_ABA304FC1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ally_pna DROP FOREIGN KEY FK_B8D574011C6E3E76');
        $this->addSql('ALTER TABLE ally_pna ADD CONSTRAINT FK_B8D574011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ally_war DROP FOREIGN KEY FK_BEBC3F951C6E3E76');
        $this->addSql('ALTER TABLE ally_war ADD CONSTRAINT FK_BEBC3F951C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE construction DROP FOREIGN KEY FK_DC91E26EA25E9820');
        $this->addSql('ALTER TABLE construction ADD CONSTRAINT FK_DC91E26EA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE destination DROP FOREIGN KEY FK_3EC63EAAA25E9820');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAAA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E471BFC2D80');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E471BFC2D80 FOREIGN KEY (fleet_list_id) REFERENCES fleet_list (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE341C6E3E76');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE341C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) NOT NULL');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E1136BE75');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E4C91BDE4');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ally_peace DROP FOREIGN KEY FK_ABA304FC1C6E3E76');
        $this->addSql('ALTER TABLE ally_peace ADD CONSTRAINT FK_ABA304FC1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE ally_pna DROP FOREIGN KEY FK_B8D574011C6E3E76');
        $this->addSql('ALTER TABLE ally_pna ADD CONSTRAINT FK_B8D574011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE ally_war DROP FOREIGN KEY FK_BEBC3F951C6E3E76');
        $this->addSql('ALTER TABLE ally_war ADD CONSTRAINT FK_BEBC3F951C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE construction DROP FOREIGN KEY FK_DC91E26EA25E9820');
        $this->addSql('ALTER TABLE construction ADD CONSTRAINT FK_DC91E26EA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
        $this->addSql('ALTER TABLE destination DROP FOREIGN KEY FK_3EC63EAAA25E9820');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAAA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E471BFC2D80');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E471BFC2D80 FOREIGN KEY (fleet_list_id) REFERENCES fleet_list (id)');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE341C6E3E76');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE341C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E1136BE75');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E4C91BDE4');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
    }
}
