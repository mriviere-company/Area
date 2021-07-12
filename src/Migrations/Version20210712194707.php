<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210712194707 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ally_allied DROP FOREIGN KEY FK_161AB9BB1C6E3E76');
        $this->addSql('ALTER TABLE ally_allied ADD CONSTRAINT FK_161AB9BB1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF011C6E3E76');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE exchange DROP FOREIGN KEY FK_D33BB0791C6E3E76');
        $this->addSql('ALTER TABLE exchange ADD CONSTRAINT FK_D33BB0791C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E471136BE75');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E471136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE fleet_list DROP FOREIGN KEY FK_8BDD93A51136BE75');
        $this->addSql('ALTER TABLE fleet_list ADD CONSTRAINT FK_8BDD93A51136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) NOT NULL');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77841136BE75');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77841136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AA1136BE75');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AA1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ally_allied DROP FOREIGN KEY FK_161AB9BB1C6E3E76');
        $this->addSql('ALTER TABLE ally_allied ADD CONSTRAINT FK_161AB9BB1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF011C6E3E76');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE exchange DROP FOREIGN KEY FK_D33BB0791C6E3E76');
        $this->addSql('ALTER TABLE exchange ADD CONSTRAINT FK_D33BB0791C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E471136BE75');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E471136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE fleet_list DROP FOREIGN KEY FK_8BDD93A51136BE75');
        $this->addSql('ALTER TABLE fleet_list ADD CONSTRAINT FK_8BDD93A51136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77841136BE75');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77841136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AA1136BE75');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AA1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
    }
}
