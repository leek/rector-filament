<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\BulkActionsToToolbarActionsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(BulkActionsToToolbarActionsRector::class);
};
