<?php

declare(strict_types=1);

namespace Keboola\CreateEmptyTablesProcessor;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getTables(): array
    {
        return $this->getValue(['parameters', 'tables']);
    }
}
