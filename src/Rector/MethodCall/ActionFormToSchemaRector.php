<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use RectorFilament\AbstractRector;
use RectorFilament\Rector\Concerns\DetectsFilamentContext;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, Action::form() was renamed to Action::schema().
 * This rule renames ->form([...]) calls on Action and Filter classes to ->schema([...]).
 */
final class ActionFormToSchemaRector extends AbstractRector
{
    use DetectsFilamentContext;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename ->form() to ->schema() on Filament Action classes.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
Action::make('export')
    ->form([
        TextInput::make('name'),
    ])
    ->action(fn () => null);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
Action::make('export')
    ->schema([
        TextInput::make('name'),
    ])
    ->action(fn () => null);
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

        if ($node->name->name !== 'form') {
            return null;
        }

        if (! $this->isActionOrFilterContext($node)) {
            return null;
        }

        $node->name = new Identifier('schema');

        return $node;
    }

    private function isActionOrFilterContext(MethodCall $node): bool
    {
        // Use trait's context detection
        if ($this->isFilamentContext($node)) {
            return true;
        }

        // $this->form([...]) inside a class extending Action or Filter
        if ($node->var instanceof Node\Expr\Variable && $this->getName($node->var) === 'this') {
            return $this->isInsideActionOrFilterClass($node);
        }

        return false;
    }

    private function isInsideActionOrFilterClass(Node $node): bool
    {
        $parent = $node->getAttribute('parent');
        while ($parent !== null) {
            if ($parent instanceof Class_) {
                if ($parent->extends !== null) {
                    $parentClass = $parent->extends->toString();

                    return str_contains($parentClass, 'Action')
                        || str_contains($parentClass, 'Filter');
                }

                return false;
            }
            $parent = $parent->getAttribute('parent');
        }

        return false;
    }
}
