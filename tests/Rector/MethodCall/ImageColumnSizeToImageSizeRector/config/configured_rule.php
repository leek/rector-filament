<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorFilament\Rector\MethodCall\ImageColumnSizeToImageSizeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ImageColumnSizeToImageSizeRector::class);
};
