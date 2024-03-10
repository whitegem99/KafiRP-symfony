<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210501153216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id INT AUTO_INCREMENT NOT NULL, role_type INT NOT NULL, age INT NOT NULL, country VARCHAR(255) NOT NULL, hours VARCHAR(255) NOT NULL, current_server VARCHAR(255) DEFAULT NULL, using_discord TINYINT(1) NOT NULL, extras LONGTEXT DEFAULT NULL, past_experience LONGTEXT DEFAULT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, review_date DATETIME DEFAULT NULL, approved TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE application');
    }
}
