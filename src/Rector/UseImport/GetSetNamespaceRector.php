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
 * In Filament v4, the Get and Set utility classes moved from Filament\Forms\*
 * to Filament\Schemas\Components\Utilities\*. This rule updates the use imports.
 */
final class GetSetNamespaceRector extends AbstractRector
{
    private const CLASS_MAP = [
        'Filament\\Forms\\Get' => 'Filament\\Schemas\\Components\\Utilities\\Get',
        'Filament\\Forms\\Set' => 'Filament\\Schemas\\Components\\Utilities\\Set',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update Filament\Forms\{Get,Set} imports to Filament\Schemas\Components\Utilities\*.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Filament\Forms\Get;
use Filament\Forms\Set;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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

        if (! isset(self::CLASS_MAP[$name])) {
            return null;
        }

        $node->name = new Name(self::CLASS_MAP[$name]);

        return $node;
    }
}
