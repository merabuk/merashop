<?php

declare(strict_types=1);

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
    'migrations_paths' => [
        'App\Customer\Infrastructure\Persistence\Doctrine\Migrations' => 'src/Customer/Infrastructure/Persistence/Doctrine/Migrations',
    ],
    'all_or_nothing' => true,
    'transactional' => true,
    'check_database_platform' => true,
];
