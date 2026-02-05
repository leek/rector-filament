<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\UseImport\SchemaComponentsNamespaceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SchemaComponentsNamespaceRector::class);
};
