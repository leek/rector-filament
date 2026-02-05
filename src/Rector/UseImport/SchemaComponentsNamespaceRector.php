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
 * In Filament v4, certain form components moved from Filament\Forms\Components\*
 * to Filament\Schemas\Components\*. This rule updates the use imports for
 * Fieldset, Grid, and Section.
 */
final class SchemaComponentsNamespaceRector extends AbstractRector
{
    private const CLASS_MAP = [
        'Filament\\Forms\\Components\\Fieldset' => 'Filament\\Schemas\\Components\\Fieldset',
        'Filament\\Forms\\Components\\Grid' => 'Filament\\Schemas\\Components\\Grid',
        'Filament\\Forms\\Components\\Section' => 'Filament\\Schemas\\Components\\Section',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update Filament\Forms\Components\{Fieldset,Grid,Section} imports to Filament\Schemas\Components\*.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
