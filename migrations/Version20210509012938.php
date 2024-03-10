<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210509012938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE choice_question_option (choice_question_id INT NOT NULL, option_id INT NOT NULL, INDEX IDX_35B26697A46B3B4F (choice_question_id), INDEX IDX_35B26697A7C41D6F (option_id), PRIMARY KEY(choice_question_id, option_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE choice_question_option ADD CONSTRAINT FK_35B26697A46B3B4F FOREIGN KEY (choice_question_id) REFERENCES choice_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE choice_question_option ADD CONSTRAINT FK_35B26697A7C41D6F FOREIGN KEY (option_id) REFERENCES `option` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `option` DROP value');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE choice_question_option');
        $this->addSql('ALTER TABLE `option` ADD value VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
