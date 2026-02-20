<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$indent = '  ';
$managers = [
    'catalog' => 'config/migrations/catalog.php',
    'customer' => 'config/migrations/customer.php',
    'email_sender' => 'config/migrations/email_sender.php',
    'identity_access' => 'config/migrations/identity_access.php',
];

echo 'Preparing test databases...'.PHP_EOL;

foreach ($managers as $em => $config) {
    echo "{$indent}Migrating {$em}...".PHP_EOL;

    if (!file_exists($config)) {
        echo "{$indent}{$indent}No configuration found for {$em}!".PHP_EOL;

        continue;
    }

    $process = new Process([
        'php',
        'bin/console',
        'doctrine:migrations:migrate',
        "--em={$em}",
        "--configuration={$config}",
        '--no-interaction',
        '--env=test',
    ]);

    $process->setTimeout(300);

    $process->run(function ($type, $buffer) use ($indent) {
        if (Process::ERR === $type) {
            echo "{$indent}{$indent}[ERROR] ".$buffer;
        } else {
            echo $indent.$indent.$buffer;
        }
    });

    if (!$process->isSuccessful()) {
        echo "Error migrating {$em}!".PHP_EOL;
        exit(1);
    }
}

echo 'All databases are migrated and ready!'.PHP_EOL.PHP_EOL;
