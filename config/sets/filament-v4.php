<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\ActionFormToSchemaRector;
use RectorFilament\Rector\MethodCall\BulkActionsToToolbarActionsRector;
use RectorFilament\Rector\MethodCall\FiltersLayoutArgToMethodRector;
use RectorFilament\Rector\MethodCall\LivewireComponentParamNameRector;
use RectorFilament\Rector\MethodCall\ModelToRecordClosureParamRector;
use RectorFilament\Rector\MethodCall\TableActionsToRecordActionsRector;
use RectorFilament\Rector\UseImport\ActionsNamespaceRector;
use RectorFilament\Rector\UseImport\GetSetNamespaceRector;
use RectorFilament\Rector\UseImport\SchemaComponentsNamespaceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(ActionFormToSchemaRector::class);
    $rectorConfig->rule(ActionsNamespaceRector::class);
    $rectorConfig->rule(SchemaComponentsNamespaceRector::class);
    $rectorConfig->rule(GetSetNamespaceRector::class);
    $rectorConfig->rule(ModelToRecordClosureParamRector::class);
    $rectorConfig->rule(LivewireComponentParamNameRector::class);
    $rectorConfig->rule(TableActionsToRecordActionsRector::class);
    $rectorConfig->rule(BulkActionsToToolbarActionsRector::class);
    $rectorConfig->rule(FiltersLayoutArgToMethodRector::class);
};
