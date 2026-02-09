<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\MutateFormDataUsingToMutateDataUsingRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MutateFormDataUsingToMutateDataUsingRector::class);
};
