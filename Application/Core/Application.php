<?php

declare(strict_types=1);

namespace Application\Core;

use Config\DIConfig;
use DI\ContainerBuilder;
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

        /**
         * Build the Dependency Injection Container
         */
        $this->container = $this->initDependencyInjectionContainer();
    }

    private function initDependencyInjectionContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(DIConfig::definitions());
        $container = $containerBuilder->build();

        return $container;
    }

    public static function boot(): Application
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}