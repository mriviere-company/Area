<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180509220655 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fleet ADD flightType VARCHAR(255) DEFAULT NULL, CHANGE name name VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE planet CHANGE name name VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fleet DROP flightType, CHANGE name name VARCHAR(15) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE planet CHANGE name name VARCHAR(15) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
