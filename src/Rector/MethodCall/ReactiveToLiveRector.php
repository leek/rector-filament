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
 * In Filament v4, ->reactive() was renamed to ->live().
 */
final class ReactiveToLiveRector extends AbstractRector
{
    use DetectsFilamentContext;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename ->reactive() to ->live() on Filament components.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
TextInput::make('name')->reactive();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
TextInput::make('name')->live();
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

        if ($node->name->name !== 'reactive') {
            return null;
        }

        if (! $this->isFilamentContext($node)) {
            return null;
        }

        $node->name = new Identifier('live');

        return $node;
    }
}
