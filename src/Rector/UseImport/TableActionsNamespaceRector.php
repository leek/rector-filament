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
 * In Filament v4, action classes moved from Filament\Tables\Actions\*
 * to Filament\Actions\*. This rule updates the use imports.
 */
final class TableActionsNamespaceRector extends AbstractRector
{
    private const OLD_PREFIX = 'Filament\\Tables\\Actions';

    private const NEW_PREFIX = 'Filament\\Actions';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update Filament\Tables\Actions\* imports to Filament\Actions\*.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
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

        if (! str_starts_with($name, self::OLD_PREFIX)) {
            return null;
        }

        $newName = self::NEW_PREFIX . substr($name, strlen(self::OLD_PREFIX));

        $node->name = new Name($newName);

        return $node;
    }
}
