<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210503214834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD acting_user_id INT DEFAULT NULL, ADD is_deleted TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493EAD8611 FOREIGN KEY (acting_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6493EAD8611 ON user (acting_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493EAD8611');
        $this->addSql('DROP INDEX IDX_8D93D6493EAD8611 ON user');
        $this->addSql('ALTER TABLE user DROP acting_user_id, DROP is_deleted, DROP deleted_at');
    }
}
