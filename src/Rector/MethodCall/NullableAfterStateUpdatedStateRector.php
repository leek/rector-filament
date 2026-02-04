<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * The $state parameter in afterStateUpdated callbacks can be null when a field
 * is cleared. This rule ensures the type hint is nullable to prevent type errors.
 */
final class NullableAfterStateUpdatedStateRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make $state parameter nullable in afterStateUpdated callbacks.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
TextInput::make('name')
    ->afterStateUpdated(function (string $state) {
        // ...
    });
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
TextInput::make('name')
    ->afterStateUpdated(function (?string $state) {
        // ...
    });
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

        if ($node->name->name !== 'afterStateUpdated') {
            return null;
        }

        $firstArg = $node->getArgs()[0] ?? null;
        if (! $firstArg instanceof Node\Arg) {
            return null;
        }

        $callable = $firstArg->value;
        if (! $callable instanceof Closure && ! $callable instanceof ArrowFunction) {
            return null;
        }

        $changed = false;

        foreach ($callable->params as $param) {
            if ($this->getName($param->var) !== 'state') {
                continue;
            }

            if (! $param->type instanceof Identifier) {
                continue;
            }

            if ($param->type->name !== 'string') {
                continue;
            }

            $param->type = new NullableType($param->type);
            $changed = true;
        }

        if (! $changed) {
            return null;
        }

        return $node;
    }
}
