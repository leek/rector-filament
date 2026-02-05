<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, the second argument of Table::filters() (the layout)
 * was moved to a dedicated ->filtersLayout() method call.
 *
 * This rule removes the second argument from ->filters() and chains
 * a ->filtersLayout() call with that argument.
 */
final class FiltersLayoutArgToMethodRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Extract second arg of ->filters() into chained ->filtersLayout() call on Table.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$table->filters([
    Filter::make('active'),
], layout: FiltersLayout::AboveContent);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$table->filters([
    Filter::make('active'),
])->filtersLayout(FiltersLayout::AboveContent);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        if ($node->name->name !== 'filters') {
            return null;
        }

        if (! $this->isObjectType($node->var, new ObjectType('Filament\\Tables\\Table'))) {
            return null;
        }

        $args = $node->getArgs();
        if (count($args) < 2) {
            return null;
        }

        // Resolve by named argument when present, fall back to positional order
        $filtersArg = null;
        $layoutArg = null;

        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                match ($arg->name->name) {
                    'layout' => $layoutArg = $arg,
                    'filters' => $filtersArg = $arg,
                    default => null,
                };
            }
        }

        // Fall back to positional: filters is 1st, layout is 2nd
        if ($filtersArg === null) {
            $filtersArg = $args[0];
        }
        if ($layoutArg === null) {
            $layoutArg = $args[1] ?? null;
        }

        if ($layoutArg === null) {
            return null;
        }

        // Keep only the filters argument on ->filters()
        $node->args = [new Arg($filtersArg->value)];

        // Chain ->filtersLayout($layoutArg) after ->filters()
        return new MethodCall(
            $node,
            new Identifier('filtersLayout'),
            [new Arg($layoutArg->value)]
        );
    }
}
