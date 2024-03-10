<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210509014828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD text_question_id INT DEFAULT NULL, ADD choice_question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EE6176EC FOREIGN KEY (text_question_id) REFERENCES text_question (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA46B3B4F FOREIGN KEY (choice_question_id) REFERENCES choice_question (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494EE6176EC ON question (text_question_id)');
        $this->addSql('CREATE INDEX IDX_B6F7494EA46B3B4F ON question (choice_question_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EE6176EC');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA46B3B4F');
        $this->addSql('DROP INDEX IDX_B6F7494EE6176EC ON question');
        $this->addSql('DROP INDEX IDX_B6F7494EA46B3B4F ON question');
        $this->addSql('ALTER TABLE question DROP text_question_id, DROP choice_question_id');
    }
}
