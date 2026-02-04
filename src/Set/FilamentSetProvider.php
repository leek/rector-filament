<?php

declare(strict_types=1);

namespace RectorFilament\Set;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use Rector\Set\ValueObject\Set;

final class FilamentSetProvider implements SetProviderInterface
{
    /**
     * @var string
     */
    private const GROUP_NAME = 'filament';

    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return [
            new ComposerTriggeredSet(
                self::GROUP_NAME,
                'filament/filament',
                '4.0',
                FilamentSetList::FILAMENT_40,
            ),
            new Set(
                self::GROUP_NAME,
                'Code quality',
                FilamentSetList::FILAMENT_CODE_QUALITY,
            ),
        ];
    }
}
