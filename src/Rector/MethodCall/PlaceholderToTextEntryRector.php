<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitor;
use Rector\PhpParser\Node\FileNode;
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
        return [
            FileNode::class,
            StaticCall::class,
            MethodCall::class,
            ClassMethod::class,
            Closure::class,
            ArrowFunction::class,
        ];
    }

    /**
     * @return Node|Node[]|null|NodeVisitor::REMOVE_NODE
     */
    public function refactor(Node $node): Node|array|int|null
    {
        if ($node instanceof FileNode) {
            return $this->refactorFileNode($node);
        }

        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }

        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }

        if ($node instanceof ClassMethod || $node instanceof Closure || $node instanceof ArrowFunction) {
            return $this->refactorReturnType($node);
        }

        return null;
    }

    private function refactorFileNode(FileNode $fileNode): ?FileNode
    {
        $stmts = $fileNode->stmts;
        $hasExistingTextEntryImport = false;
        $placeholderUseIndex = null;
        $placeholderUseItemIndex = null;

        // Scan for existing imports
        foreach ($stmts as $i => $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            foreach ($stmt->uses as $j => $useItem) {
                $name = $useItem->name->toString();

                if ($name === self::NEW_CLASS) {
                    $hasExistingTextEntryImport = true;
                }

                if ($name === self::OLD_CLASS) {
                    $placeholderUseIndex = $i;
                    $placeholderUseItemIndex = $j;
                }
            }
        }

        if ($placeholderUseIndex === null) {
            return null;
        }

        if ($hasExistingTextEntryImport) {
            // Remove the entire Placeholder use statement to avoid duplicates
            array_splice($fileNode->stmts, $placeholderUseIndex, 1);
        } else {
            // Rename Placeholder → TextEntry
            $fileNode->stmts[$placeholderUseIndex]->uses[$placeholderUseItemIndex]->name = new Name(self::NEW_CLASS);
        }

        return $fileNode;
    }

    private function refactorStaticCall(StaticCall $node): ?StaticCall
    {
        if (! $this->isPlaceholderClass($node->class)) {
            return null;
        }

        // Use short name — the import handler ensures the correct import exists.
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

    private function refactorReturnType(ClassMethod|Closure|ArrowFunction $node): ClassMethod|Closure|ArrowFunction|null
    {
        $returnType = $node->returnType;

        if ($returnType === null) {
            return null;
        }

        // Rector resolves names via NameResolver with replaceNodes=true, so short
        // names like `: Placeholder` are already FullyQualified at this point.
        // We replace with a short Name so the printer outputs `TextEntry` not `\Filament\...`.
        if ($returnType instanceof FullyQualified && $returnType->toString() === self::OLD_CLASS) {
            $node->returnType = new Name(self::NEW_SHORT);

            return $node;
        }

        // Fallback for unresolved short names (e.g. in snippet-only fixtures).
        if ($returnType instanceof Name && $returnType->toString() === self::OLD_SHORT) {
            $node->returnType = new Name(self::NEW_SHORT);

            return $node;
        }

        return null;
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
