<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260322131055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Colonnes déjà existantes en BDD — up() vidé pour éviter le conflit';
    }

    public function up(Schema $schema): void
    {
        // author_name, author_email et is_verified existent déjà en BDD
        // On vide le up() pour ne pas déclencher "Column already exists"
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP author_name, DROP author_email, CHANGE author_id author_id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP is_verified');
    }
}