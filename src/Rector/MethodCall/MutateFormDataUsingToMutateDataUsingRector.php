<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorFilament\AbstractRector;
use RectorFilament\Rector\Concerns\DetectsFilamentContext;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, ->mutateFormDataUsing() was renamed to ->mutateDataUsing().
 */
final class MutateFormDataUsingToMutateDataUsingRector extends AbstractRector
{
    use DetectsFilamentContext;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename ->mutateFormDataUsing() to ->mutateDataUsing() on Filament Actions.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
Action::make('create')
    ->mutateFormDataUsing(fn (array $data) => $data);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
Action::make('create')
    ->mutateDataUsing(fn (array $data) => $data);
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

        if ($node->name->name !== 'mutateFormDataUsing') {
            return null;
        }

        if (! $this->isFilamentContext($node)) {
            return null;
        }

        $node->name = new Identifier('mutateDataUsing');

        return $node;
    }
}
