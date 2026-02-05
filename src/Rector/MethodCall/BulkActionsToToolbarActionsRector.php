<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, Table::bulkActions([...]) was replaced with
 * Table::toolbarActions([BulkActionGroup::make([...])]).
 *
 * This rule wraps the original array argument inside a BulkActionGroup::make()
 * call. If the argument is already a BulkActionGroup::make() call, it is left
 * as-is.
 */
final class BulkActionsToToolbarActionsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Transform ->bulkActions([...]) to ->toolbarActions([BulkActionGroup::make([...])]) on Table.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$table->bulkActions([
    DeleteBulkAction::make(),
]);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$table->toolbarActions([
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
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
        if ($node->isFirstClassCallable()) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if ($node->name->name !== 'bulkActions') {
            return null;
        }

        if (! $this->isObjectType($node->var, new ObjectType('Filament\\Tables\\Table'))) {
            return null;
        }

        $args = $node->getArgs();
        if (count($args) === 0) {
            return null;
        }

        $firstArg = $args[0]->value;

        // If the argument is an array that already contains a BulkActionGroup::make(),
        // just rename the method — no wrapping needed.
        if ($firstArg instanceof Array_ && $this->arrayContainsBulkActionGroup($firstArg)) {
            $node->name = new Identifier('toolbarActions');

            return $node;
        }

        // If the argument is a bare BulkActionGroup::make() (not in an array),
        // wrap it in an array and rename.
        if ($this->isBulkActionGroupMake($firstArg)) {
            $node->name = new Identifier('toolbarActions');
            $node->args = [new Arg(new Array_([new Node\Expr\ArrayItem($firstArg)]))];

            return $node;
        }

        // Otherwise, wrap the actions in BulkActionGroup::make() and an array.
        $bulkActionGroupCall = new StaticCall(
            new FullyQualified('Filament\\Tables\\Actions\\BulkActionGroup'),
            'make',
            [new Arg($firstArg)]
        );

        $node->args = [new Arg(new Array_([new Node\Expr\ArrayItem($bulkActionGroupCall)]))];
        $node->name = new Identifier('toolbarActions');

        return $node;
    }

    private function arrayContainsBulkActionGroup(Array_ $array): bool
    {
        foreach ($array->items as $item) {
            if ($item !== null && $this->isBulkActionGroupMake($item->value)) {
                return true;
            }
        }

        return false;
    }

    private function isBulkActionGroupMake(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->name instanceof Identifier || $node->name->name !== 'make') {
            return false;
        }

        if (! $node->class instanceof FullyQualified && ! $node->class instanceof Node\Name) {
            return false;
        }

        $className = $node->class->toString();

        return $className === 'Filament\\Tables\\Actions\\BulkActionGroup'
            || str_ends_with($className, 'BulkActionGroup');
    }
}
