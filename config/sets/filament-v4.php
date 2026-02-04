<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\ActionFormToSchemaRector;
use RectorFilament\Rector\UseImport\TableActionsNamespaceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(ActionFormToSchemaRector::class);
    $rectorConfig->rule(TableActionsNamespaceRector::class);
};
