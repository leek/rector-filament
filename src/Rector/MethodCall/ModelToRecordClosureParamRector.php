<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use RectorFilament\AbstractRector;
use RectorFilament\Rector\Concerns\DetectsFilamentContext;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, the conventional closure parameter name changed from
 * $model to $record. This rule renames $model → $record in Filament
 * closure parameters and updates all usages within the closure body.
 */
final class ModelToRecordClosureParamRector extends AbstractRector
{
    use DetectsFilamentContext;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename $model to $record in Filament closure parameters.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
TextInput::make('name')
    ->visible(function ($model) {
        return $model->is_active;
    });
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
TextInput::make('name')
    ->visible(function ($record) {
        return $record->is_active;
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
        if (! $this->isFilamentContext($node)) {
            return null;
        }

        $changed = false;

        foreach ($node->getArgs() as $arg) {
            $callable = $arg->value;
            if (! $callable instanceof Closure && ! $callable instanceof ArrowFunction) {
                continue;
            }

            $hasRecord = false;
            $modelParam = null;
            foreach ($callable->params as $param) {
                $paramName = $this->getName($param->var);
                if ($paramName === 'record') {
                    $hasRecord = true;
                }
                if ($paramName === 'model') {
                    $modelParam = $param;
                }
            }

            if ($modelParam === null || $hasRecord) {
                continue;
            }

            $this->renameClosureParam($callable, $modelParam, 'model', 'record');
            $changed = true;
        }

        if (! $changed) {
            return null;
        }

        return $node;
    }
}
