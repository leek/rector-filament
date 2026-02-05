<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, Table::actions() was renamed to Table::recordActions().
 * This rule renames ->actions(...) calls on Table to ->recordActions(...).
 */
final class TableActionsToRecordActionsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename ->actions() to ->recordActions() on Filament Table.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$table->actions([
    EditAction::make(),
]);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$table->recordActions([
    EditAction::make(),
]);
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

        if ($node->name->name !== 'actions') {
            return null;
        }

        if (! $this->isObjectType($node->var, new ObjectType('Filament\\Tables\\Table'))) {
            return null;
        }

        $node->name = new Identifier('recordActions');

        return $node;
    }
}
