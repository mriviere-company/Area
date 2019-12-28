<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191228213453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE stats (id INT AUTO_INCREMENT NOT NULL, points BIGINT NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fleet ADD food BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE planet ADD food INT NOT NULL, ADD foodMax INT NOT NULL, ADD fdProduction NUMERIC(28, 5) NOT NULL, ADD farm SMALLINT DEFAULT NULL, ADD aeroponicFarm SMALLINT DEFAULT NULL, ADD silos SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_users ADD aeroponicFarm SMALLINT DEFAULT NULL, CHANGE bot bot TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE nickname_list CHANGE pseudo pseudo VARCHAR(16) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE stats');
        $this->addSql('ALTER TABLE app_users DROP aeroponicFarm, CHANGE bot bot TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE fleet DROP food');
        $this->addSql('ALTER TABLE nickname_list CHANGE pseudo pseudo VARCHAR(16) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`');
        $this->addSql('ALTER TABLE planet DROP food, DROP foodMax, DROP fdProduction, DROP farm, DROP aeroponicFarm, DROP silos');
    }
}
