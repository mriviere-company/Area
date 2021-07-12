<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210712130451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E47A25E9820');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E47A25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fleet DROP FOREIGN KEY FK_A05E1E47A25E9820');
        $this->addSql('ALTER TABLE fleet ADD CONSTRAINT FK_A05E1E47A25E9820 FOREIGN KEY (planet_id) REFERENCES planet (id)');
    }
}
