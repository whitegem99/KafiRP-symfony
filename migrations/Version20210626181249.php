<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210626181249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ticket_image (ticket_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_ED0D67D4700047D2 (ticket_id), INDEX IDX_ED0D67D43DA5256D (image_id), PRIMARY KEY(ticket_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_message_image (ticket_message_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_7B5F5728C5E9817D (ticket_message_id), INDEX IDX_7B5F57283DA5256D (image_id), PRIMARY KEY(ticket_message_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticket_image ADD CONSTRAINT FK_ED0D67D4700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket_image ADD CONSTRAINT FK_ED0D67D43DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket_message_image ADD CONSTRAINT FK_7B5F5728C5E9817D FOREIGN KEY (ticket_message_id) REFERENCES ticket_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket_message_image ADD CONSTRAINT FK_7B5F57283DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ticket_image');
        $this->addSql('DROP TABLE ticket_message_image');
    }
}
