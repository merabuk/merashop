<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202083724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table for users module';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }
}
