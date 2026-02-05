<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use RectorFilament\AbstractRector;
use RectorFilament\Rector\Concerns\DetectsFilamentContext;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, closure parameters type-hinted as Livewire\Component
 * should be named $livewire. This rule renames any such parameter to
 * $livewire and updates all usages within the closure body.
 */
final class LivewireComponentParamNameRector extends AbstractRector
{
    use DetectsFilamentContext;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename Livewire\Component closure parameters to $livewire in Filament contexts.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Livewire\Component;

TextInput::make('name')
    ->visible(function (Component $component) {
        return $component->getData();
    });
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Livewire\Component;

TextInput::make('name')
    ->visible(function (Component $livewire) {
        return $livewire->getData();
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
        if ($node->isFirstClassCallable()) {
            return null;
        }

        if (! $this->isFilamentContext($node)) {
            return null;
        }

        $changed = false;

        foreach ($node->getArgs() as $arg) {
            $callable = $arg->value;
            if (! $callable instanceof Closure && ! $callable instanceof ArrowFunction) {
                continue;
            }

            foreach ($callable->params as $param) {
                $paramName = $this->getName($param->var);
                if ($paramName === null || $paramName === 'livewire') {
                    continue;
                }

                if (! $this->isLivewireComponentType($param->type)) {
                    continue;
                }

                $this->renameClosureParam($callable, $param, $paramName, 'livewire');
                $changed = true;
            }
        }

        if (! $changed) {
            return null;
        }

        return $node;
    }

    private function isLivewireComponentType(?Node $type): bool
    {
        if ($type === null) {
            return false;
        }

        $innerType = $type instanceof NullableType ? $type->type : $type;

        if (! $innerType instanceof FullyQualified && ! $innerType instanceof Node\Name) {
            return false;
        }

        $typeName = $innerType->toString();

        return $typeName === 'Livewire\\Component';
    }
}
