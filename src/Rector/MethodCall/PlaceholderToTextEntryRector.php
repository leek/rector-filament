<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\UseUse;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, Placeholder was replaced by TextEntry from the Infolists package,
 * and the ->content() method was renamed to ->state().
 */
final class PlaceholderToTextEntryRector extends AbstractRector
{
    private const OLD_CLASS = 'Filament\\Forms\\Components\\Placeholder';

    private const NEW_CLASS = 'Filament\\Infolists\\Components\\TextEntry';

    private const OLD_SHORT = 'Placeholder';

    private const NEW_SHORT = 'TextEntry';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace Placeholder::make()->content() with TextEntry::make()->state().', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Filament\Forms\Components\Placeholder;

Placeholder::make('name')->content('value');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Filament\Infolists\Components\TextEntry;

TextEntry::make('name')->state('value');
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [UseUse::class, StaticCall::class, MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof UseUse) {
            return $this->refactorUseUse($node);
        }

        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }

        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }

        return null;
    }

    private function refactorUseUse(UseUse $node): ?UseUse
    {
        if ($node->name->toString() !== self::OLD_CLASS) {
            return null;
        }

        $node->name = new Name(self::NEW_CLASS);

        return $node;
    }

    private function refactorStaticCall(StaticCall $node): ?StaticCall
    {
        if (! $this->isPlaceholderClass($node->class)) {
            return null;
        }

        // Use short name — the UseUse handler ensures the correct import exists.
        $node->class = new Name(self::NEW_SHORT);

        return $node;
    }

    private function refactorMethodCall(MethodCall $node): ?MethodCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if ($node->name->name !== 'content') {
            return null;
        }

        if (! $this->isOnPlaceholderChain($node)) {
            return null;
        }

        $node->name = new Identifier('state');

        return $node;
    }

    private function isOnPlaceholderChain(MethodCall $node): bool
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        if (! $current instanceof StaticCall) {
            return false;
        }

        // Check for both old and new class names since the StaticCall
        // may have already been renamed in the same traversal pass.
        return $this->isPlaceholderClass($current->class)
            || $this->isTextEntryClass($current->class);
    }

    private function isPlaceholderClass(Node $class): bool
    {
        if ($class instanceof FullyQualified) {
            return $class->toString() === self::OLD_CLASS;
        }

        if ($class instanceof Name) {
            return $class->toString() === self::OLD_SHORT;
        }

        return false;
    }

    private function isTextEntryClass(Node $class): bool
    {
        if ($class instanceof FullyQualified) {
            return $class->toString() === self::NEW_CLASS;
        }

        if ($class instanceof Name) {
            return $class->toString() === self::NEW_SHORT;
        }

        return false;
    }
}
