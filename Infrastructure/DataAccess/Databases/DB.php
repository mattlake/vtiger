<?php

declare(strict_types=1);

namespace Infrastructure\DataAccess\Databases;

use Infrastructure\DataAccess\Databases\Contracts\DatabaseContract;

class DB implements DatabaseContract
{
    public string $sample = 'sample text';
}