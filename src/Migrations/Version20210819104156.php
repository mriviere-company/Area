<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819104156 extends AbstractMigration
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
        $this->addSql('ALTER TABLE ally_peace DROP FOREIGN KEY FK_ABA304FC1C6E3E76');
        $this->addSql('ALTER TABLE ally_peace ADD CONSTRAINT FK_ABA304FC1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ally_pna DROP FOREIGN KEY FK_B8D574011C6E3E76');
        $this->addSql('ALTER TABLE ally_pna ADD CONSTRAINT FK_B8D574011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ally_proposal DROP FOREIGN KEY FK_D6F70BC11136BE75');
        $this->addSql('ALTER TABLE ally_proposal DROP FOREIGN KEY FK_D6F70BC11C6E3E76');
        $this->addSql('ALTER TABLE ally_proposal ADD CONSTRAINT FK_D6F70BC11136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ally_proposal ADD CONSTRAINT FK_D6F70BC11C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ally_war DROP FOREIGN KEY FK_BEBC3F951C6E3E76');
        $this->addSql('ALTER TABLE ally_war ADD CONSTRAINT FK_BEBC3F951C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF01C256317D');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF011844E6B7');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF011C6E3E76');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF01A76ED395');
        $this->addSql('DROP INDEX UNIQ_3B15EF01C256317D ON app_character');
        $this->addSql('ALTER TABLE app_character DROP ship_id');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF011844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF01A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE construction DROP FOREIGN KEY FK_DC91E26EA25E9820');
        $this->addSql('ALTER TABLE construction ADD CONSTRAINT FK_DC91E26EA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE destination DROP FOREIGN KEY FK_3EC63EAA4B061DF9');
        $this->addSql('ALTER TABLE destination DROP FOREIGN KEY FK_3EC63EAAA25E9820');
        $this->addSql('DROP INDEX UNIQ_3EC63EAA4B061DF9 ON destination');
        $this->addSql('ALTER TABLE destination DROP fleet_id');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAAA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA71844E6B7');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA71844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE exchange DROP FOREIGN KEY FK_D33BB0791C6E3E76');
        $this->addSql('ALTER TABLE exchange ADD CONSTRAINT FK_D33BB0791C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE fleet_list DROP FOREIGN KEY FK_8BDD93A51136BE75');
        $this->addSql('ALTER TABLE fleet_list ADD CONSTRAINT FK_8BDD93A51136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE galaxy DROP FOREIGN KEY FK_F6BB13761844E6B7');
        $this->addSql('ALTER TABLE galaxy ADD CONSTRAINT FK_F6BB13761844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE341C6E3E76');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE341C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE heroe DROP FOREIGN KEY FK_EB5092211136BE75');
        $this->addSql('ALTER TABLE heroe DROP FOREIGN KEY FK_EB5092214B061DF9');
        $this->addSql('ALTER TABLE heroe DROP FOREIGN KEY FK_EB509221A25E9820');
        $this->addSql('DROP INDEX UNIQ_EB5092214B061DF9 ON heroe');
        $this->addSql('DROP INDEX UNIQ_EB5092211136BE75 ON heroe');
        $this->addSql('DROP INDEX UNIQ_EB509221A25E9820 ON heroe');
        $this->addSql('ALTER TABLE heroe DROP character_id, DROP fleet_id, DROP planet_id');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F1136BE75');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23C1136BE75');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23C1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE planet DROP FOREIGN KEY FK_68136AA54584665A');
        $this->addSql('DROP INDEX UNIQ_68136AA54584665A ON planet');
        $this->addSql('ALTER TABLE planet DROP product_id');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA25E9820');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rank DROP FOREIGN KEY FK_8879E8E51136BE75');
        $this->addSql('DROP INDEX UNIQ_8879E8E51136BE75 ON rank');
        $this->addSql('ALTER TABLE rank DROP character_id');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) NOT NULL');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77841136BE75');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77841136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE s_content DROP FOREIGN KEY FK_C088E5031136BE75');
        $this->addSql('ALTER TABLE s_content ADD CONSTRAINT FK_C088E5031136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE salon DROP FOREIGN KEY FK_F268F4171844E6B7');
        $this->addSql('ALTER TABLE salon ADD CONSTRAINT FK_F268F4171844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sector DROP FOREIGN KEY FK_4BA3D9E8B61FAB2');
        $this->addSql('ALTER TABLE sector ADD CONSTRAINT FK_4BA3D9E8B61FAB2 FOREIGN KEY (galaxy_id) REFERENCES galaxy (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE ships DROP FOREIGN KEY FK_27F71B311136BE75');
        $this->addSql('ALTER TABLE ships ADD CONSTRAINT FK_27F71B311136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AA1136BE75');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AA1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E1136BE75');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E4C91BDE4');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ally_allied DROP FOREIGN KEY FK_161AB9BB1C6E3E76');
        $this->addSql('ALTER TABLE ally_allied ADD CONSTRAINT FK_161AB9BB1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE ally_peace DROP FOREIGN KEY FK_ABA304FC1C6E3E76');
        $this->addSql('ALTER TABLE ally_peace ADD CONSTRAINT FK_ABA304FC1C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE ally_pna DROP FOREIGN KEY FK_B8D574011C6E3E76');
        $this->addSql('ALTER TABLE ally_pna ADD CONSTRAINT FK_B8D574011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE ally_proposal DROP FOREIGN KEY FK_D6F70BC11C6E3E76');
        $this->addSql('ALTER TABLE ally_proposal DROP FOREIGN KEY FK_D6F70BC11136BE75');
        $this->addSql('ALTER TABLE ally_proposal ADD CONSTRAINT FK_D6F70BC11C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE ally_proposal ADD CONSTRAINT FK_D6F70BC11136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE ally_war DROP FOREIGN KEY FK_BEBC3F951C6E3E76');
        $this->addSql('ALTER TABLE ally_war ADD CONSTRAINT FK_BEBC3F951C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF011C6E3E76');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF01A76ED395');
        $this->addSql('ALTER TABLE app_character DROP FOREIGN KEY FK_3B15EF011844E6B7');
        $this->addSql('ALTER TABLE app_character ADD ship_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF01C256317D FOREIGN KEY (ship_id) REFERENCES ships (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF011C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF01A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE app_character ADD CONSTRAINT FK_3B15EF011844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B15EF01C256317D ON app_character (ship_id)');
        $this->addSql('ALTER TABLE construction DROP FOREIGN KEY FK_DC91E26EA25E9820');
        $this->addSql('ALTER TABLE construction ADD CONSTRAINT FK_DC91E26EA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
        $this->addSql('ALTER TABLE destination DROP FOREIGN KEY FK_3EC63EAAA25E9820');
        $this->addSql('ALTER TABLE destination ADD fleet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAA4B061DF9 FOREIGN KEY (fleet_id) REFERENCES fleet (id)');
        $this->addSql('ALTER TABLE destination ADD CONSTRAINT FK_3EC63EAAA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EC63EAA4B061DF9 ON destination (fleet_id)');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA71844E6B7');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA71844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE exchange DROP FOREIGN KEY FK_D33BB0791C6E3E76');
        $this->addSql('ALTER TABLE exchange ADD CONSTRAINT FK_D33BB0791C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE fleet_list DROP FOREIGN KEY FK_8BDD93A51136BE75');
        $this->addSql('ALTER TABLE fleet_list ADD CONSTRAINT FK_8BDD93A51136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE galaxy DROP FOREIGN KEY FK_F6BB13761844E6B7');
        $this->addSql('ALTER TABLE galaxy ADD CONSTRAINT FK_F6BB13761844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE341C6E3E76');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE341C6E3E76 FOREIGN KEY (ally_id) REFERENCES ally (id)');
        $this->addSql('ALTER TABLE heroe ADD character_id INT DEFAULT NULL, ADD fleet_id INT DEFAULT NULL, ADD planet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heroe ADD CONSTRAINT FK_EB5092211136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE heroe ADD CONSTRAINT FK_EB5092214B061DF9 FOREIGN KEY (fleet_id) REFERENCES fleet (id)');
        $this->addSql('ALTER TABLE heroe ADD CONSTRAINT FK_EB509221A25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB5092214B061DF9 ON heroe (fleet_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB5092211136BE75 ON heroe (character_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB509221A25E9820 ON heroe (planet_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F1136BE75');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23C1136BE75');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23C1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE planet ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT FK_68136AA54584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68136AA54584665A ON planet (product_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA25E9820');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
        $this->addSql('ALTER TABLE rank ADD character_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rank ADD CONSTRAINT FK_8879E8E51136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8879E8E51136BE75 ON rank (character_id)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77841136BE75');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77841136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE s_content DROP FOREIGN KEY FK_C088E5031136BE75');
        $this->addSql('ALTER TABLE s_content ADD CONSTRAINT FK_C088E5031136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE salon DROP FOREIGN KEY FK_F268F4171844E6B7');
        $this->addSql('ALTER TABLE salon ADD CONSTRAINT FK_F268F4171844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE sector DROP FOREIGN KEY FK_4BA3D9E8B61FAB2');
        $this->addSql('ALTER TABLE sector ADD CONSTRAINT FK_4BA3D9E8B61FAB2 FOREIGN KEY (galaxy_id) REFERENCES galaxy (id)');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE ships DROP FOREIGN KEY FK_27F71B311136BE75');
        $this->addSql('ALTER TABLE ships ADD CONSTRAINT FK_27F71B311136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AA1136BE75');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AA1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E1136BE75');
        $this->addSql('ALTER TABLE view DROP FOREIGN KEY FK_FEFDAB8E4C91BDE4');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E1136BE75 FOREIGN KEY (character_id) REFERENCES app_character (id)');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
    }
}
