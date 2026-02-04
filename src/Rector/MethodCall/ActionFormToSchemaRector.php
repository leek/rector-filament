<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, Action::form() was renamed to Action::schema().
 * This rule renames ->form([...]) calls on Action classes to ->schema([...]).
 */
final class ActionFormToSchemaRector extends AbstractRector
{
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

        if (! $this->isActionContext($node)) {
            return null;
        }

        $node->name = new Identifier('schema');

        return $node;
    }

    private function isActionContext(MethodCall $node): bool
    {
        // PHPStan type resolution for chained calls like Action::make()->form([...])
        if ($this->isObjectType($node->var, new ObjectType('Filament\\Actions\\Action'))) {
            return true;
        }

        // $this->form([...]) inside a class extending Action
        if ($node->var instanceof Node\Expr\Variable && $this->getName($node->var) === 'this') {
            return $this->isInsideActionClass($node);
        }

        return false;
    }

    private function isInsideActionClass(Node $node): bool
    {
        $parent = $node->getAttribute('parent');
        while ($parent !== null) {
            if ($parent instanceof Class_) {
                if ($parent->extends !== null) {
                    $parentClass = $parent->extends->toString();

                    return str_contains($parentClass, 'Action');
                }

                return false;
            }
            $parent = $parent->getAttribute('parent');
        }

        return false;
    }
}
