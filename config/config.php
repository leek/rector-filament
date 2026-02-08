<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;

return static function (RectorConfig $rectorConfig): void {
    // RenameParamToMatchTypeRector is dangerous in Filament/Livewire projects.
    // It renames method parameters to match their type hints (e.g. $record → $clientProfile
    // for ?ClientProfile), which silently breaks:
    //   - Livewire mount() params (matched by name from Blade @livewire directives)
    //   - Filament closure params (injected by name: $record, $state, $livewire, etc.)
    $rectorConfig->skip([
        RenameParamToMatchTypeRector::class,
    ]);
};
