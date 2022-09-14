<?php

declare(strict_types=1);

namespace Config;

use Infrastructure\DataAccess\Databases\Contracts\DatabaseContract;
use Infrastructure\DataAccess\Databases\DB;

class DIConfig
{
    public static function definitions(): array
    {
        return [
            DatabaseContract::class => new DB(),
        ];
    }
}
