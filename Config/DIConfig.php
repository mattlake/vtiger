<?php

declare(strict_types=1);

namespace Config;

use Infrastructure\DataAccess\Databases\Contracts\DatabaseContract;

// TODO clean up and remove requires when PSR4 is fully implemented
require_once __DIR__.'/../include/database/PearDatabase.php';


class DIConfig
{
    public static function definitions(): array
    {
        return [
            DatabaseContract::class => \PearDatabase::getInstance(),
        ];
    }
}
