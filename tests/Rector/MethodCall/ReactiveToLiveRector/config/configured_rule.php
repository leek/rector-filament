<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\ReactiveToLiveRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReactiveToLiveRector::class);
};
