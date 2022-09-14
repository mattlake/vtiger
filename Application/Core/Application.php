<?php

declare(strict_types=1);

namespace Application\Core;

use Dotenv\Dotenv;

class Application
{
    private static ?Application $instance = null;
    private $rootDirectory = __DIR__ . '/../../';

    private function __construct()
    {
        $this->bootstrap();
    }

    private function bootstrap()
    {
        /**
         * Load Environmental Variables
         */
        Dotenv::createImmutable($this->rootDirectory)->load();
    }

    public static function getInstance(): Application
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}