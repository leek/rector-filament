<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use RectorFilament\AbstractRector;
use RectorFilament\Rector\Concerns\DetectsFilamentContext;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, ->label('') on components should become ->hiddenLabel(),
 * and ->label('') on Actions/BulkActions should become ->iconButton().
 * Table Columns are skipped (no hiddenLabel() method).
 */
final class EmptyLabelToHiddenLabelRector extends AbstractRector
{
    use DetectsFilamentContext;

    /**
     * @var array<string>
     */
    private const ACTION_CLASSES = [
        'Action',
        'BulkAction',
        'Filament\\Actions\\Action',
        'Filament\\Actions\\BulkAction',
    ];

    /**
     * @var array<string>
     */
    private const COLUMN_CLASSES = [
        'TextColumn',
        'ImageColumn',
        'IconColumn',
        'ColorColumn',
        'SelectColumn',
        'ToggleColumn',
        'CheckboxColumn',
        'TextInputColumn',
        'Filament\\Tables\\Columns\\TextColumn',
        'Filament\\Tables\\Columns\\ImageColumn',
        'Filament\\Tables\\Columns\\IconColumn',
        'Filament\\Tables\\Columns\\ColorColumn',
        'Filament\\Tables\\Columns\\SelectColumn',
        'Filament\\Tables\\Columns\\ToggleColumn',
        'Filament\\Tables\\Columns\\CheckboxColumn',
        'Filament\\Tables\\Columns\\TextInputColumn',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace ->label(\'\') with ->hiddenLabel() on components or ->iconButton() on Actions.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
TextInput::make('name')->label('');
Action::make('delete')->label('');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
TextInput::make('name')->hiddenLabel();
Action::make('delete')->iconButton();
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

        if ($node->name->name !== 'label') {
            return null;
        }

        if (! $this->isEmptyStringLabel($node)) {
            return null;
        }

        $rootClassName = $this->resolveChainRootClassName($node);

        if ($rootClassName === null) {
            return null;
        }

        // Skip table columns — they don't have hiddenLabel()
        if ($this->isColumnClass($rootClassName)) {
            return null;
        }

        // Actions/BulkActions → ->iconButton()
        if ($this->isActionClass($rootClassName)) {
            $node->name = new Identifier('iconButton');
            $node->args = [];

            return $node;
        }

        // Only transform if this is a Filament schema/form component
        if (! $this->isComponentClass($rootClassName)) {
            return null;
        }

        // Components → ->hiddenLabel()
        $node->name = new Identifier('hiddenLabel');
        $node->args = [];

        return $node;
    }

    private function isEmptyStringLabel(MethodCall $node): bool
    {
        if (count($node->args) !== 1) {
            return false;
        }

        $arg = $node->args[0];

        if (! $arg instanceof Node\Arg) {
            return false;
        }

        return $arg->value instanceof String_ && $arg->value->value === '';
    }

    private function resolveChainRootClassName(MethodCall $node): ?string
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        if (! $current instanceof StaticCall) {
            return null;
        }

        return $this->resolveClassName($current->class);
    }

    private function resolveClassName(Node $class): ?string
    {
        if ($class instanceof FullyQualified) {
            return $class->toString();
        }

        if ($class instanceof Name) {
            return $class->toString();
        }

        return null;
    }

    private function isActionClass(string $className): bool
    {
        foreach (self::ACTION_CLASSES as $actionClass) {
            if ($className === $actionClass) {
                return true;
            }
        }

        return false;
    }

    private function isComponentClass(string $className): bool
    {
        return str_starts_with($className, 'Filament\\Schemas\\Components\\')
            || str_starts_with($className, 'Filament\\Forms\\Components\\');
    }

    private function isColumnClass(string $className): bool
    {
        foreach (self::COLUMN_CLASSES as $columnClass) {
            if ($className === $columnClass) {
                return true;
            }
        }

        // Also match any class ending with "Column" from Filament namespace
        if (str_ends_with($className, 'Column') && str_starts_with($className, 'Filament\\Tables\\Columns\\')) {
            return true;
        }

        return false;
    }
}
