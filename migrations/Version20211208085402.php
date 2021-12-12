<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211208085402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD deadline DATETIME DEFAULT NULL, ADD client VARCHAR(255) NOT NULL, ADD priority VARCHAR(255) NOT NULL, ADD realisation_date DATETIME DEFAULT NULL, ADD status VARCHAR(255) NOT NULL, ADD evils VARCHAR(255) NOT NULL, ADD heros VARCHAR(255) NOT NULL, DROP done');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD done TINYINT(1) NOT NULL, DROP deadline, DROP client, DROP priority, DROP realisation_date, DROP status, DROP evils, DROP heros');
    }
}
