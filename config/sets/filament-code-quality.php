<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\FilamentUtilityInjectionTypeRector;
use RectorFilament\Rector\MethodCall\NullableAfterStateUpdatedStateRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(NullableAfterStateUpdatedStateRector::class);
    $rectorConfig->rule(FilamentUtilityInjectionTypeRector::class);
};
