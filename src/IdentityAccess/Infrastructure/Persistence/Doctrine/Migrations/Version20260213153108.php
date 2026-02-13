<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213153108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin account type to refresh token table for identity access module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TYPE refresh_token_account_type ADD VALUE 'admin' AFTER 'module'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TYPE refresh_token_account_type RENAME TO refresh_token_account_type_old');

        $this->addSql("CREATE TYPE refresh_token_account_type AS ENUM ('user', 'module')");

        $this->addSql('DELETE FROM refresh_tokens WHERE account_type::text = \'admin\'');

        $this->addSql(
            'ALTER TABLE refresh_tokens
            ALTER COLUMN account_type TYPE refresh_token_account_type
            USING account_type::text::refresh_token_account_type'
        );

        $this->addSql('DROP TYPE refresh_token_account_type_old');
    }
}
