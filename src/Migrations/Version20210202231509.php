<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210202231509 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_users DROP FOREIGN KEY FK_C25028243349A583');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E473349A583');
        $this->addSql('ALTER TABLE planet DROP FOREIGN KEY FK_68136AA53349A583');
        $this->addSql('CREATE TABLE heroe (id INT AUTO_INCREMENT NOT NULL, capture TINYINT(1) NOT NULL, name VARCHAR(25) NOT NULL, cost INT UNSIGNED NOT NULL, level SMALLINT UNSIGNED NOT NULL, speed SMALLINT UNSIGNED NOT NULL, shield SMALLINT UNSIGNED NOT NULL, armor SMALLINT UNSIGNED NOT NULL, laser SMALLINT UNSIGNED NOT NULL, missile SMALLINT UNSIGNED NOT NULL, plasma SMALLINT UNSIGNED NOT NULL, `precision` SMALLINT UNSIGNED NOT NULL, niobium SMALLINT UNSIGNED NOT NULL, water SMALLINT UNSIGNED NOT NULL, food SMALLINT UNSIGNED NOT NULL, uranium SMALLINT UNSIGNED NOT NULL, bitcoin SMALLINT UNSIGNED NOT NULL, warPoint SMALLINT UNSIGNED NOT NULL, worker SMALLINT UNSIGNED NOT NULL, soldier SMALLINT UNSIGNED NOT NULL, tank SMALLINT UNSIGNED NOT NULL, scientist SMALLINT UNSIGNED NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, warPoint BIGINT UNSIGNED DEFAULT NULL, bitcoin BIGINT UNSIGNED DEFAULT NULL, sonde BIGINT UNSIGNED DEFAULT NULL, cargoI INT UNSIGNED DEFAULT NULL, cargoV INT UNSIGNED DEFAULT NULL, cargoX BIGINT UNSIGNED DEFAULT NULL, colonizer SMALLINT UNSIGNED DEFAULT NULL, recycleur INT UNSIGNED DEFAULT NULL, barge INT UNSIGNED DEFAULT NULL, moonMaker SMALLINT UNSIGNED DEFAULT NULL, radarShip INT UNSIGNED DEFAULT NULL, brouilleurShip INT UNSIGNED DEFAULT NULL, motherShip SMALLINT UNSIGNED DEFAULT NULL, hunter BIGINT UNSIGNED DEFAULT NULL, hunterHeavy BIGINT UNSIGNED DEFAULT NULL, hunterWar BIGINT UNSIGNED DEFAULT NULL, corvet BIGINT UNSIGNED DEFAULT NULL, corvetLaser BIGINT UNSIGNED DEFAULT NULL, corvetWar BIGINT UNSIGNED DEFAULT NULL, fregate BIGINT UNSIGNED DEFAULT NULL, fregatePlasma BIGINT UNSIGNED DEFAULT NULL, croiser BIGINT UNSIGNED DEFAULT NULL, ironClad BIGINT UNSIGNED DEFAULT NULL, destroyer BIGINT UNSIGNED DEFAULT NULL, soldier INT UNSIGNED DEFAULT NULL, tank SMALLINT UNSIGNED DEFAULT NULL, worker INT UNSIGNED DEFAULT NULL, scientist SMALLINT UNSIGNED DEFAULT NULL, nuclear_bomb SMALLINT UNSIGNED DEFAULT NULL, niobium BIGINT UNSIGNED DEFAULT NULL, water BIGINT UNSIGNED DEFAULT NULL, food BIGINT UNSIGNED DEFAULT NULL, uranium INT UNSIGNED DEFAULT NULL, teleport TINYINT(1) NOT NULL, speedup INT UNSIGNED DEFAULT NULL, noobShield INT UNSIGNED DEFAULT NULL, heroeXp INT UNSIGNED DEFAULT NULL, heroeStar TINYINT(1) NOT NULL, resetShip TINYINT(1) NOT NULL, sword VARCHAR(35) DEFAULT NULL, body VARCHAR(35) DEFAULT NULL, foot VARCHAR(35) DEFAULT NULL, head VARCHAR(35) DEFAULT NULL, gun VARCHAR(35) DEFAULT NULL, rarity SMALLINT UNSIGNED NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE commander');
        $this->addSql('DROP TABLE nickname_list');
        $this->addSql('DROP INDEX UNIQ_C25028243349A583 ON app_users');
        $this->addSql('ALTER TABLE app_users CHANGE commander_id heroe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_users ADD CONSTRAINT FK_C250282477D25060 FOREIGN KEY (heroe_id) REFERENCES heroe (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C250282477D25060 ON app_users (heroe_id)');
        $this->addSql('DROP INDEX UNIQ_A05E1E473349A583 ON fleet');
        $this->addSql('ALTER TABLE fleet CHANGE commander_id heroe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E4777D25060 FOREIGN KEY (heroe_id) REFERENCES heroe (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A05E1E4777D25060 ON fleet (heroe_id)');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23CA76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id)');
        $this->addSql('CREATE INDEX IDX_9067F23CA76ED395 ON mission (user_id)');
        $this->addSql('DROP INDEX UNIQ_68136AA53349A583 ON planet');
        $this->addSql('ALTER TABLE planet CHANGE commander_id heroe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT FK_68136AA577D25060 FOREIGN KEY (heroe_id) REFERENCES heroe (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68136AA577D25060 ON planet (heroe_id)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) NOT NULL');
        $this->addSql('ALTER TABLE server ADD production NUMERIC(28, 3) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_users DROP FOREIGN KEY FK_C250282477D25060');
        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E4777D25060');
        $this->addSql('ALTER TABLE planet DROP FOREIGN KEY FK_68136AA577D25060');
        $this->addSql('CREATE TABLE commander (id INT AUTO_INCREMENT NOT NULL, capture TINYINT(1) NOT NULL, name VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, level SMALLINT UNSIGNED NOT NULL, speed SMALLINT UNSIGNED NOT NULL, shield SMALLINT UNSIGNED NOT NULL, armor SMALLINT UNSIGNED NOT NULL, laser SMALLINT UNSIGNED NOT NULL, missile SMALLINT UNSIGNED NOT NULL, plasma SMALLINT UNSIGNED NOT NULL, niobium SMALLINT UNSIGNED NOT NULL, water SMALLINT UNSIGNED NOT NULL, bitcoin SMALLINT UNSIGNED NOT NULL, worker SMALLINT UNSIGNED NOT NULL, soldier SMALLINT UNSIGNED NOT NULL, cost INT UNSIGNED NOT NULL, food SMALLINT UNSIGNED NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nickname_list (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(16) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sessions (sess_id VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, sess_data BLOB NOT NULL, sess_time INT UNSIGNED NOT NULL, sess_lifetime INT UNSIGNED NOT NULL, PRIMARY KEY(sess_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE heroe');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP INDEX UNIQ_C250282477D25060 ON app_users');
        $this->addSql('ALTER TABLE app_users CHANGE heroe_id commander_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_users ADD CONSTRAINT FK_C25028243349A583 FOREIGN KEY (commander_id) REFERENCES commander (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C25028243349A583 ON app_users (commander_id)');
        $this->addSql('DROP INDEX UNIQ_A05E1E4777D25060 ON fleet');
        $this->addSql('ALTER TABLE fleet CHANGE heroe_id commander_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E473349A583 FOREIGN KEY (commander_id) REFERENCES commander (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A05E1E473349A583 ON fleet (commander_id)');
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23CA76ED395');
        $this->addSql('DROP INDEX IDX_9067F23CA76ED395 ON mission');
        $this->addSql('DROP INDEX UNIQ_68136AA577D25060 ON planet');
        $this->addSql('ALTER TABLE planet CHANGE heroe_id commander_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD CONSTRAINT FK_68136AA53349A583 FOREIGN KEY (commander_id) REFERENCES commander (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68136AA53349A583 ON planet (commander_id)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE series series CHAR(88) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`');
        $this->addSql('ALTER TABLE server DROP production');
    }
}
