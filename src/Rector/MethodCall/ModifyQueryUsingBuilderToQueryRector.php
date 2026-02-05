<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Filament convention uses $query for the parameter in modifyQueryUsing callbacks.
 * This rule renames $builder to $query for consistency.
 *
 * Handles both direct method calls and named arguments:
 *   ->modifyQueryUsing(fn (Builder $builder) => ...)
 *   ->relationship('name', modifyQueryUsing: fn (Builder $builder) => ...)
 */
final class ModifyQueryUsingBuilderToQueryRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename $builder to $query in modifyQueryUsing callbacks.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
->modifyQueryUsing(fn (Builder $builder) => $builder->where('active', true))
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
->modifyQueryUsing(fn (Builder $query) => $query->where('active', true))
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

        $callable = $this->findModifyQueryUsingClosure($node);
        if ($callable === null) {
            return null;
        }

        $builderParam = null;
        foreach ($callable->params as $param) {
            if ($this->getName($param->var) === 'builder') {
                $builderParam = $param;
                break;
            }
        }

        if ($builderParam === null) {
            return null;
        }

        $builderParam->var = new Variable('query');

        $nodes = $callable instanceof Closure ? $callable->stmts : [$callable->expr];

        $this->traverseNodesWithCallable($nodes, function (Node $node): ?Node {
            if ($node instanceof Variable && $node->name === 'builder') {
                return new Variable('query');
            }

            return null;
        });

        return $node;
    }

    private function findModifyQueryUsingClosure(MethodCall $node): Closure|ArrowFunction|null
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        // Direct: ->modifyQueryUsing(fn (Builder $builder) => ...)
        if ($node->name->name === 'modifyQueryUsing') {
            $firstArg = $node->getArgs()[0] ?? null;
            if (! $firstArg instanceof Arg) {
                return null;
            }

            $callable = $firstArg->value;
            if ($callable instanceof Closure || $callable instanceof ArrowFunction) {
                return $callable;
            }

            return null;
        }

        // Named arg: ->relationship('name', modifyQueryUsing: fn (Builder $builder) => ...)
        foreach ($node->getArgs() as $arg) {
            if ($arg->name instanceof Identifier && $arg->name->name === 'modifyQueryUsing') {
                $callable = $arg->value;
                if ($callable instanceof Closure || $callable instanceof ArrowFunction) {
                    return $callable;
                }
            }
        }

        return null;
    }
}
