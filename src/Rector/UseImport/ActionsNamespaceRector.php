<?php

declare(strict_types=1);

namespace RectorFilament\Rector\UseImport;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\UseItem;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, action classes moved from Filament\Tables\Actions\*,
 * Filament\Notifications\Actions\*, and Filament\Forms\Actions\*
 * to Filament\Actions\*. This rule updates the use imports.
 */
final class ActionsNamespaceRector extends AbstractRector
{
    private const OLD_PREFIXES = [
        'Filament\\Tables\\Actions',
        'Filament\\Notifications\\Actions',
        'Filament\\Forms\\Actions',
    ];

    private const NEW_PREFIX = 'Filament\\Actions';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update Filament action imports from Tables, Notifications, and Forms namespaces to Filament\Actions\*.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Actions\Action;
use Filament\Forms\Actions\Action;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\Action;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [UseItem::class];
    }

    /**
     * @param UseItem $node
     */
    public function refactor(Node $node): ?Node
    {
        $name = $node->name->toString();

        foreach (self::OLD_PREFIXES as $oldPrefix) {
            if (str_starts_with($name, $oldPrefix)) {
                $newName = self::NEW_PREFIX . substr($name, strlen($oldPrefix));
                $node->name = new Name($newName);

                return $node;
            }
        }

        return null;
    }
}
