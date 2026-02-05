<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\ActionFormToSchemaRector;
use RectorFilament\Rector\UseImport\ActionsNamespaceRector;
use RectorFilament\Rector\UseImport\GetSetNamespaceRector;
use RectorFilament\Rector\UseImport\SchemaComponentsNamespaceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(ActionFormToSchemaRector::class);
    $rectorConfig->rule(ActionsNamespaceRector::class);
    $rectorConfig->rule(SchemaComponentsNamespaceRector::class);
    $rectorConfig->rule(GetSetNamespaceRector::class);
};
