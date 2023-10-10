<?php

namespace App\Tests;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

use function dirname;
use function passthru;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$projectDir = dirname(__DIR__);
(new Filesystem())->remove("$projectDir/var/test-data.db");
passthru('APP_ENV=test php test-app/bin/console doctrine:schema:update --quiet --dump-sql --force');
